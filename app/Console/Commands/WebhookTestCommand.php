<?php

namespace App\Console\Commands;

use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Console\Command;

class WebhookTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:test 
                            {--id= : Test specific webhook by ID}
                            {--url= : Test specific URL}
                            {--all : Test all active webhooks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test webhook connectivity and response';

    public function __construct(
        private WebhookService $webhookService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('all')) {
            return $this->testAllWebhooks();
        }

        if ($webhookId = $this->option('id')) {
            return $this->testWebhookById($webhookId);
        }

        if ($url = $this->option('url')) {
            return $this->testWebhookByUrl($url);
        }

        $this->error('Please specify --id, --url, or --all option');
        return Command::FAILURE;
    }

    /**
     * Test all active webhooks
     */
    private function testAllWebhooks(): int
    {
        $webhooks = Webhook::where('is_active', true)->get();

        if ($webhooks->isEmpty()) {
            $this->warn('No active webhooks found');
            return Command::SUCCESS;
        }

        $this->info("Testing {$webhooks->count()} webhook(s)...");
        $this->newLine();

        $results = [];
        foreach ($webhooks as $webhook) {
            $results[] = $this->testWebhook($webhook);
        }

        $this->displaySummary($results);
        return Command::SUCCESS;
    }

    /**
     * Test webhook by ID
     */
    private function testWebhookById(string $id): int
    {
        $webhook = Webhook::find($id);

        if (!$webhook) {
            $this->error("Webhook with ID {$id} not found");
            return Command::FAILURE;
        }

        $this->testWebhook($webhook);
        return Command::SUCCESS;
    }

    /**
     * Test webhook by URL
     */
    private function testWebhookByUrl(string $url): int
    {
        // Create temporary webhook for testing
        $webhook = new Webhook([
            'name' => 'Test Webhook',
            'url' => $url,
            'is_active' => true
        ]);

        $this->testWebhook($webhook);
        return Command::SUCCESS;
    }

    /**
     * Test individual webhook
     */
    private function testWebhook(Webhook $webhook): array
    {
        $this->info("Testing webhook: {$webhook->name} ({$webhook->url})");

        $startTime = microtime(true);
        $result = $this->webhookService->testWebhook($webhook);
        $endTime = microtime(true);

        $responseTime = round(($endTime - $startTime) * 1000, 2);

        if ($result['success']) {
            $this->line("  <fg=green>✓</> Success (HTTP {$result['status']}) - {$responseTime}ms");
        } else {
            $this->line("  <fg=red>✗</> Failed (HTTP {$result['status']}) - {$responseTime}ms");
            if (isset($result['error'])) {
                $this->line("    Error: {$result['error']}");
            }
        }

        if (isset($result['response']) && $result['response']) {
            $response = substr($result['response'], 0, 100);
            $this->line("    Response: {$response}" . (strlen($result['response']) > 100 ? '...' : ''));
        }

        $this->newLine();

        return [
            'webhook' => $webhook,
            'success' => $result['success'],
            'status' => $result['status'],
            'response_time' => $responseTime
        ];
    }

    /**
     * Display test summary
     */
    private function displaySummary(array $results): void
    {
        $successful = array_filter($results, fn($r) => $r['success']);
        $failed = array_filter($results, fn($r) => !$r['success']);

        $this->info('Test Summary:');
        $this->line("  Successful: <fg=green>" . count($successful) . "</>");
        $this->line("  Failed: <fg=red>" . count($failed) . "</>");

        if (!empty($failed)) {
            $this->newLine();
            $this->warn('Failed webhooks:');
            foreach ($failed as $result) {
                $this->line("  - {$result['webhook']->name} (HTTP {$result['status']})");
            }
        }
    }
}
