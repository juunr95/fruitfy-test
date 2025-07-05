<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureToggle extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'description',
        'message',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Get a feature toggle by key
     */
    public static function findByKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }

    /**
     * Check if a feature is enabled by key
     */
    public static function isEnabled(string $key): bool
    {
        $feature = static::findByKey($key);
        return $feature ? $feature->enabled : false;
    }

    /**
     * Get all enabled features
     */
    public static function getEnabled(): array
    {
        return static::where('enabled', true)
            ->pluck('key')
            ->toArray();
    }

    /**
     * Get all features as key-value pairs
     */
    public static function getAllAsKeyValue(): array
    {
        return static::all()->pluck('enabled', 'key')->toArray();
    }

    /**
     * Get feature message
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Enable this feature
     */
    public function enable(): void
    {
        $this->update(['enabled' => true]);
    }

    /**
     * Disable this feature
     */
    public function disable(): void
    {
        $this->update(['enabled' => false]);
    }
}
