<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\OrderOpening;
use App\Models\User;

class OrderOpeningPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any OrderOpening');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrderOpening $orderopening): bool
    {
        return $user->checkPermissionTo('view OrderOpening');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create OrderOpening');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrderOpening $orderopening): bool
    {
        return $user->checkPermissionTo('update OrderOpening');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrderOpening $orderopening): bool
    {
        return $user->checkPermissionTo('delete OrderOpening');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any OrderOpening');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OrderOpening $orderopening): bool
    {
        return $user->checkPermissionTo('restore OrderOpening');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any OrderOpening');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, OrderOpening $orderopening): bool
    {
        return $user->checkPermissionTo('replicate OrderOpening');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder OrderOpening');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OrderOpening $orderopening): bool
    {
        return $user->checkPermissionTo('force-delete OrderOpening');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any OrderOpening');
    }
}
