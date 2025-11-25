<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\WarehouseRecord;
use App\Models\User;

class WarehouseRecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any WarehouseRecord');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WarehouseRecord $warehouserecord): bool
    {
        return $user->checkPermissionTo('view WarehouseRecord');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create WarehouseRecord');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WarehouseRecord $warehouserecord): bool
    {
        return $user->checkPermissionTo('update WarehouseRecord');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WarehouseRecord $warehouserecord): bool
    {
        return $user->checkPermissionTo('delete WarehouseRecord');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any WarehouseRecord');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WarehouseRecord $warehouserecord): bool
    {
        return $user->checkPermissionTo('restore WarehouseRecord');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any WarehouseRecord');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, WarehouseRecord $warehouserecord): bool
    {
        return $user->checkPermissionTo('replicate WarehouseRecord');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder WarehouseRecord');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WarehouseRecord $warehouserecord): bool
    {
        return $user->checkPermissionTo('force-delete WarehouseRecord');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any WarehouseRecord');
    }
}
