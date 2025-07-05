<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use App\Exceptions\FeatureDisabledException;
use App\Services\FeatureToggleService;

class ContactsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Limpar cache dos feature toggles antes de cada teste
        FeatureToggleService::clearCache();
    }

    #[Test]
    public function it_should_be_able_to_create_a_new_contact(): void
    {
        $data = [
            'name' => 'Rodolfo Meri',
            'email' => 'rodolfomeri@contato.com',
            'phone' => '(41) 98899-4422'
        ];

        $response = $this->post('/contacts', $data);

        $response->assertStatus(Response::HTTP_FOUND); // 302 redirect

        $expected = $data;
        $expected['phone'] = preg_replace('/\D/', '', $expected['phone']);

        $this->assertDatabaseHas('contacts', $expected);
    }

    #[Test]
    public function it_should_validate_information(): void
    {
        $data = [
            'name' => 'ro',
            'email' => 'email-errado@',
            'phone' => '419'
        ];

        $response = $this->post('/contacts', $data);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'phone'
        ]);

        $this->assertDatabaseCount('contacts', 0);
    }

    #[Test]
    public function it_should_be_able_to_list_contacts_paginated_by_10_items_per_page(): void
    {
        \App\Models\Contact::factory(20)->create();

        $response = $this->get('/contacts');

        $response->assertStatus(200);

        // Using Inertia, the view would be 'app' with props
        $response->assertViewIs('app');

        $response->assertInertia(fn ($page) => $page
            ->component('Contacts/Index')
            ->has('contacts')
            ->has('contacts.data', 10)
        );
    }

    #[Test]
    public function it_should_be_able_to_delete_a_contact(): void
    {
        $contact = \App\Models\Contact::factory()->create();

        $response = $this->delete("/contacts/{$contact->id}");

        $response->assertStatus(Response::HTTP_FOUND); // 302 redirect

        $this->assertDatabaseMissing('contacts', $contact->toArray());
    }

    #[Test]
    public function the_contact_email_should_be_unique(): void
    {
        $contact = \App\Models\Contact::factory()->create();

        $data = [
            'name' => 'Rodolfo Meri',
            'email' => $contact->email,
            'phone' => '(41) 98899-4422'
        ];

        $response = $this->post('/contacts', $data);

        $response->assertSessionHasErrors([
            'email'
        ]);

        $this->assertDatabaseCount('contacts', 1);
    }

    #[Test]
    public function it_should_be_able_to_update_a_contact(): void
    {
        $contact = \App\Models\Contact::factory()->create();

        $data = [
            'name' => 'Rodolfo Meri',
            'email' => 'emailatualizado@email.com',
            'phone' => '(41) 98899-4422'
        ];

        $response = $this->put("/contacts/{$contact->id}", $data);

        $response->assertStatus(Response::HTTP_FOUND); // 302 redirect

        $expected = $data;

        $expected['phone'] = preg_replace('/\D/', '', $expected['phone']);

        $this->assertDatabaseHas('contacts', $expected);

        $this->assertDatabaseMissing('contacts', $contact->toArray());
    }

    #[Test]
    public function it_should_not_allow_creating_contact_when_feature_is_disabled(): void
    {
        $this->disableFeatureToggle('contacts.can_create');

        $data = [
            'name' => 'Rodolfo Meri',
            'email' => 'rodolfomeri@contato.com',
            'phone' => '(41) 98899-4422'
        ];

        $response = $this->post('/contacts', $data);

        $response->assertRedirect()
            ->assertSessionHas('warning');

        $this->assertDatabaseCount('contacts', 0);
    }

    #[Test]
    public function it_should_not_allow_updating_contact_when_feature_is_disabled(): void
    {
        $contact = \App\Models\Contact::factory()->create();

        $this->disableFeatureToggle('contacts.can_update');

        $data = [
            'name' => 'Rodolfo Meri Updated',
            'email' => 'updated@email.com',
            'phone' => '(41) 98899-4422'
        ];

        $response = $this->put("/contacts/{$contact->id}", $data);

        $response->assertRedirect()
            ->assertSessionHas('warning');

        $this->assertDatabaseMissing('contacts', $data);
    }

    #[Test]
    public function it_should_not_allow_deleting_contact_when_feature_is_disabled(): void
    {
        $contact = \App\Models\Contact::factory()->create();

        $this->disableFeatureToggle('contacts.can_delete');

        $response = $this->delete("/contacts/{$contact->id}");

        $response->assertRedirect()
            ->assertSessionHas('warning');

        // Need to check if the contact still exists using ID since toArray() might have timestamp format differences
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
        ]);
    }
}
