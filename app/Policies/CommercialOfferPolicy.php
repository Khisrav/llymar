<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\CommercialOffer;
use App\Models\User;

class CommercialOfferPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any CommercialOffer');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CommercialOffer $commercialoffer): bool
    {
        return $user->checkPermissionTo('view CommercialOffer');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create CommercialOffer');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CommercialOffer $commercialoffer): bool
    {
        return $user->checkPermissionTo('update CommercialOffer');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CommercialOffer $commercialoffer): bool
    {
        return $user->checkPermissionTo('delete CommercialOffer');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any CommercialOffer');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CommercialOffer $commercialoffer): bool
    {
        return $user->checkPermissionTo('restore CommercialOffer');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any CommercialOffer');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, CommercialOffer $commercialoffer): bool
    {
        return $user->checkPermissionTo('replicate CommercialOffer');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder CommercialOffer');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CommercialOffer $commercialoffer): bool
    {
        return $user->checkPermissionTo('force-delete CommercialOffer');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any CommercialOffer');
    }
}
