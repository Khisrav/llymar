<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'default_factor',
        'discount',
        'company',
        'phone',
        'address',
        'role',
        'inn',
        'kpp',
        'current_account',
        'correspondent_account',
        'bik',
        'bank',
        'legal_address',
        'telegram',
        'parent_id',
        'reward_fee',
        'country',
        'region',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
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
    
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->can('access admin panel');
    }
    
    /**
     * Relationship: User has many Orders.
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    
    /**
     * Relationship: User belongs to a parent User.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
    
    /**
     * Relationship: User has many child Users.
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }
    
    /**
     * Relationship: User has many Commission Credits as initiator.
     *
     * @return HasMany
     */
    public function initiatedCommissions(): HasMany
    {
        return $this->hasMany(ComissionCredits::class, 'user_id');
    }
    
    /**
     * Relationship: User has many Commission Credits as recipient.
     *
     * @return HasMany
     */
    public function receivedCommissions(): HasMany
    {
        return $this->hasMany(ComissionCredits::class, 'parent_id');
    }
}
