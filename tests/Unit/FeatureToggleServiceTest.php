<?php

namespace Tests\Unit;

use App\Exceptions\FeatureDisabledException;
use App\Models\FeatureToggle;
use App\Services\FeatureToggleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeatureToggleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Limpar cache antes de cada teste para evitar interferência
        FeatureToggleService::clearCache();
    }

    #[Test]
    public function it_should_detect_enabled_feature(): void
    {
        FeatureToggle::create([
            'key' => 'test.feature',
            'description' => 'Test feature',
            'message' => 'Test feature message',
            'enabled' => true,
        ]);

        $this->assertTrue(FeatureToggleService::isEnabled('test.feature'));
        $this->assertFalse(FeatureToggleService::isDisabled('test.feature'));
    }

    #[Test]
    public function it_should_detect_disabled_feature(): void
    {
        FeatureToggle::create([
            'key' => 'test.feature',
            'description' => 'Test feature',
            'message' => 'Test feature message',
            'enabled' => false,
        ]);

        $this->assertFalse(FeatureToggleService::isEnabled('test.feature'));
        $this->assertTrue(FeatureToggleService::isDisabled('test.feature'));
    }

    #[Test]
    public function it_should_return_false_for_non_existent_feature(): void
    {
        $this->assertFalse(FeatureToggleService::isEnabled('non.existent.feature'));
        $this->assertTrue(FeatureToggleService::isDisabled('non.existent.feature'));
    }

    #[Test]
    public function it_should_throw_exception_when_feature_is_disabled(): void
    {
        FeatureToggle::create([
            'key' => 'test.feature',
            'description' => 'Test feature',
            'message' => 'Feature is disabled',
            'enabled' => false,
        ]);

        $this->expectException(FeatureDisabledException::class);
        
        FeatureToggleService::ensureEnabled('test.feature');
    }

    #[Test]
    public function it_should_not_throw_exception_when_feature_is_enabled(): void
    {
        FeatureToggle::create([
            'key' => 'test.feature',
            'description' => 'Test feature',
            'message' => 'Feature is enabled',
            'enabled' => true,
        ]);

        // Should not throw exception
        FeatureToggleService::ensureEnabled('test.feature');
        
        $this->assertTrue(true);
    }

    #[Test]
    public function it_should_get_feature_message(): void
    {
        FeatureToggle::create([
            'key' => 'test.feature',
            'description' => 'Test feature',
            'message' => 'Custom message',
            'enabled' => false,
        ]);

        $this->assertEquals('Custom message', FeatureToggleService::getMessage('test.feature'));
    }

    #[Test]
    public function it_should_return_null_for_non_existent_feature_message(): void
    {
        $this->assertNull(FeatureToggleService::getMessage('non.existent.feature'));
    }

    #[Test]
    public function it_should_check_contacts_features(): void
    {
        $this->assertTrue(FeatureToggleService::canCreateContacts());
        $this->assertTrue(FeatureToggleService::canUpdateContacts());
        $this->assertTrue(FeatureToggleService::canDeleteContacts());
    }

    #[Test]
    public function it_should_ensure_contacts_features(): void
    {
        // Should not throw exceptions since features are enabled by default in tests
        FeatureToggleService::ensureCanCreateContacts();
        FeatureToggleService::ensureCanUpdateContacts();
        FeatureToggleService::ensureCanDeleteContacts();
        
        $this->assertTrue(true);
    }

    #[Test]
    public function it_should_throw_exception_when_create_contacts_is_disabled(): void
    {
        $this->disableFeatureToggle('contacts.can_create');

        $this->expectException(FeatureDisabledException::class);
        
        FeatureToggleService::ensureCanCreateContacts();
    }

    #[Test]
    public function it_should_throw_exception_when_update_contacts_is_disabled(): void
    {
        $this->disableFeatureToggle('contacts.can_update');

        $this->expectException(FeatureDisabledException::class);
        
        FeatureToggleService::ensureCanUpdateContacts();
    }

    #[Test]
    public function it_should_throw_exception_when_delete_contacts_is_disabled(): void
    {
        $this->disableFeatureToggle('contacts.can_delete');

        $this->expectException(FeatureDisabledException::class);
        
        FeatureToggleService::ensureCanDeleteContacts();
    }

    #[Test]
    public function it_should_get_contact_features(): void
    {
        $features = FeatureToggleService::getContactFeatures();

        $this->assertEquals([
            'can_create' => true,
            'can_update' => true,
            'can_delete' => true,
        ], $features);
    }

    #[Test]
    public function it_should_get_all_features_for_frontend(): void
    {
        $features = FeatureToggleService::getAllFeatures();

        $this->assertEquals([
            'contacts' => [
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true,
            ]
        ], $features);
    }

    #[Test]
    public function it_should_clear_cache(): void
    {
        // Put something in cache
        Cache::put('feature_toggles', ['test' => 'value'], 60);
        
        $this->assertNotNull(Cache::get('feature_toggles'));
        
        FeatureToggleService::clearCache();
        
        $this->assertNull(Cache::get('feature_toggles'));
    }

    #[Test]
    public function it_should_use_cache_for_feature_toggles(): void
    {
        // Create a feature toggle
        FeatureToggle::create([
            'key' => 'test.cached',
            'description' => 'Test cached feature',
            'message' => 'Cached message',
            'enabled' => true,
        ]);

        // First call should query database and cache
        $this->assertTrue(FeatureToggleService::isEnabled('test.cached'));
        
        // Delete the feature from database
        FeatureToggle::where('key', 'test.cached')->delete();
        
        // Second call should use cache and still return true
        $this->assertTrue(FeatureToggleService::isEnabled('test.cached'));
    }
} 