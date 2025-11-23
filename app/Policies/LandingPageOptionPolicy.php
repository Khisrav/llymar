<?php

namespace App\Policies;

use App\Models\LandingPageOption;
use App\Models\User;

class LandingPageOptionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_landing::page::option');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LandingPageOption $landingPageOption): bool
    {
        return $user->can('view_landing::page::option');
    }

    /**
     * Determine whether the user can create models.
     * ALWAYS FALSE - options are predefined
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LandingPageOption $landingPageOption): bool
    {
        return $user->can('update_landing::page::option');
    }

    /**
     * Determine whether the user can delete the model.
     * ALWAYS FALSE - options are predefined
     */
    public function delete(User $user, LandingPageOption $landingPageOption): bool
    {
        return false;
    }

    /**
     * Determine whether the user can bulk delete.
     * ALWAYS FALSE - options are predefined
     */
    public function deleteAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete.
     * ALWAYS FALSE - options are predefined
     */
    public function forceDelete(User $user, LandingPageOption $landingPageOption): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently bulk delete.
     * ALWAYS FALSE - options are predefined
     */
    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, LandingPageOption $landingPageOption): bool
    {
        return $user->can('restore_landing::page::option');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_landing::page::option');
    }

    /**
     * Determine whether the user can replicate.
     * ALWAYS FALSE - options are predefined
     */
    public function replicate(User $user, LandingPageOption $landingPageOption): bool
    {
        return false;
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_landing::page::option');
    }
}

