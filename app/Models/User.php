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
use App\Models\CommissionCredit;
use App\Models\News;

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
     * Relationship: User has many CommercialOffers.
     *
     * @return HasMany
     */
    public function commercialOffers(): HasMany
    {
        return $this->hasMany(CommercialOffer::class);
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
        return $this->hasMany(CommissionCredit::class, 'user_id');
    }
    
    /**
     * Relationship: User has many Commission Credits as recipient.
     *
     * @return HasMany
     */
    public function receivedCommissions(): HasMany
    {
        return $this->hasMany(CommissionCredit::class, 'parent_id');
    }
    
    /**
     * Sync DXF access for all child users when parent is a Dealer
     */
    // public function syncChildrenDxfAccess(): void
    // {
    //     // Only sync if this user is a Dealer
    //     if (!$this->hasRole('Dealer')) {
    //         return;
    //     }
        
    //     $hasParentDxfAccess = $this->can('access dxf');
        
    //     // Get all child users
    //     $children = $this->children;
        
    //     foreach ($children as $child) {
    //         if ($hasParentDxfAccess) {
    //             $child->givePermissionTo('access dxf');
    //         } else {
    //             $child->revokePermissionTo('access dxf');
    //         }
    //     }
    // }
    
    /**
     * Sync app sketcher access for all child users
     */
    public function syncChildrenSketcherAccess(): void
    {
        $hasParentSketcherAccess = $this->can('access app sketcher');
        
        // Get all child users
        $children = $this->children;
        
        foreach ($children as $child) {
            if ($hasParentSketcherAccess) {
                $child->givePermissionTo('access app sketcher');
            } else {
                $child->revokePermissionTo('access app sketcher');
            }
        }
    }
    
    /**
     * Check if user should inherit DXF access from parent
     */
    // public function shouldInheritDxfFromParent(): bool
    // {
    //     if (!$this->parent_id || !$this->parent) {
    //         return false;
    //     }
        
    //     return $this->parent->hasRole('Dealer') && $this->parent->can('access dxf');
    // }
    
    /**
     * Ensure DXF access is inherited from Dealer parent if applicable
     */
    // public function ensureDxfAccessInheritance(): void
    // {
    //     if ($this->shouldInheritDxfFromParent()) {
    //         $this->givePermissionTo('access dxf');
    //     }
    // }
    
    /**
     * Check if user should inherit app sketcher access from parent
     */
    public function shouldInheritSketcherFromParent(): bool
    {
        if (!$this->parent_id || !$this->parent) {
            return false;
        }
        
        return $this->parent->can('access app sketcher');
    }
    
    /**
     * Ensure app sketcher access is inherited from parent if applicable
     */
    public function ensureSketcherAccessInheritance(): void
    {
        if ($this->shouldInheritSketcherFromParent()) {
            $this->givePermissionTo('access app sketcher');
        } else {
            // If parent doesn't have the permission, child shouldn't either
            if ($this->parent_id && $this->parent) {
                $this->revokePermissionTo('access app sketcher');
            }
        }
    }
    
    /**
     * Override syncRoles to handle DXF access syncing
     */
    public function syncRoles($roles)
    {
        $result = parent::syncRoles($roles);
        
        // After role sync, handle DXF access
        // $this->handleDxfAccessAfterRoleChange();
        
        return $result;
    }
    
    /**
     * Override assignRole to handle DXF access syncing
     */
    // public function assignRole($role)
    // {
    //     $result = parent::assignRole($role);
        
    //     // After role assignment, handle DXF access
    //     $this->handleDxfAccessAfterRoleChange();
        
    //     return $result;
    // }
    
    /**
     * Override removeRole to handle DXF access syncing
     */
    public function removeRole($role)
    {
        $result = parent::removeRole($role);
        
        // After role removal, handle DXF access
        // $this->handleDxfAccessAfterRoleChange();
        
        return $result;
    }
    
    /**
     * Handle DXF access syncing after role changes
     */
    // protected function handleDxfAccessAfterRoleChange(): void
    // {
    //     // If this user is now a Dealer, sync DXF access to children
    //     if ($this->hasRole('Dealer')) {
    //         $this->syncChildrenDxfAccess();
    //     } else {
    //         // If this user is no longer a Dealer, revoke DXF access from all children
    //         $children = $this->children;
    //         foreach ($children as $child) {
    //             $child->revokePermissionTo('access dxf');
    //         }
    //     }
        
    //     // Ensure this user inherits DXF access from dealer parent if applicable
    //     $this->ensureDxfAccessInheritance();
    // }

    /**
     * Get the user's news articles.
     */
    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'author_id');
    }

    /**
     * Get commission records received by this user.
     */
}
