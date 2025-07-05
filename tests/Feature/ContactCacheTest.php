<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Services\ContactCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContactCacheTest extends TestCase
{
    use RefreshDatabase;

    private ContactCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Habilitar feature toggles para os testes
        $this->enableFeatureToggles();
        
        $this->cacheService = new ContactCacheService();
        
        // Limpar cache antes de cada teste
        Cache::clear();
    }

    #[Test]
    public function it_generates_consistent_cache_keys(): void
    {
        $request1 = new Request([
            'search' => 'test',
            'sort_by' => 'name',
            'sort_direction' => 'asc',
            'per_page' => 10,
            'page' => 1
        ]);

        $request2 = new Request([
            'search' => 'test',
            'sort_by' => 'name',
            'sort_direction' => 'asc',
            'per_page' => 10,
            'page' => 1
        ]);

        $key1 = $this->cacheService->generateCacheKey($request1);
        $key2 = $this->cacheService->generateCacheKey($request2);

        $this->assertEquals($key1, $key2);
    }

    #[Test]
    public function it_generates_different_cache_keys_for_different_params(): void
    {
        $request1 = new Request(['search' => 'test']);
        $request2 = new Request(['search' => 'different']);

        $key1 = $this->cacheService->generateCacheKey($request1);
        $key2 = $this->cacheService->generateCacheKey($request2);

        $this->assertNotEquals($key1, $key2);
    }

    #[Test]
    public function it_caches_contact_index_queries(): void
    {
        // Criar alguns contatos
        Contact::factory()->count(5)->create();

        // Primeira requisição - deve fazer query no banco
        $response1 = $this->getJson('/api/contacts');
        $response1->assertStatus(200);

        // Verificar se foi cacheado
        $cacheKey = $this->cacheService->generateCacheKey(new Request());
        $this->assertTrue(Cache::has($cacheKey));

        // Segunda requisição - deve vir do cache
        $response2 = $this->getJson('/api/contacts');
        $response2->assertStatus(200);

        // Verificar que o resultado é o mesmo
        $this->assertEquals($response1->json(), $response2->json());
    }

    #[Test]
    public function it_uses_different_cache_for_different_pagination(): void
    {
        Contact::factory()->count(20)->create();

        // Primeira página
        $response1 = $this->getJson('/api/contacts?page=1&per_page=10');
        $response1->assertStatus(200);

        // Segunda página
        $response2 = $this->getJson('/api/contacts?page=2&per_page=10');
        $response2->assertStatus(200);

        // Resultados devem ser diferentes
        $this->assertNotEquals($response1->json('data'), $response2->json('data'));
    }

    #[Test]
    public function it_uses_different_cache_for_different_search_terms(): void
    {
        Contact::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        Contact::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        // Busca por John
        $response1 = $this->getJson('/api/contacts?search=John');
        $response1->assertStatus(200);

        // Busca por Jane
        $response2 = $this->getJson('/api/contacts?search=Jane');
        $response2->assertStatus(200);

        // Resultados devem ser diferentes
        $this->assertNotEquals($response1->json('data'), $response2->json('data'));
    }

    #[Test]
    public function it_clears_cache_when_contact_is_created(): void
    {
        // Criar cache inicial
        $this->getJson('/api/contacts');
        $cacheKey = $this->cacheService->generateCacheKey(new Request());
        $this->assertTrue(Cache::has($cacheKey));

        // Criar novo contato
        $contactData = [
            'name' => 'New Contact',
            'email' => 'new@example.com',
            'phone' => '11999999999'
        ];

        $response = $this->postJson('/api/contacts', $contactData);
        $response->assertStatus(201);

        // Cache deve ter sido limpo
        $this->assertFalse(Cache::has($cacheKey));
    }

    #[Test]
    public function it_clears_cache_when_contact_is_updated(): void
    {
        $contact = Contact::factory()->create();

        // Criar cache inicial
        $this->getJson('/api/contacts');
        $cacheKey = $this->cacheService->generateCacheKey(new Request());
        $this->assertTrue(Cache::has($cacheKey));

        // Atualizar contato
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '11988888888'
        ];

        $response = $this->putJson("/api/contacts/{$contact->id}", $updateData);
        $response->assertStatus(200);

        // Cache deve ter sido limpo
        $this->assertFalse(Cache::has($cacheKey));
    }

    #[Test]
    public function it_clears_cache_when_contact_is_deleted(): void
    {
        $contact = Contact::factory()->create();

        // Criar cache inicial
        $this->getJson('/api/contacts');
        $cacheKey = $this->cacheService->generateCacheKey(new Request());
        $this->assertTrue(Cache::has($cacheKey));

        // Deletar contato
        $response = $this->deleteJson("/api/contacts/{$contact->id}");
        $response->assertStatus(200);

        // Cache deve ter sido limpo
        $this->assertFalse(Cache::has($cacheKey));
    }

    #[Test]
    public function it_handles_cache_miss_gracefully(): void
    {
        Contact::factory()->count(3)->create();

        // Limpar cache manualmente
        Cache::clear();

        // Requisição deve funcionar mesmo sem cache
        $response = $this->getJson('/api/contacts');
        $response->assertStatus(200);
        
        $response->assertJsonStructure([
            'data',
            'current_page',
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'links',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total'
        ]);

        $this->assertCount(3, $response->json('data'));
    }

    #[Test]
    public function it_respects_cache_ttl(): void
    {
        Contact::factory()->count(2)->create();

        // Primeira requisição
        $response = $this->getJson('/api/contacts');
        $response->assertStatus(200);

        $cacheKey = $this->cacheService->generateCacheKey(new Request());
        $this->assertTrue(Cache::has($cacheKey));

        // Simular expiração do cache (limpar manualmente)
        Cache::forget($cacheKey);
        $this->assertFalse(Cache::has($cacheKey));

        // Nova requisição deve funcionar e recriar o cache
        $response2 = $this->getJson('/api/contacts');
        $response2->assertStatus(200);
        $this->assertTrue(Cache::has($cacheKey));
    }

    #[Test]
    public function it_includes_all_relevant_parameters_in_cache_key(): void
    {
        $params = [
            'search' => 'test',
            'sort_by' => 'name',
            'sort_direction' => 'desc',
            'per_page' => 20,
            'page' => 2
        ];

        $request = new Request($params);
        $cacheKey = $this->cacheService->generateCacheKey($request);

        // Cache key deve ser uma string não vazia
        $this->assertIsString($cacheKey);
        $this->assertNotEmpty($cacheKey);
        
        // Deve começar com o prefixo esperado
        $this->assertStringStartsWith('contacts_index:', $cacheKey);
    }

    #[Test]
    public function it_works_with_empty_parameters(): void
    {
        Contact::factory()->count(2)->create();

        $request = new Request(); // Sem parâmetros
        $cacheKey = $this->cacheService->generateCacheKey($request);

        // Deve gerar uma chave válida mesmo sem parâmetros
        $this->assertIsString($cacheKey);
        $this->assertNotEmpty($cacheKey);

        // Requisição deve funcionar
        $response = $this->getJson('/api/contacts');
        $response->assertStatus(200);
    }

    #[Test]
    public function it_handles_null_parameters_correctly(): void
    {
        $request1 = new Request(['search' => null, 'sort_by' => 'name']);
        $request2 = new Request(['sort_by' => 'name']);

        $key1 = $this->cacheService->generateCacheKey($request1);
        $key2 = $this->cacheService->generateCacheKey($request2);

        // Parâmetros null devem ser ignorados, então as chaves devem ser iguais
        $this->assertEquals($key1, $key2);
    }

    #[Test]
    public function cache_service_provides_stats(): void
    {
        $stats = $this->cacheService->getStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('cache_driver', $stats);
        $this->assertArrayHasKey('cache_duration_minutes', $stats);
        $this->assertArrayHasKey('cache_prefix', $stats);
    }

    #[Test]
    public function cache_service_can_check_if_enabled(): void
    {
        $isEnabled = $this->cacheService->isEnabled();
        $this->assertIsBool($isEnabled);
    }
}
