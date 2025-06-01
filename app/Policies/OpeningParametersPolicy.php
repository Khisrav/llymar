<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\OpeningParameters;
use App\Models\User;

class OpeningParametersPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any OpeningParameters');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OpeningParameters $openingparameters): bool
    {
        return $user->checkPermissionTo('view OpeningParameters');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create OpeningParameters');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OpeningParameters $openingparameters): bool
    {
        return $user->checkPermissionTo('update OpeningParameters');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OpeningParameters $openingparameters): bool
    {
        return $user->checkPermissionTo('delete OpeningParameters');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any OpeningParameters');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OpeningParameters $openingparameters): bool
    {
        return $user->checkPermissionTo('restore OpeningParameters');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any OpeningParameters');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, OpeningParameters $openingparameters): bool
    {
        return $user->checkPermissionTo('replicate OpeningParameters');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder OpeningParameters');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OpeningParameters $openingparameters): bool
    {
        return $user->checkPermissionTo('force-delete OpeningParameters');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any OpeningParameters');
    }
}
