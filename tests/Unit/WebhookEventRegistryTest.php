<?php

namespace Tests\Unit;

use App\Services\WebhookEventRegistry;
use App\Events\ContactCreated;
use App\Events\ContactUpdated;
use App\Events\ContactDeleted;
use App\Models\Contact;
use App\Contracts\WebhookEventInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WebhookEventRegistryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear registry before each test
        WebhookEventRegistry::clear();
    }

    #[Test]
    public function it_can_handle_contact_created_event()
    {
        $contact = Contact::factory()->create();
        $event = new ContactCreated($contact);

        $result = WebhookEventRegistry::handle($event);

        $this->assertEquals('contact.contact_created', $result['identifier']);
        $this->assertArrayHasKey('contact', $result['payload']);
        $this->assertEquals($contact->id, $result['payload']['contact']['id']);
        $this->assertEquals('created', $result['payload']['action']);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals($contact->id, $result['metadata']['contact_id']);
    }

    #[Test]
    public function it_can_handle_contact_updated_event()
    {
        $contact = Contact::factory()->create();
        $oldData = ['name' => 'Old Name', 'email' => 'old@example.com'];
        $contact->update(['name' => 'New Name']);
        
        $event = new ContactUpdated($contact, $oldData);

        $result = WebhookEventRegistry::handle($event);

        $this->assertEquals('contact.contact_updated', $result['identifier']);
        $this->assertArrayHasKey('contact', $result['payload']);
        $this->assertArrayHasKey('old_data', $result['payload']);
        $this->assertArrayHasKey('changes', $result['payload']);
        $this->assertEquals('updated', $result['payload']['action']);
        $this->assertEquals($oldData, $result['payload']['old_data']);
    }

    #[Test]
    public function it_can_handle_contact_deleted_event()
    {
        $contactData = [
            'id' => 1,
            'name' => 'Test Contact',
            'email' => 'test@example.com',
            'phone' => '123-456-7890'
        ];
        
        $event = new ContactDeleted($contactData);

        $result = WebhookEventRegistry::handle($event);

        $this->assertEquals('contact.contact_deleted', $result['identifier']);
        $this->assertArrayHasKey('contact', $result['payload']);
        $this->assertEquals($contactData, $result['payload']['contact']);
        $this->assertEquals('deleted', $result['payload']['action']);
        $this->assertEquals(1, $result['metadata']['contact_id']);
        $this->assertTrue($result['metadata']['permanent_deletion']);
    }

    #[Test]
    public function it_can_register_custom_event_handler()
    {
        $customHandler = function($event) {
            return [
                'identifier' => 'custom.event',
                'payload' => ['custom' => 'data'],
                'metadata' => ['custom_handler' => true]
            ];
        };

        WebhookEventRegistry::register('CustomEvent', $customHandler);

        $handlers = WebhookEventRegistry::getRegisteredHandlers();
        $this->assertArrayHasKey('CustomEvent', $handlers);
        $this->assertEquals($customHandler, $handlers['CustomEvent']);
    }

    #[Test]
    public function it_uses_generic_handler_for_unknown_events()
    {
        $unknownEvent = new class {
            public $contact;
            public function __construct()
            {
                $this->contact = (object) [
                    'id' => 1,
                    'name' => 'Test',
                    'toArray' => function() {
                        return ['id' => 1, 'name' => 'Test'];
                    }
                ];
            }
        };

        $result = WebhookEventRegistry::handle($unknownEvent);

        $this->assertStringStartsWith('contact.', $result['identifier']);
        $this->assertArrayHasKey('payload', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals('generic_handler', $result['metadata']['handled_by']);
    }

    #[Test]
    public function it_can_get_event_identifier_from_webhook_interface()
    {
        $contact = Contact::factory()->create();
        $event = new ContactCreated($contact);

        $this->assertInstanceOf(WebhookEventInterface::class, $event);
        
        $identifier = WebhookEventRegistry::getEventIdentifier($event);
        $this->assertEquals('contact.contact_created', $identifier);
    }

    #[Test]
    public function it_can_clear_registered_handlers()
    {
        WebhookEventRegistry::register('TestEvent', function() {});
        
        $this->assertNotEmpty(WebhookEventRegistry::getRegisteredHandlers());
        
        WebhookEventRegistry::clear();
        
        $this->assertEmpty(WebhookEventRegistry::getRegisteredHandlers());
    }

    #[Test]
    public function it_includes_changes_in_contact_updated_payload()
    {
        $contact = Contact::factory()->create(['name' => 'Original Name', 'email' => 'original@example.com']);
        $oldData = $contact->toArray();
        
        $contact->update(['name' => 'Updated Name']);
        
        $event = new ContactUpdated($contact, $oldData);
        $result = WebhookEventRegistry::handle($event);

        $this->assertArrayHasKey('changes', $result['payload']);
        $this->assertArrayHasKey('name', $result['payload']['changes']);
        $this->assertEquals('Original Name', $result['payload']['changes']['name']['from']);
        $this->assertEquals('Updated Name', $result['payload']['changes']['name']['to']);
    }
} 