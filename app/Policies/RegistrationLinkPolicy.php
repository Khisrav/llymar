<?php

namespace App\Policies;

use App\Models\RegistrationLink;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RegistrationLinkPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Super-Admin, Operator, ROP, and Dealer can view registration links
        return $user->hasAnyRole(['Super-Admin', 'Operator', 'ROP', 'Dealer']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RegistrationLink $registrationLink): bool
    {
        // Super-Admin can view all
        if ($user->hasRole('Super-Admin')) {
            return true;
        }

        // Others can only view their own created links
        return $registrationLink->created_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Super-Admin, Operator, ROP, and Dealer can create registration links
        return $user->hasAnyRole(['Super-Admin', 'Operator', 'ROP', 'Dealer']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RegistrationLink $registrationLink): bool
    {
        // Super-Admin can update all
        if ($user->hasRole('Super-Admin')) {
            return true;
        }

        // Others can only update their own created links
        return $registrationLink->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RegistrationLink $registrationLink): bool
    {
        // Super-Admin can delete all
        if ($user->hasRole('Super-Admin')) {
            return true;
        }

        // Others can only delete their own created links
        return $registrationLink->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RegistrationLink $registrationLink): bool
    {
        return $user->hasRole('Super-Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RegistrationLink $registrationLink): bool
    {
        return $user->hasRole('Super-Admin');
    }
}
