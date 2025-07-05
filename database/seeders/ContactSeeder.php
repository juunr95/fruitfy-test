<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use default count of 50 contacts
        $count = 50;
        
        $this->command->info("Creating {$count} contacts...");

        // Create contacts with different phone types
        $mobileCount = intval($count * 0.8); // 80% mobile
        $landlineCount = $count - $mobileCount; // 20% landline

        // Create mobile contacts
        if ($mobileCount > 0) {
            Contact::factory($mobileCount)->mobile()->create();
            $this->command->info("Created {$mobileCount} contacts with mobile phones");
        }

        // Create landline contacts
        if ($landlineCount > 0) {
            Contact::factory($landlineCount)->landline()->create();
            $this->command->info("Created {$landlineCount} contacts with landline phones");
        }

        $this->command->info("✅ Total contacts created: {$count}");
    }
}
