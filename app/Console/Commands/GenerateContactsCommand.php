<?php

namespace App\Console\Commands;

use App\Models\Contact;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateContactsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contacts:generate 
                          {count=50 : Number of contacts to generate}
                          {--clear : Clear existing contacts before generating new ones}
                          {--mobile-percent=80 : Percentage of mobile phones (0-100)}
                          {--batch-size=100 : Number of contacts to create per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate fake contacts using Faker with Brazilian data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = (int) $this->argument('count');
        $clearExisting = $this->option('clear');
        $mobilePercent = (int) $this->option('mobile-percent');
        $batchSize = (int) $this->option('batch-size');

        // Validate inputs
        if ($count <= 0) {
            $this->error('Count must be greater than 0');
            return 1;
        }

        if ($mobilePercent < 0 || $mobilePercent > 100) {
            $this->error('Mobile percentage must be between 0 and 100');
            return 1;
        }

        if ($batchSize <= 0) {
            $this->error('Batch size must be greater than 0');
            return 1;
        }

        // Clear existing contacts if requested
        if ($clearExisting) {
            if ($this->confirm('Are you sure you want to delete all existing contacts?')) {
                $existingCount = Contact::count();
                Contact::truncate();
                $this->info("✅ Deleted {$existingCount} existing contacts");
            } else {
                $this->info('Keeping existing contacts');
            }
        }

        // Calculate phone type distribution
        $mobileCount = intval($count * ($mobilePercent / 100));
        $landlineCount = $count - $mobileCount;

        $this->info("📱 Generating {$count} contacts:");
        $this->info("   - Mobile phones: {$mobileCount} ({$mobilePercent}%)");
        $this->info("   - Landline phones: {$landlineCount} (" . (100 - $mobilePercent) . "%)");
        $this->info("   - Batch size: {$batchSize}");

        // Show progress bar
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $created = 0;
        $startTime = microtime(true);

        try {
            // Generate mobile contacts in batches
            if ($mobileCount > 0) {
                $this->generateInBatches($mobileCount, $batchSize, 'mobile', $progressBar, $created);
            }

            // Generate landline contacts in batches
            if ($landlineCount > 0) {
                $this->generateInBatches($landlineCount, $batchSize, 'landline', $progressBar, $created);
            }

            $progressBar->finish();

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $this->newLine(2);
            $this->info("✅ Successfully generated {$created} contacts in {$duration} seconds");
            $this->info("📊 Total contacts in database: " . Contact::count());

            // Show some sample data
            if ($this->option('verbose')) {
                $this->showSampleContacts();
            }

            return 0;

        } catch (\Exception $e) {
            $progressBar->finish();
            $this->newLine(2);
            $this->error("❌ Error generating contacts: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate contacts in batches to avoid memory issues
     */
    private function generateInBatches(int $count, int $batchSize, string $type, $progressBar, int &$created): void
    {
        $remaining = $count;

        while ($remaining > 0) {
            $currentBatch = min($batchSize, $remaining);
            
            DB::transaction(function() use ($currentBatch, $type, $progressBar, &$created) {
                if ($type === 'mobile') {
                    Contact::factory($currentBatch)->mobile()->create();
                } else {
                    Contact::factory($currentBatch)->landline()->create();
                }
                
                $created += $currentBatch;
                $progressBar->advance($currentBatch);
            });

            $remaining -= $currentBatch;
        }
    }

    /**
     * Show sample contacts for verification
     */
    private function showSampleContacts(): void
    {
        $this->newLine();
        $this->info("📋 Sample contacts:");
        
        $samples = Contact::latest()->limit(5)->get();
        
        $headers = ['ID', 'Name', 'Email', 'Phone', 'Created'];
        $rows = [];
        
        foreach ($samples as $contact) {
            $rows[] = [
                $contact->id,
                $contact->name,
                $contact->email,
                $contact->phone,
                $contact->created_at->format('Y-m-d H:i:s')
            ];
        }
        
        $this->table($headers, $rows);
    }
}
