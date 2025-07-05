<?php

namespace App\Console\Commands;

use App\Models\Webhook;
use Illuminate\Console\Command;

class WebhookManageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:manage 
                            {action : Action to perform: list, create, delete, enable, disable}
                            {--id= : Webhook ID for actions}
                            {--name= : Webhook name for creation}
                            {--url= : Webhook URL for creation}
                            {--events=* : Events to listen for (contact.created, contact.updated, contact.deleted)}
                            {--secret= : Secret for webhook verification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage webhooks (list, create, delete, enable, disable)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'list' => $this->listWebhooks(),
            'create' => $this->createWebhook(),
            'delete' => $this->deleteWebhook(),
            'enable' => $this->enableWebhook(),
            'disable' => $this->disableWebhook(),
            default => $this->invalidAction($action)
        };
    }

    /**
     * List all webhooks
     */
    private function listWebhooks(): int
    {
        $webhooks = Webhook::all();

        if ($webhooks->isEmpty()) {
            $this->info('No webhooks configured');
            return Command::SUCCESS;
        }

        $this->info('Webhooks:');
        $this->newLine();

        $headers = ['ID', 'Name', 'URL', 'Events', 'Active', 'Last Status', 'Last Triggered'];
        $rows = [];

        foreach ($webhooks as $webhook) {
            $events = $webhook->events ? implode(', ', $webhook->events) : 'All events';
            $lastTriggered = $webhook->last_triggered_at 
                ? $webhook->last_triggered_at->diffForHumans() 
                : 'Never';

            $rows[] = [
                $webhook->id,
                $webhook->name,
                substr($webhook->url, 0, 50) . (strlen($webhook->url) > 50 ? '...' : ''),
                $events,
                $webhook->is_active ? '✓' : '✗',
                $webhook->last_response_status ?? 'N/A',
                $lastTriggered
            ];
        }

        $this->table($headers, $rows);
        return Command::SUCCESS;
    }

    /**
     * Create new webhook
     */
    private function createWebhook(): int
    {
        $name = $this->option('name') ?: $this->ask('Webhook name');
        $url = $this->option('url') ?: $this->ask('Webhook URL');
        $events = $this->option('events') ?: [];
        $secret = $this->option('secret');

        if (!$name || !$url) {
            $this->error('Name and URL are required');
            return Command::FAILURE;
        }

        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error('Invalid URL format');
            return Command::FAILURE;
        }

        // Validate events
        $allowedEvents = ['contact.created', 'contact.updated', 'contact.deleted'];
        foreach ($events as $event) {
            if (!in_array($event, $allowedEvents)) {
                $this->error("Invalid event: {$event}. Allowed: " . implode(', ', $allowedEvents));
                return Command::FAILURE;
            }
        }

        $webhook = Webhook::create([
            'name' => $name,
            'url' => $url,
            'events' => empty($events) ? null : $events,
            'secret' => $secret,
            'is_active' => true
        ]);

        $this->info("Webhook created successfully with ID: {$webhook->id}");
        return Command::SUCCESS;
    }

    /**
     * Delete webhook
     */
    private function deleteWebhook(): int
    {
        $id = $this->option('id') ?: $this->ask('Webhook ID to delete');

        $webhook = Webhook::find($id);
        if (!$webhook) {
            $this->error("Webhook with ID {$id} not found");
            return Command::FAILURE;
        }

        if ($this->confirm("Delete webhook '{$webhook->name}'?")) {
            $webhook->delete();
            $this->info('Webhook deleted successfully');
        } else {
            $this->info('Deletion cancelled');
        }

        return Command::SUCCESS;
    }

    /**
     * Enable webhook
     */
    private function enableWebhook(): int
    {
        $id = $this->option('id') ?: $this->ask('Webhook ID to enable');

        $webhook = Webhook::find($id);
        if (!$webhook) {
            $this->error("Webhook with ID {$id} not found");
            return Command::FAILURE;
        }

        $webhook->update(['is_active' => true, 'retry_count' => 0]);
        $this->info("Webhook '{$webhook->name}' enabled successfully");
        return Command::SUCCESS;
    }

    /**
     * Disable webhook
     */
    private function disableWebhook(): int
    {
        $id = $this->option('id') ?: $this->ask('Webhook ID to disable');

        $webhook = Webhook::find($id);
        if (!$webhook) {
            $this->error("Webhook with ID {$id} not found");
            return Command::FAILURE;
        }

        $webhook->update(['is_active' => false]);
        $this->info("Webhook '{$webhook->name}' disabled successfully");
        return Command::SUCCESS;
    }

    /**
     * Handle invalid action
     */
    private function invalidAction(string $action): int
    {
        $this->error("Invalid action: {$action}");
        $this->info('Available actions: list, create, delete, enable, disable');
        return Command::FAILURE;
    }
}
