<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ItemProperty;
use App\Models\User;

class ItemPropertyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any ItemProperty');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ItemProperty $itemproperty): bool
    {
        return $user->checkPermissionTo('view ItemProperty');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ItemProperty');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ItemProperty $itemproperty): bool
    {
        return $user->checkPermissionTo('update ItemProperty');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ItemProperty $itemproperty): bool
    {
        return $user->checkPermissionTo('delete ItemProperty');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any ItemProperty');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ItemProperty $itemproperty): bool
    {
        return $user->checkPermissionTo('restore ItemProperty');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any ItemProperty');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, ItemProperty $itemproperty): bool
    {
        return $user->checkPermissionTo('replicate ItemProperty');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder ItemProperty');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ItemProperty $itemproperty): bool
    {
        return $user->checkPermissionTo('force-delete ItemProperty');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any ItemProperty');
    }
}
