<?php

namespace Tests\Unit;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContactModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_contact(): void
    {
        $contact = Contact::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '(11) 99999-9999'
        ]);

        $this->assertInstanceOf(Contact::class, $contact);
        $this->assertEquals('John Doe', $contact->name);
        $this->assertEquals('john@example.com', $contact->email);
        $this->assertEquals('11999999999', $contact->phone); // Phone should be cleaned
    }

    #[Test]
    public function it_cleans_phone_number_on_save(): void
    {
        $contact = Contact::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+55 (11) 98765-4321'
        ]);

        $this->assertEquals('5511987654321', $contact->phone);
    }

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $fillable = (new Contact())->getFillable();
        
        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('phone', $fillable);
    }
} 