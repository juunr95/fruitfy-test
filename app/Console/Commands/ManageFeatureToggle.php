<?php

namespace App\Console\Commands;

use App\Models\FeatureToggle;
use App\Services\FeatureToggleService;
use Illuminate\Console\Command;

class ManageFeatureToggle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feature:toggle 
                            {action : The action to perform (enable, disable, list, clear-cache)}
                            {key? : The feature toggle key}
                            {--message= : Optional message for the feature toggle}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage feature toggles (enable, disable, list, clear-cache)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $key = $this->argument('key');

        switch ($action) {
            case 'enable':
                return $this->enableFeature($key);
            case 'disable':
                return $this->disableFeature($key);
            case 'list':
                return $this->listFeatures();
            case 'clear-cache':
                return $this->clearCache();
            default:
                $this->error('Invalid action. Use: enable, disable, list, or clear-cache');
                return 1;
        }
    }

    private function enableFeature($key)
    {
        if (!$key) {
            $this->error('Feature key is required for enable action');
            return 1;
        }

        $feature = FeatureToggle::findByKey($key);
        if (!$feature) {
            $this->error("Feature toggle '{$key}' not found");
            return 1;
        }

        $feature->enable();
        FeatureToggleService::clearCache();
        
        $this->info("Feature toggle '{$key}' has been enabled");
        return 0;
    }

    private function disableFeature($key)
    {
        if (!$key) {
            $this->error('Feature key is required for disable action');
            return 1;
        }

        $feature = FeatureToggle::findByKey($key);
        if (!$feature) {
            $this->error("Feature toggle '{$key}' not found");
            return 1;
        }

        // Update message if provided
        $message = $this->option('message');
        if ($message) {
            $feature->update(['message' => $message]);
        }

        $feature->disable();
        FeatureToggleService::clearCache();
        
        $this->info("Feature toggle '{$key}' has been disabled");
        return 0;
    }

    private function listFeatures()
    {
        $features = FeatureToggle::all();
        
        if ($features->isEmpty()) {
            $this->info('No feature toggles found');
            return 0;
        }

        $this->table(
            ['Key', 'Description', 'Status', 'Message'],
            $features->map(function ($feature) {
                return [
                    $feature->key,
                    $feature->description,
                    $feature->enabled ? '✅ Enabled' : '❌ Disabled',
                    $feature->message ?? 'N/A'
                ];
            })
        );

        return 0;
    }

    private function clearCache()
    {
        FeatureToggleService::clearCache();
        $this->info('Feature toggles cache has been cleared');
        return 0;
    }
}
