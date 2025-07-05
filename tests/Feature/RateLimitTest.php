<?php

namespace Tests\Feature;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Habilitar feature toggles para os testes
        $this->enableFeatureToggles();
    }

    #[Test]
    public function it_allows_requests_within_rate_limit(): void
    {
        Contact::factory()->create();

        // Fazer algumas requisições dentro do limite
        for ($i = 0; $i < 5; $i++) {
            $response = $this->getJson('/api/contacts');
            $response->assertStatus(200);
            
            // Verificar headers de rate limit
            $response->assertHeader('X-RateLimit-Limit');
            $response->assertHeader('X-RateLimit-Remaining');
            $response->assertHeader('X-RateLimit-Reset');
        }
    }

    #[Test]
    public function it_blocks_requests_when_rate_limit_exceeded_for_read_operations(): void
    {
        // Usar um IP único para este teste
        $uniqueIp = '192.168.1.' . rand(1, 254);
        $key = "rate_limit:default:ip:{$uniqueIp}";
        
        // Manualmente aplicar rate limit para simular 10 tentativas
        for ($i = 0; $i < 61; $i++) {
            RateLimiter::hit($key, 15 * 60); // 15 minutos
        }

        Contact::factory()->create();

        $response = $this->withServerVariables(['REMOTE_ADDR' => $uniqueIp])->getJson('/api/contacts');
        $response->assertStatus(429);
                $response->assertJsonStructure([
                    'error',
                    'message',
                    'retry_after',
                    'limit',
                    'window'
                ]);
                $response->assertJson([
                    'error' => 'Rate limit exceeded',
                    'message' => 'Too many requests. Please try again later.',
                    'limit' => 60
                ]);
                $response->assertHeader('Retry-After');
    }

    #[Test]
    public function it_blocks_requests_when_rate_limit_exceeded_for_write_operations(): void
    {
        // Usar um IP único para este teste
        $uniqueIp = '192.168.1.' . rand(1, 254);
        $key = "rate_limit:strict:ip:{$uniqueIp}";
        
        // Manualmente aplicar rate limit para simular 10 tentativas
        for ($i = 0; $i < 10; $i++) {
            RateLimiter::hit($key, 15 * 60); // 15 minutos
        }
        
        // Agora fazer uma requisição que deve ser bloqueada
        $contactData = [
            'name' => "Test Contact",
            'email' => "test@example.com",
            'phone' => "11999999999"
        ];

        $response = $this->withServerVariables(['REMOTE_ADDR' => $uniqueIp])
                         ->postJson('/api/contacts', $contactData);
        
        // Esta requisição deve ser bloqueada
        $response->assertStatus(429);
        $response->assertJsonStructure([
            'error',
            'message',
            'retry_after',
            'limit',
            'window'
        ]);
        $response->assertJson([
            'error' => 'Rate limit exceeded',
            'message' => 'Too many requests. Please try again later.',
            'limit' => 10
        ]);
        $response->assertHeader('Retry-After');
    }

    #[Test]
    public function it_includes_correct_rate_limit_headers(): void
    {
        $uniqueIp = '192.168.4.' . rand(1, 254);
        Contact::factory()->create();

        $response = $this->withServerVariables(['REMOTE_ADDR' => $uniqueIp])
                         ->getJson('/api/contacts');

        $response->assertStatus(200);
        $response->assertHeader('X-RateLimit-Limit', '60');
        $response->assertHeader('X-RateLimit-Remaining', '59');
        $response->assertHeader('X-RateLimit-Reset');

        // Verificar que o valor de reset é um timestamp futuro
        $resetTime = $response->headers->get('X-RateLimit-Reset');
        $this->assertIsNumeric($resetTime);
        $this->assertGreaterThan(time(), $resetTime);
    }

    #[Test]
    public function it_decreases_remaining_requests_with_each_call(): void
    {
        $uniqueIp = '192.168.5.' . rand(1, 254);
        Contact::factory()->create();

        // Primeira requisição
        $response1 = $this->withServerVariables(['REMOTE_ADDR' => $uniqueIp])
                          ->getJson('/api/contacts');
        $response1->assertStatus(200);
        $remaining1 = $response1->headers->get('X-RateLimit-Remaining');

        // Segunda requisição
        $response2 = $this->withServerVariables(['REMOTE_ADDR' => $uniqueIp])
                          ->getJson('/api/contacts');
        $response2->assertStatus(200);
        $remaining2 = $response2->headers->get('X-RateLimit-Remaining');

        // Terceira requisição
        $response3 = $this->withServerVariables(['REMOTE_ADDR' => $uniqueIp])
                          ->getJson('/api/contacts');
        $response3->assertStatus(200);
        $remaining3 = $response3->headers->get('X-RateLimit-Remaining');

        // Verificar que o número de requisições restantes diminui
        $this->assertEquals($remaining1 - 1, $remaining2);
        $this->assertEquals($remaining2 - 1, $remaining3);
    }

    #[Test]
    public function it_applies_different_limits_for_different_operations(): void
    {
        $uniqueIp = '192.168.6.' . rand(1, 254);
        Contact::factory()->create();

        // Fazer requisições de leitura (limite default: 60)
        $readResponse = $this->withServerVariables(['REMOTE_ADDR' => $uniqueIp])
                             ->getJson('/api/contacts');
        $readResponse->assertStatus(200);
        $readResponse->assertHeader('X-RateLimit-Limit', '60');

        // Fazer requisições de escrita (limite strict: 10)
        $writeResponse = $this->withServerVariables(['REMOTE_ADDR' => $uniqueIp])
                              ->postJson('/api/contacts', [
                                  'name' => 'Test Contact',
                                  'email' => 'test@example.com',
                                  'phone' => '11999999999'
                              ]);
        $writeResponse->assertStatus(201);
        $writeResponse->assertHeader('X-RateLimit-Limit', '10');
    }

    #[Test]
    public function it_handles_invalid_requests_within_rate_limit(): void
    {
        // Fazer requisições inválidas também devem contar para o rate limit
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/contacts', [
                'name' => 'x', // Muito curto
                'email' => 'invalid-email',
                'phone' => '123' // Muito curto
            ]);
            
            $response->assertStatus(422); // Validation error
            $response->assertHeader('X-RateLimit-Limit', '10');
            $response->assertHeader('X-RateLimit-Remaining');
        }
    }

    #[Test]
    public function it_provides_correct_retry_after_header(): void
    {
        // Usar um IP único para este teste
        $uniqueIp = '192.168.2.' . rand(1, 254);
        $key = "rate_limit:strict:ip:{$uniqueIp}";
        
        // Manualmente aplicar rate limit para simular 10 tentativas
        for ($i = 0; $i < 10; $i++) {
            RateLimiter::hit($key, 15 * 60); // 15 minutos
        }
        
        // Fazer uma requisição que deve ser bloqueada
        $response = $this->withServerVariables(['REMOTE_ADDR' => $uniqueIp])
                         ->postJson('/api/contacts', [
                             'name' => "Test",
                             'email' => "test@example.com",
                             'phone' => "11999999999"
                         ]);
        
        $response->assertStatus(429);
        $retryAfter = $response->headers->get('Retry-After');
        
        // Retry-After deve ser um número positivo (segundos)
        $this->assertIsNumeric($retryAfter);
        $this->assertGreaterThan(0, $retryAfter);
        
        // Para strict rate limit, deve ser no máximo 15 minutos (900 segundos)
        $this->assertLessThanOrEqual(900, $retryAfter);
    }

    #[Test]
    public function it_resets_rate_limit_after_window_expires(): void
    {
        $uniqueIp = '192.168.7.' . rand(1, 254);
        // Este teste é conceitual - em um ambiente real, seria necessário
        // manipular o tempo ou usar um cache mock para simular o reset
        
        Contact::factory()->create();
        
        // Fazer uma requisição
        $response = $this->withServerVariables(['REMOTE_ADDR' => $uniqueIp])
                         ->getJson('/api/contacts');
        $response->assertStatus(200);
        
        $remaining1 = $response->headers->get('X-RateLimit-Remaining');
        
        // Simular reset manual do rate limiter
        RateLimiter::clear("rate_limit:default:ip:{$uniqueIp}");
        
        // Fazer outra requisição após o "reset"
        $response2 = $this->withServerVariables(['REMOTE_ADDR' => $uniqueIp])
                          ->getJson('/api/contacts');
        $response2->assertStatus(200);
        
        $remaining2 = $response2->headers->get('X-RateLimit-Remaining');
        
        // Após o reset, deve ter o limite completo novamente
        $this->assertEquals(59, $remaining2);
    }

    #[Test]
    public function it_handles_different_ip_addresses_separately(): void
    {
        $uniqueIp1 = '192.168.8.' . rand(1, 254);
        $uniqueIp2 = '192.168.9.' . rand(1, 254);
        
        Contact::factory()->create();

        // Primeira requisição do IP 1
        $response1 = $this->withServerVariables(['REMOTE_ADDR' => $uniqueIp1])
                          ->getJson('/api/contacts');
        $response1->assertStatus(200);
        $remaining1 = $response1->headers->get('X-RateLimit-Remaining');

        // Simular requisição de IP diferente
        $response2 = $this->withServerVariables(['REMOTE_ADDR' => $uniqueIp2])
                          ->getJson('/api/contacts');
        $response2->assertStatus(200);
        $remaining2 = $response2->headers->get('X-RateLimit-Remaining');

        // IPs diferentes devem ter contadores separados
        $this->assertEquals(59, $remaining2); // Novo IP deve ter limite completo
    }
}
