<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'events',
        'secret',
        'is_active',
        'last_response_status',
        'last_triggered_at',
        'retry_count',
        'max_retries',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
        'retry_count' => 'integer',
        'max_retries' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'retry_count' => 0,
        'max_retries' => 3,
    ];

    /**
     * Check if webhook should be triggered for given event
     */
    public function shouldTriggerFor(string $event): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (empty($this->events)) {
            return true; // Trigger for all events if none specified
        }

        return in_array($event, $this->events);
    }

    /**
     * Increment retry count
     */
    public function incrementRetryCount(): void
    {
        $this->increment('retry_count');
    }

    /**
     * Reset retry count
     */
    public function resetRetryCount(): void
    {
        $this->update(['retry_count' => 0]);
    }

    /**
     * Check if max retries exceeded
     */
    public function maxRetriesExceeded(): bool
    {
        return $this->retry_count > $this->max_retries;
    }

    /**
     * Update last trigger info
     */
    public function updateLastTrigger(int $statusCode): void
    {
        $this->update([
            'last_response_status' => $statusCode,
            'last_triggered_at' => now(),
        ]);
    }
} 