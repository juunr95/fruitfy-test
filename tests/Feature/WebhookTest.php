<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Webhook;
use App\Services\WebhookService;
use App\Events\ContactCreated;
use App\Events\ContactUpdated;
use App\Events\ContactDeleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Enable feature toggles for tests
        $this->enableFeatureToggles();
    }

    #[Test]
    public function it_can_create_a_webhook()
    {
        $webhook = Webhook::create([
            'name' => 'Test Webhook',
            'url' => 'https://httpbin.org/post',
            'events' => ['contact.contact_created'],
            'secret' => 'test-secret',
            'is_active' => true
        ]);

        $this->assertDatabaseHas('webhooks', [
            'name' => 'Test Webhook',
            'url' => 'https://httpbin.org/post',
            'is_active' => true
        ]);

        $this->assertTrue($webhook->shouldTriggerFor('contact.contact_created'));
        $this->assertFalse($webhook->shouldTriggerFor('contact.contact_updated'));
    }

    #[Test]
    public function it_can_test_webhook_connectivity()
    {
        $webhook = Webhook::create([
            'name' => 'Test Connectivity',
            'url' => 'https://httpbin.org/post',
            'is_active' => true
        ]);

        Http::fake([
            'httpbin.org/*' => Http::response(['message' => 'OK'], 200)
        ]);

        $service = new WebhookService();
        $result = $service->testWebhook($webhook);

        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status']);
        $this->assertStringContainsString('OK', $result['response']);
    }

    #[Test]
    public function it_can_send_webhook_manually()
    {
        $webhook = Webhook::create([
            'name' => 'Manual Webhook',
            'url' => 'https://httpbin.org/post',
            'is_active' => true
        ]);

        Http::fake([
            'httpbin.org/*' => Http::response(['success' => true], 200)
        ]);

        $service = new WebhookService();
        
        $payload = [
            'contact' => [
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com'
            ]
        ];

        $result = $service->sendWebhook($webhook, 'contact.contact_created', $payload);

        $this->assertTrue($result);

        // Check webhook status was updated
        $webhook->refresh();
        $this->assertEquals(200, $webhook->last_response_status);
        $this->assertNotNull($webhook->last_triggered_at);
        $this->assertEquals(0, $webhook->retry_count);
    }

    #[Test]
    public function it_handles_webhook_failures_gracefully()
    {
        $webhook = Webhook::create([
            'name' => 'Failing Webhook',
            'url' => 'https://nonexistent.example.com/webhook',
            'is_active' => true
        ]);

        Http::fake([
            'nonexistent.example.com/*' => Http::response([], 500)
        ]);

        $service = new WebhookService();
        
        $result = $service->sendWebhook($webhook, 'contact.contact_created', []);

        $this->assertFalse($result);

        $webhook->refresh();
        $this->assertEquals(500, $webhook->last_response_status);
        $this->assertEquals(1, $webhook->retry_count);
        $this->assertTrue($webhook->is_active); // Still active on first failure
    }

    #[Test]
    public function it_disables_webhook_after_max_retries()
    {
        $webhook = Webhook::create([
            'name' => 'Retry Test Webhook',
            'url' => 'https://failing.example.com/webhook',
            'max_retries' => 2,
            'is_active' => true
        ]);

        Http::fake([
            'failing.example.com/*' => Http::response([], 500)
        ]);

        $service = new WebhookService();

        // First failure
        $service->sendWebhook($webhook, 'test.event', []);
        $webhook->refresh();
        $this->assertTrue($webhook->is_active);
        $this->assertEquals(1, $webhook->retry_count);

        // Second failure
        $service->sendWebhook($webhook, 'test.event', []);
        $webhook->refresh();
        $this->assertTrue($webhook->is_active);
        $this->assertEquals(2, $webhook->retry_count);

        // Third failure - should disable webhook
        $service->sendWebhook($webhook, 'test.event', []);
        $webhook->refresh();
        $this->assertFalse($webhook->is_active);
    }

    #[Test]
    public function it_includes_signature_when_secret_is_provided()
    {
        $webhook = Webhook::create([
            'name' => 'Signed Webhook',
            'url' => 'https://httpbin.org/post',
            'secret' => 'test-secret-123',
            'is_active' => true
        ]);

        Http::fake([
            'httpbin.org/*' => Http::response(['success' => true], 200)
        ]);

        $service = new WebhookService();
        $service->sendWebhook($webhook, 'test.event', ['test' => 'data']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Webhook-Signature')
                && str_starts_with($request->header('X-Webhook-Signature')[0], 'sha256=')
                && $request->hasHeader('User-Agent', 'ContactsApp-Webhook/1.0');
        });
    }

    #[Test]
    public function webhook_service_can_trigger_multiple_webhooks()
    {
        // Create multiple webhooks
        $webhook1 = Webhook::create([
            'name' => 'Webhook 1',
            'url' => 'https://httpbin.org/post',
            'events' => ['contact.contact_created'],
            'is_active' => true
        ]);

        $webhook2 = Webhook::create([
            'name' => 'Webhook 2',
            'url' => 'https://httpbin.org/post',
            'events' => ['contact.contact_created'],
            'is_active' => true
        ]);

        Http::fake([
            'httpbin.org/*' => Http::response(['success' => true], 200)
        ]);

        $service = new WebhookService();
        $service->triggerWebhooks('contact.contact_created', ['test' => 'data']);

        Http::assertSentCount(2);
    }

    #[Test]
    public function inactive_webhooks_are_not_triggered()
    {
        $webhook = Webhook::create([
            'name' => 'Inactive Webhook',
            'url' => 'https://httpbin.org/post',
            'is_active' => false
        ]);

        Http::fake();

        $service = new WebhookService();
        $service->triggerWebhooks('contact.contact_created', []);

        Http::assertNothingSent();
    }

    #[Test]
    public function webhooks_only_trigger_for_specified_events()
    {
        $webhook = Webhook::create([
            'name' => 'Specific Event Webhook',
            'url' => 'https://httpbin.org/post',
            'events' => ['contact.contact_created'], // Only created events
            'is_active' => true
        ]);

        Http::fake();

        $service = new WebhookService();
        
        // Should trigger for specified event
        $service->triggerWebhooks('contact.contact_created', []);
        Http::assertSentCount(1);

        Http::fake(); // Reset

        // Should not trigger for different event
        $service->triggerWebhooks('contact.contact_updated', []);
        Http::assertNothingSent();
    }

    #[Test]
    public function webhook_payload_structure_is_correct()
    {
        $webhook = Webhook::create([
            'name' => 'Payload Test Webhook',
            'url' => 'https://httpbin.org/post',
            'is_active' => true
        ]);

        Http::fake([
            'httpbin.org/*' => Http::response(['success' => true], 200)
        ]);

        $service = new WebhookService();
        
        $testData = [
            'contact' => [
                'id' => 123,
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ]
        ];

        $service->sendWebhook($webhook, 'contact.contact_created', $testData);

        Http::assertSent(function ($request) use ($testData) {
            $body = json_decode($request->body(), true);
            
            return $body['event'] === 'contact.contact_created'
                && isset($body['timestamp'])
                && $body['data'] === $testData
                && isset($body['timestamp']);
        });
    }
} 