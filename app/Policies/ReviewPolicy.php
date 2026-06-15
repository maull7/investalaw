<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Review $review): bool
    {
        return $user->id === $review->reviewer_id
            || $user->id === $review->reviewDocument->user_id
            || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isReviewer() || $user->isAdmin();
    }

    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->reviewer_id && ! $review->isCompleted();
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }
}
