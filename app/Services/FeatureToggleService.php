<?php

namespace App\Services;

use App\Exceptions\FeatureDisabledException;
use App\Models\FeatureToggle;
use Illuminate\Support\Facades\Cache;

class FeatureToggleService
{
    private const CACHE_KEY = 'feature_toggles';
    private const CACHE_TTL = 60; // 1 minute

    /**
     * Get all feature toggles from cache or database
     */
    private static function getFeatureToggles(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return FeatureToggle::all()->mapWithKeys(fn ($toggle) => [
                $toggle->key => [
                    'enabled' => $toggle->enabled,
                    'message' => $toggle->message,
                    'description' => $toggle->description,
                ]
            ])->toArray();
        });
    }

    /**
     * Clear feature toggles cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Check if a feature is enabled
     *
     * @param string $feature
     * @return bool
     */
    public static function isEnabled(string $feature): bool
    {
        $features = self::getFeatureToggles();
        return $features[$feature]['enabled'] ?? false;
    }

    /**
     * Check if a feature is disabled
     *
     * @param string $feature
     * @return bool
     */
    public static function isDisabled(string $feature): bool
    {
        return !self::isEnabled($feature);
    }

    /**
     * Get feature message
     */
    public static function getMessage(string $feature): ?string
    {
        $features = self::getFeatureToggles();
        return $features[$feature]['message'] ?? null;
    }

    /**
     * Ensure feature is enabled or throw exception
     */
    public static function ensureEnabled(string $feature): void
    {
        if (self::isDisabled($feature)) {
            $message = self::getMessage($feature);
            throw new FeatureDisabledException($feature, $message);
        }
    }

    /**
     * Check if contacts can be created
     *
     * @return bool
     */
    public static function canCreateContacts(): bool
    {
        return self::isEnabled('contacts.can_create');
    }

    /**
     * Check if contacts can be updated
     *
     * @return bool
     */
    public static function canUpdateContacts(): bool
    {
        return self::isEnabled('contacts.can_update');
    }

    /**
     * Check if contacts can be deleted
     *
     * @return bool
     */
    public static function canDeleteContacts(): bool
    {
        return self::isEnabled('contacts.can_delete');
    }

    /**
     * Ensure contacts can be created
     */
    public static function ensureCanCreateContacts(): void
    {
        self::ensureEnabled('contacts.can_create');
    }

    /**
     * Ensure contacts can be updated
     */
    public static function ensureCanUpdateContacts(): void
    {
        self::ensureEnabled('contacts.can_update');
    }

    /**
     * Ensure contacts can be deleted
     */
    public static function ensureCanDeleteContacts(): void
    {
        self::ensureEnabled('contacts.can_delete');
    }

    /**
     * Get all contact feature toggles
     *
     * @return array
     */
    public static function getContactFeatures(): array
    {
        return [
            'can_create' => self::canCreateContacts(),
            'can_update' => self::canUpdateContacts(),
            'can_delete' => self::canDeleteContacts(),
        ];
    }

    /**
     * Get all feature toggles for frontend
     */
    public static function getAllFeatures(): array
    {
        $features = self::getFeatureToggles();
        
        return [
            'contacts' => [
                'can_create' => $features['contacts.can_create']['enabled'] ?? false,
                'can_update' => $features['contacts.can_update']['enabled'] ?? false,
                'can_delete' => $features['contacts.can_delete']['enabled'] ?? false,
            ]
        ];
    }
} 