<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\LlymarCalculatorItem;
use App\Models\User;

class LlymarCalculatorItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any LlymarCalculatorItem');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LlymarCalculatorItem $llymarcalculatoritem): bool
    {
        return $user->checkPermissionTo('view LlymarCalculatorItem');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create LlymarCalculatorItem');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LlymarCalculatorItem $llymarcalculatoritem): bool
    {
        return $user->checkPermissionTo('update LlymarCalculatorItem');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LlymarCalculatorItem $llymarcalculatoritem): bool
    {
        return $user->checkPermissionTo('delete LlymarCalculatorItem');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any LlymarCalculatorItem');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LlymarCalculatorItem $llymarcalculatoritem): bool
    {
        return $user->checkPermissionTo('restore LlymarCalculatorItem');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any LlymarCalculatorItem');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, LlymarCalculatorItem $llymarcalculatoritem): bool
    {
        return $user->checkPermissionTo('replicate LlymarCalculatorItem');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder LlymarCalculatorItem');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LlymarCalculatorItem $llymarcalculatoritem): bool
    {
        return $user->checkPermissionTo('force-delete LlymarCalculatorItem');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any LlymarCalculatorItem');
    }
}
