<?php

namespace App\Policies;

use App\Models\ReviewDocument;
use App\Models\User;

class ReviewDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ReviewDocument $reviewDocument): bool
    {
        return $user->id === $reviewDocument->user_id || $user->isReviewer() || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ReviewDocument $reviewDocument): bool
    {
        return $user->id === $reviewDocument->user_id && $reviewDocument->isDraft();
    }

    public function delete(User $user, ReviewDocument $reviewDocument): bool
    {
        return $user->id === $reviewDocument->user_id && $reviewDocument->isDraft();
    }

    public function submit(User $user, ReviewDocument $reviewDocument): bool
    {
        return $user->id === $reviewDocument->user_id && $reviewDocument->isDraft();
    }

    public function review(User $user, ReviewDocument $reviewDocument): bool
    {
        return ($user->isReviewer() || $user->isAdmin()) && $reviewDocument->canBeReviewed();
    }
}
