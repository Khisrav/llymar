<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\AuthProvider;
use App\Models\User;

class AuthProviderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any AuthProvider');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AuthProvider $authprovider): bool
    {
        return $user->checkPermissionTo('view AuthProvider');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create AuthProvider');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AuthProvider $authprovider): bool
    {
        return $user->checkPermissionTo('update AuthProvider');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AuthProvider $authprovider): bool
    {
        return $user->checkPermissionTo('delete AuthProvider');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any AuthProvider');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AuthProvider $authprovider): bool
    {
        return $user->checkPermissionTo('restore AuthProvider');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any AuthProvider');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, AuthProvider $authprovider): bool
    {
        return $user->checkPermissionTo('replicate AuthProvider');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder AuthProvider');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AuthProvider $authprovider): bool
    {
        return $user->checkPermissionTo('force-delete AuthProvider');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any AuthProvider');
    }
}
