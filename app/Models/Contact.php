<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @OA\Schema(
 *     schema="Contact",
 *     type="object",
 *     title="Contact",
 *     description="Contact model",
 *     @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 *     @OA\Property(property="name", type="string", maxLength=255, example="João Silva"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, example="joao@example.com"),
 *     @OA\Property(property="phone", type="string", maxLength=20, example="(11) 99999-9999"),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly="true"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly="true")
 * )
 * 
 * @OA\Schema(
 *     schema="ContactStore",
 *     type="object",
 *     required={"name", "email", "phone"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="João Silva"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, example="joao@example.com"),
 *     @OA\Property(property="phone", type="string", maxLength=20, example="(11) 99999-9999")
 * )
 * 
 * @OA\Schema(
 *     schema="ContactUpdate",
 *     type="object",
 *     required={"name", "email", "phone"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="João Silva"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, example="joao@example.com"),
 *     @OA\Property(property="phone", type="string", maxLength=20, example="(11) 99999-9999")
 * )
 */
class Contact extends Model implements Auditable
{
    /** @use HasFactory<\Database\Factories\ContactFactory> */
    use HasFactory, AuditableTrait;

    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Auditable attributes
     */
    protected $auditInclude = [
        'name',
        'email',
        'phone',
    ];

    /**
     * Auditable events
     */
    protected $auditEvents = [
        'created',
        'updated',
        'deleted',
    ];

    /**
     * Set the phone attribute by removing non-numeric characters.
     */
    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = preg_replace('/\D/', '', $value);
    }

    /**
     * Boot the model and register audit events
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            \OwenIt\Auditing\Models\Audit::create([
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'event' => 'created',
                'old_values' => [],
                'new_values' => $model->getAttributes(),
                'url' => request()->fullUrl() ?? null,
                'ip_address' => request()->ip() ?? null,
                'user_agent' => request()->userAgent() ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        static::updated(function ($model) {
            \OwenIt\Auditing\Models\Audit::create([
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'event' => 'updated',
                'old_values' => $model->getOriginal(),
                'new_values' => $model->getDirty(),
                'url' => request()->fullUrl() ?? null,
                'ip_address' => request()->ip() ?? null,
                'user_agent' => request()->userAgent() ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        static::deleted(function ($model) {
            \OwenIt\Auditing\Models\Audit::create([
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'event' => 'deleted',
                'old_values' => $model->getAttributes(),
                'new_values' => [],
                'url' => request()->fullUrl() ?? null,
                'ip_address' => request()->ip() ?? null,
                'user_agent' => request()->userAgent() ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }
}
