<?php

namespace App\Repositories;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewRepository
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Review::with(['reviewDocument.user', 'reviewer', 'findings.regulation.type']);

        if (! empty($filters['reviewer_id'])) {
            $query->where('reviewer_id', $filters['reviewer_id']);
        }

        if (! empty($filters['review_document_id'])) {
            $query->where('review_document_id', $filters['review_document_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): Review
    {
        return Review::with(['reviewDocument.regulations.type', 'reviewDocument.regulations.category', 'reviewer', 'findings.category', 'findings.regulation.type'])->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Review
    {
        return Review::create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Review $review, array $data): Review
    {
        $review->update($data);

        return $review->fresh();
    }

    public function delete(Review $review): bool
    {
        return $review->delete();
    }

    public function complete(Review $review): Review
    {
        $review->completed_at = now();
        $review->save();

        return $review->fresh();
    }
}
