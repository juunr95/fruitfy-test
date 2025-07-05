<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OwenIt\Auditing\Models\Audit;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Habilitar feature toggles para os testes
        $this->enableFeatureToggles();
    }

    #[Test]
    public function it_creates_audit_record_when_contact_is_created(): void
    {
        $contactData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'phone' => '(11) 99999-9999'
        ];

        $contact = Contact::create($contactData);

        // Verificar se audit foi criado
        $this->assertDatabaseHas('audits', [
            'auditable_type' => Contact::class,
            'auditable_id' => $contact->id,
            'event' => 'created'
        ]);

        // Verificar dados do audit
        $audit = Audit::where('auditable_type', Contact::class)
                     ->where('auditable_id', $contact->id)
                     ->where('event', 'created')
                     ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('created', $audit->event);
        $this->assertArrayHasKey('name', $audit->new_values);
        $this->assertArrayHasKey('email', $audit->new_values);
        $this->assertArrayHasKey('phone', $audit->new_values);
        $this->assertEquals($contactData['name'], $audit->new_values['name']);
        $this->assertEquals($contactData['email'], $audit->new_values['email']);
    }

    #[Test]
    public function it_creates_audit_record_when_contact_is_updated(): void
    {
        $contact = Contact::factory()->create([
            'name' => 'Nome Original',
            'email' => 'original@example.com',
            'phone' => '11988887777'
        ]);

        $originalData = $contact->toArray();

        // Atualizar contato
        $newData = [
            'name' => 'Nome Atualizado',
            'email' => 'atualizado@example.com',
            'phone' => '11999998888'
        ];

        $contact->update($newData);

        // Verificar se audit de update foi criado
        $this->assertDatabaseHas('audits', [
            'auditable_type' => Contact::class,
            'auditable_id' => $contact->id,
            'event' => 'updated'
        ]);

        // Verificar dados do audit
        $audit = Audit::where('auditable_type', Contact::class)
                     ->where('auditable_id', $contact->id)
                     ->where('event', 'updated')
                     ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('updated', $audit->event);
        
        // Verificar valores antigos
        $this->assertEquals($originalData['name'], $audit->old_values['name']);
        $this->assertEquals($originalData['email'], $audit->old_values['email']);
        $this->assertEquals($originalData['phone'], $audit->old_values['phone']);
        
        // Verificar novos valores
        $this->assertEquals($newData['name'], $audit->new_values['name']);
        $this->assertEquals($newData['email'], $audit->new_values['email']);
        $this->assertEquals(preg_replace('/\D/', '', $newData['phone']), $audit->new_values['phone']);
    }

    #[Test]
    public function it_creates_audit_record_when_contact_is_deleted(): void
    {
        $contact = Contact::factory()->create([
            'name' => 'Contato para Deletar',
            'email' => 'deletar@example.com',
            'phone' => '11988887777'
        ]);

        $contactId = $contact->id;
        $originalData = $contact->toArray();

        // Deletar contato
        $contact->delete();

        // Verificar se audit de delete foi criado
        $this->assertDatabaseHas('audits', [
            'auditable_type' => Contact::class,
            'auditable_id' => $contactId,
            'event' => 'deleted'
        ]);

        // Verificar dados do audit
        $audit = Audit::where('auditable_type', Contact::class)
                     ->where('auditable_id', $contactId)
                     ->where('event', 'deleted')
                     ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('deleted', $audit->event);
        
        // Verificar que os valores antigos estão registrados
        $this->assertEquals($originalData['name'], $audit->old_values['name']);
        $this->assertEquals($originalData['email'], $audit->old_values['email']);
        $this->assertEquals($originalData['phone'], $audit->old_values['phone']);
        
        // Verificar que não há novos valores
        $this->assertEmpty($audit->new_values);
    }

    #[Test]
    public function it_creates_audit_record_when_user_is_created(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ];

        $user = User::create($userData);

        // Verificar se audit foi criado
        $this->assertDatabaseHas('audits', [
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'event' => 'created'
        ]);

        // Verificar dados do audit
        $audit = Audit::where('auditable_type', User::class)
                     ->where('auditable_id', $user->id)
                     ->where('event', 'created')
                     ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('created', $audit->event);
        $this->assertArrayHasKey('name', $audit->new_values);
        $this->assertArrayHasKey('email', $audit->new_values);
        $this->assertEquals($userData['name'], $audit->new_values['name']);
        $this->assertEquals($userData['email'], $audit->new_values['email']);
        
        // Verificar que a senha não está sendo auditada (por segurança)
        $this->assertArrayNotHasKey('password', $audit->new_values);
    }

    #[Test]
    public function it_creates_audit_record_when_user_is_updated(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);

        $originalName = $user->name;
        $originalEmail = $user->email;

        // Atualizar usuário
        $user->update([
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);

        // Verificar se audit de update foi criado
        $this->assertDatabaseHas('audits', [
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'event' => 'updated'
        ]);

        // Verificar dados do audit
        $audit = Audit::where('auditable_type', User::class)
                     ->where('auditable_id', $user->id)
                     ->where('event', 'updated')
                     ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('updated', $audit->event);
        
        // Verificar valores antigos e novos
        $this->assertEquals($originalName, $audit->old_values['name']);
        $this->assertEquals($originalEmail, $audit->old_values['email']);
        $this->assertEquals('Updated Name', $audit->new_values['name']);
        $this->assertEquals('updated@example.com', $audit->new_values['email']);
    }

    #[Test]
    public function it_tracks_multiple_audits_for_same_model(): void
    {
        $contact = Contact::factory()->create([
            'name' => 'Initial Name',
            'email' => 'initial@example.com',
            'phone' => '11988887777'
        ]);

        // Primeira atualização
        $contact->update(['name' => 'First Update']);
        
        // Segunda atualização
        $contact->update(['name' => 'Second Update']);
        
        // Terceira atualização
        $contact->update(['email' => 'updated@example.com']);

        // Verificar que há múltiplos registros de audit
        $audits = Audit::where('auditable_type', Contact::class)
                      ->where('auditable_id', $contact->id)
                      ->orderBy('created_at')
                      ->get();

        $this->assertCount(4, $audits); // created + 3 updates

        // Verificar eventos
        $this->assertEquals('created', $audits[0]->event);
        $this->assertEquals('updated', $audits[1]->event);
        $this->assertEquals('updated', $audits[2]->event);
        $this->assertEquals('updated', $audits[3]->event);

        // Verificar mudanças específicas
        $this->assertEquals('First Update', $audits[1]->new_values['name']);
        $this->assertEquals('Second Update', $audits[2]->new_values['name']);
        $this->assertEquals('updated@example.com', $audits[3]->new_values['email']);
    }

    #[Test]
    public function it_can_retrieve_audit_history_from_model(): void
    {
        $contact = Contact::factory()->create();
        
        // Fazer algumas alterações
        $contact->update(['name' => 'Updated Name 1']);
        $contact->update(['name' => 'Updated Name 2']);

        // Recuperar auditorias através do modelo
        $audits = $contact->audits;

        $this->assertCount(3, $audits); // created + 2 updates
        $this->assertEquals('created', $audits->first()->event);
        $this->assertEquals('updated', $audits->last()->event);
    }

    #[Test]
    public function it_stores_audit_metadata_correctly(): void
    {
        $contact = Contact::factory()->create([
            'name' => 'Test Contact',
            'email' => 'test@example.com',
            'phone' => '11988887777'
        ]);

        $audit = $contact->audits->first();

        // Verificar metadados básicos
        $this->assertNotNull($audit->created_at);
        $this->assertNotNull($audit->updated_at);
        $this->assertEquals(Contact::class, $audit->auditable_type);
        $this->assertEquals($contact->id, $audit->auditable_id);
        
        // Verificar que a URL e user agent podem estar presentes (dependendo do contexto)
        $this->assertTrue(is_string($audit->url) || is_null($audit->url));
        $this->assertTrue(is_string($audit->user_agent) || is_null($audit->user_agent));
    }
}
