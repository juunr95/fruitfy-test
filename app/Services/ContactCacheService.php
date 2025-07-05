<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ContactCacheService
{
    /**
     * Cache duration in minutes
     */
    private const CACHE_DURATION = 10;

    /**
     * Cache prefix for contact listings
     */
    private const CACHE_PREFIX = 'contacts_index';

    /**
     * Generate cache key based on request parameters
     */
    public function generateCacheKey(Request $request): string
    {
        // Get all query parameters that affect the result
        $params = [
            'search' => $request->get('search'),
            'sort_by' => $request->get('sort_by'),
            'sort_direction' => $request->get('sort_direction'),
            'per_page' => $request->get('per_page', 10),
            'page' => $request->get('page', 1),
        ];

        // Remove null values and sort for consistent key
        $params = array_filter($params, fn($value) => !is_null($value));
        ksort($params);

        // Create hash of parameters
        $paramHash = md5(serialize($params));
        
        return self::CACHE_PREFIX . ":{$paramHash}";
    }

    /**
     * Remember a value in cache
     */
    public function remember(string $key, callable $callback)
    {
        return Cache::remember($key, now()->addMinutes(self::CACHE_DURATION), $callback);
    }

    /**
     * Clear all contact-related cache
     */
    public function clearAll(): void
    {
        // Get all cache keys that start with our prefix
        $this->clearByPattern(self::CACHE_PREFIX . ':*');
    }

    /**
     * Clear cache by pattern (for file/array cache drivers)
     */
    private function clearByPattern(string $pattern): void
    {
        // For production with Redis, you could use:
        // Cache::tags(['contacts'])->flush();
        
        // For file/array cache, we'll clear all cache
        // In a real application, you might want to use cache tags or a more sophisticated approach
        Cache::flush();
    }

    /**
     * Get cache statistics (useful for debugging)
     */
    public function getStats(): array
    {
        // This is a simplified version - in production you might want more detailed stats
        return [
            'cache_driver' => config('cache.default'),
            'cache_duration_minutes' => self::CACHE_DURATION,
            'cache_prefix' => self::CACHE_PREFIX,
        ];
    }

    /**
     * Check if caching is enabled
     */
    public function isEnabled(): bool
    {
        return config('cache.default') !== 'array' || app()->environment('testing');
    }

    /**
     * Warm up cache for common queries
     */
    public function warmUp(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // Common queries to pre-cache
        $commonQueries = [
            [], // Default query
            ['per_page' => 20], // Larger page size
            ['sort_by' => 'name', 'sort_direction' => 'asc'], // Sorted by name
            ['sort_by' => 'created_at', 'sort_direction' => 'desc'], // Sorted by date
        ];

        foreach ($commonQueries as $queryParams) {
            $request = new Request($queryParams);
            $cacheKey = $this->generateCacheKey($request);
            
            // Only warm up if not already cached
            if (!Cache::has($cacheKey)) {
                // You would call the actual query logic here
                // For now, we'll just mark the intent
                logger("Would warm up cache for key: {$cacheKey}");
            }
        }
    }
} 