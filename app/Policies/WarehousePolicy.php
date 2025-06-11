<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Warehouse;
use App\Models\User;

class WarehousePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Warehouse');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->checkPermissionTo('view Warehouse');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Warehouse');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->checkPermissionTo('update Warehouse');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->checkPermissionTo('delete Warehouse');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Warehouse');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Warehouse $warehouse): bool
    {
        return $user->checkPermissionTo('restore Warehouse');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Warehouse');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Warehouse $warehouse): bool
    {
        return $user->checkPermissionTo('replicate Warehouse');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Warehouse');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Warehouse $warehouse): bool
    {
        return $user->checkPermissionTo('force-delete Warehouse');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Warehouse');
    }
}
