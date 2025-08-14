<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Ensure new users inherit DXF access from Dealer parents
        $user->ensureDxfAccessInheritance();
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // If parent_id was changed, ensure DXF inheritance from new parent
        if ($user->wasChanged('parent_id')) {
            $user->ensureDxfAccessInheritance();
        }
    }

    /**
     * Handle the User "saved" event.
     * This fires after both create and update, and after any role/permission changes
     */
    public function saved(User $user): void
    {
        // If this user is a Dealer, sync DXF access to all children
        // This will handle role changes, permission changes, etc.
        if ($user->hasRole('Dealer')) {
            $user->syncChildrenDxfAccess();
        }
        
        // Ensure this user inherits DXF access from dealer parent if applicable
        $user->ensureDxfAccessInheritance();
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
