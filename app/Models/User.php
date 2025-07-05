<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, AuditableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Auditable attributes
     */
    protected $auditInclude = [
        'name',
        'email',
    ];

    /**
     * Auditable exclude (para não auditar senhas)
     */
    protected $auditExclude = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot the model and register audit events
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $attributes = $model->getAttributes();
            // Remove sensitive data from audit
            unset($attributes['password'], $attributes['remember_token']);

            \OwenIt\Auditing\Models\Audit::create([
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'event' => 'created',
                'old_values' => [],
                'new_values' => $attributes,
                'url' => request()->fullUrl() ?? null,
                'ip_address' => request()->ip() ?? null,
                'user_agent' => request()->userAgent() ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        static::updated(function ($model) {
            $original = $model->getOriginal();
            $dirty = $model->getDirty();
            
            // Remove sensitive data from audit
            unset($original['password'], $original['remember_token']);
            unset($dirty['password'], $dirty['remember_token']);

            if (!empty($dirty)) {
                \OwenIt\Auditing\Models\Audit::create([
                    'auditable_type' => get_class($model),
                    'auditable_id' => $model->id,
                    'event' => 'updated',
                    'old_values' => $original,
                    'new_values' => $dirty,
                    'url' => request()->fullUrl() ?? null,
                    'ip_address' => request()->ip() ?? null,
                    'user_agent' => request()->userAgent() ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        static::deleted(function ($model) {
            $attributes = $model->getAttributes();
            // Remove sensitive data from audit
            unset($attributes['password'], $attributes['remember_token']);

            \OwenIt\Auditing\Models\Audit::create([
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'event' => 'deleted',
                'old_values' => $attributes,
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
