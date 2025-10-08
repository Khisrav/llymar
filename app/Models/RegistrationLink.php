<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RegistrationLink extends Model
{
    protected $fillable = [
        'token',
        'created_by',
        'reward_fee',
        'can_access_dxf',
        'is_used',
        'used_at',
        'registered_user_id',
        'expires_at',
    ];

    protected $casts = [
        'reward_fee' => 'decimal:2',
        'can_access_dxf' => 'boolean',
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->token) {
                $model->token = Str::random(64);
            }
            if (!$model->expires_at) {
                $model->expires_at = now()->addHours(24);
            }
        });
    }

    /**
     * Get the user who created this registration link
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who registered using this link
     */
    public function registeredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_user_id');
    }

    /**
     * Check if the link is still valid
     */
    public function isValid(): bool
    {
        return !$this->is_used && $this->expires_at->isFuture();
    }

    /**
     * Mark the link as used
     */
    public function markAsUsed(User $user): void
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
            'registered_user_id' => $user->id,
        ]);
    }

    /**
     * Get the full registration URL
     */
    public function getUrlAttribute(): string
    {
        return url("/register/{$this->token}");
    }

    /**
     * Scope for valid links only
     */
    public function scopeValid($query)
    {
        return $query->where('is_used', false)
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope for expired links
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now())
                    ->where('is_used', false);
    }
}
