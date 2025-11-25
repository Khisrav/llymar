<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ContractTemplate;
use App\Models\User;

class ContractTemplatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any ContractTemplate');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ContractTemplate $contracttemplate): bool
    {
        return $user->checkPermissionTo('view ContractTemplate');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ContractTemplate');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ContractTemplate $contracttemplate): bool
    {
        return $user->checkPermissionTo('update ContractTemplate');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ContractTemplate $contracttemplate): bool
    {
        return $user->checkPermissionTo('delete ContractTemplate');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any ContractTemplate');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ContractTemplate $contracttemplate): bool
    {
        return $user->checkPermissionTo('restore ContractTemplate');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any ContractTemplate');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, ContractTemplate $contracttemplate): bool
    {
        return $user->checkPermissionTo('replicate ContractTemplate');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder ContractTemplate');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ContractTemplate $contracttemplate): bool
    {
        return $user->checkPermissionTo('force-delete ContractTemplate');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any ContractTemplate');
    }
}
