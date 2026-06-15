<?php

namespace App\Policies;

use App\Models\RegulationCategory;
use App\Models\User;

class RegulationCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RegulationCategory $regulationCategory): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, RegulationCategory $regulationCategory): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, RegulationCategory $regulationCategory): bool
    {
        return $user->isAdmin();
    }
}
