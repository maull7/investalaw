<?php

namespace App\Repositories;

use App\Models\ReviewDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewDocumentRepository
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ReviewDocument::with(['user', 'categories']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): ReviewDocument
    {
        return ReviewDocument::with(['user', 'categories', 'review.findings.category'])->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): ReviewDocument
    {
        return ReviewDocument::create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(ReviewDocument $document, array $data): ReviewDocument
    {
        $document->update($data);

        return $document->fresh();
    }

    public function delete(ReviewDocument $document): bool
    {
        return $document->delete();
    }

    public function updateStatus(ReviewDocument $document, string $status, ?string $submittedAt = null): ReviewDocument
    {
        $document->status = $status;

        if ($submittedAt) {
            $document->submitted_at = $submittedAt;
        }

        $document->save();

        return $document->fresh();
    }
}
