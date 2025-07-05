<?php

namespace Tests\Feature;

use App\Mail\ContactCreatedNotification;
use App\Mail\ContactDeletedNotification;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Services\FeatureToggleService;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Habilitar feature toggles para os testes
        $this->enableFeatureToggles();
        
        // Limpar cache dos feature toggles antes de cada teste
        FeatureToggleService::clearCache();
    }

    #[Test]
    public function it_sends_email_notification_when_contact_is_created(): void
    {
        Mail::fake();

        $contactData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'phone' => '(11) 99999-9999'
        ];

        $response = $this->post('/contacts', $contactData);

        $response->assertStatus(302); // Redirect after creation

        // Verificar se email foi enviado
        Mail::assertSent(ContactCreatedNotification::class, function ($mail) use ($contactData) {
            return $mail->contact->name === $contactData['name'] &&
                   $mail->contact->email === $contactData['email'] &&
                   $mail->contact->phone === preg_replace('/\D/', '', $contactData['phone']);
        });

        // Verificar se o email foi enviado
        Mail::assertSent(ContactCreatedNotification::class);
    }

    #[Test]
    public function it_sends_email_notification_when_contact_is_deleted(): void
    {
        Mail::fake();

        $contact = Contact::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'phone' => '11988887777'
        ]);

        $response = $this->delete("/contacts/{$contact->id}");

        $response->assertStatus(302); // Redirect after deletion

        // Verificar se email foi enviado
        Mail::assertSent(ContactDeletedNotification::class, function ($mail) use ($contact) {
            return $mail->contactData['name'] === $contact->name &&
                   $mail->contactData['email'] === $contact->email &&
                   $mail->contactData['phone'] === $contact->phone;
        });

        // Verificar se o email foi enviado
        Mail::assertSent(ContactDeletedNotification::class);
    }

    #[Test]
    public function it_sends_email_to_admin_address(): void
    {
        Mail::fake();

        $contactData = [
            'name' => 'Teste Admin',
            'email' => 'teste@example.com',
            'phone' => '(11) 99999-9999'
        ];

        $this->post('/contacts', $contactData);

        // Verificar se email foi enviado para o endereço do admin
        Mail::assertSent(ContactCreatedNotification::class, function ($mail) {
            return $mail->hasTo(config('mail.admin_email'));
        });
    }

    #[Test]
    public function it_includes_correct_subject_in_created_notification(): void
    {
        Mail::fake();

        $contactData = [
            'name' => 'Test Subject',
            'email' => 'subject@example.com',
            'phone' => '(11) 99999-9999'
        ];

        $this->post('/contacts', $contactData);

        Mail::assertSent(ContactCreatedNotification::class, function ($mail) use ($contactData) {
            $envelope = $mail->envelope();
            return $envelope->subject === 'Novo Contato Criado - ' . $contactData['name'];
        });
    }

    #[Test]
    public function it_includes_correct_subject_in_deleted_notification(): void
    {
        Mail::fake();

        $contact = Contact::factory()->create([
            'name' => 'Test Delete Subject',
            'email' => 'delete@example.com',
            'phone' => '11988887777'
        ]);

        $this->delete("/contacts/{$contact->id}");

        Mail::assertSent(ContactDeletedNotification::class, function ($mail) use ($contact) {
            $envelope = $mail->envelope();
            return $envelope->subject === 'Contato Deletado - ' . $contact->name;
        });
    }

    #[Test]
    public function it_uses_correct_view_for_created_notification(): void
    {
        Mail::fake();

        $contactData = [
            'name' => 'Test View',
            'email' => 'view@example.com',
            'phone' => '(11) 99999-9999'
        ];

        $this->post('/contacts', $contactData);

        Mail::assertSent(ContactCreatedNotification::class, function ($mail) {
            $content = $mail->content();
            return $content->view === 'emails.contact-created';
        });
    }

    #[Test]
    public function it_uses_correct_view_for_deleted_notification(): void
    {
        Mail::fake();

        $contact = Contact::factory()->create([
            'name' => 'Test Delete View',
            'email' => 'deleteview@example.com',
            'phone' => '11988887777'
        ]);

        $this->delete("/contacts/{$contact->id}");

        Mail::assertSent(ContactDeletedNotification::class, function ($mail) {
            $content = $mail->content();
            return $content->view === 'emails.contact-deleted';
        });
    }

    #[Test]
    public function it_does_not_send_email_when_contact_creation_fails(): void
    {
        Mail::fake();

        // Tentar criar contato com dados inválidos
        $invalidData = [
            'name' => 'x', // Nome muito curto
            'email' => 'invalid-email',
            'phone' => '123' // Telefone muito curto
        ];

        $response = $this->post('/contacts', $invalidData);

        $response->assertSessionHasErrors(['name', 'email', 'phone']);

        // Verificar que nenhum email foi enviado
        Mail::assertNotSent(ContactCreatedNotification::class);
    }

    #[Test]
    public function it_does_not_send_email_when_contact_deletion_fails(): void
    {
        Mail::fake();

        // Tentar deletar contato inexistente
        $response = $this->delete("/contacts/999999");

        $response->assertStatus(404);

        // Verificar que nenhum email foi enviado
        Mail::assertNotSent(ContactDeletedNotification::class);
    }

    #[Test]
    public function it_sends_email_via_api_contact_creation(): void
    {
        Mail::fake();

        $contactData = [
            'name' => 'API Test',
            'email' => 'api@example.com',
            'phone' => '(11) 99999-9999'
        ];

        $response = $this->postJson('/api/contacts', $contactData);

        $response->assertStatus(201);

        // Verificar se email foi enviado mesmo via API
        Mail::assertSent(ContactCreatedNotification::class, function ($mail) use ($contactData) {
            return $mail->contact->name === $contactData['name'];
        });
    }

    #[Test]
    public function it_sends_email_via_api_contact_deletion(): void
    {
        Mail::fake();

        $contact = Contact::factory()->create([
            'name' => 'API Delete Test',
            'email' => 'apidelete@example.com',
            'phone' => '11988887777'
        ]);

        $response = $this->deleteJson("/api/contacts/{$contact->id}");

        $response->assertStatus(200);

        // Verificar se email foi enviado mesmo via API
        Mail::assertSent(ContactDeletedNotification::class, function ($mail) use ($contact) {
            return $mail->contactData['name'] === $contact->name;
        });
    }
}
