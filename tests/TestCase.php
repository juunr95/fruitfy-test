<?php

namespace Tests;

use App\Models\FeatureToggle;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Configura feature toggles habilitados por padrão para testes
        $this->enableFeatureToggles();
    }

    /**
     * Enable feature toggles for testing
     */
    protected function enableFeatureToggles(array $features = null): void
    {
        $defaultFeatures = [
            'contacts.can_create',
            'contacts.can_update',
            'contacts.can_delete',
        ];

        $featuresToEnable = $features ?? $defaultFeatures;

        foreach ($featuresToEnable as $feature) {
            FeatureToggle::updateOrCreate(
                ['key' => $feature],
                [
                    'key' => $feature,
                    'description' => "Feature toggle for {$feature}",
                    'message' => "Feature {$feature} is disabled.",
                    'enabled' => true,
                ]
            );
        }
    }

    /**
     * Disable specific feature toggles for testing
     */
    protected function disableFeatureToggles(array $features): void
    {
        foreach ($features as $feature) {
            FeatureToggle::updateOrCreate(
                ['key' => $feature],
                [
                    'key' => $feature,
                    'description' => "Feature toggle for {$feature}",
                    'message' => "Feature {$feature} is disabled.",
                    'enabled' => false,
                ]
            );
        }
    }

    /**
     * Enable a specific feature toggle
     */
    protected function enableFeatureToggle(string $feature): void
    {
        $this->enableFeatureToggles([$feature]);
    }

    /**
     * Disable a specific feature toggle
     */
    protected function disableFeatureToggle(string $feature): void
    {
        $this->disableFeatureToggles([$feature]);
    }
}
