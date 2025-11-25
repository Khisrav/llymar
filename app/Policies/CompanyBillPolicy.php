<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\CompanyBill;
use App\Models\User;

class CompanyBillPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any CompanyBill');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CompanyBill $companybill): bool
    {
        return $user->checkPermissionTo('view CompanyBill');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create CompanyBill');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CompanyBill $companybill): bool
    {
        return $user->checkPermissionTo('update CompanyBill');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CompanyBill $companybill): bool
    {
        return $user->checkPermissionTo('delete CompanyBill');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any CompanyBill');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CompanyBill $companybill): bool
    {
        return $user->checkPermissionTo('restore CompanyBill');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any CompanyBill');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, CompanyBill $companybill): bool
    {
        return $user->checkPermissionTo('replicate CompanyBill');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder CompanyBill');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CompanyBill $companybill): bool
    {
        return $user->checkPermissionTo('force-delete CompanyBill');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any CompanyBill');
    }
}
