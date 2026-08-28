<?php

namespace App\Policies;

use App\Models\Regulation;
use App\Models\User;

class RegulationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Regulation $regulation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Regulation $regulation): bool
    {
        return false;
    }

    public function delete(User $user, Regulation $regulation): bool
    {
        return $regulation->created_by === null
            || $user->isAdmin()
            || ($user->isSubAdmin() && $regulation->created_by === $user->id);
    }
}
