<?php

namespace App\Services;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\ReviewFinding;
use App\Repositories\ReviewDocumentRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function __construct(
        private readonly ReviewRepository $reviewRepository,
        private readonly ReviewDocumentRepository $reviewDocumentRepository
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $findings
     */
    public function createReview(array $data, array $findings, int $reviewerId): Review
    {
        return DB::transaction(function () use ($data, $findings, $reviewerId) {
            $data['reviewer_id'] = $reviewerId;
            $review = $this->reviewRepository->create($data);

            foreach ($findings as $finding) {
                $finding['review_id'] = $review->id;
                ReviewFinding::create($finding);
            }

            $this->reviewDocumentRepository->updateStatus(
                $review->reviewDocument,
                ReviewStatus::InReview->value
            );

            return $review;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $findings
     */
    public function updateReview(Review $review, array $data, array $findings): Review
    {
        return DB::transaction(function () use ($review, $data, $findings) {
            $review = $this->reviewRepository->update($review, $data);

            $review->findings()->delete();

            foreach ($findings as $finding) {
                $finding['review_id'] = $review->id;
                ReviewFinding::create($finding);
            }

            return $review->fresh(['findings.category']);
        });
    }

    public function completeReview(Review $review): Review
    {
        return DB::transaction(function () use ($review) {
            $review = $this->reviewRepository->complete($review);

            $this->reviewDocumentRepository->updateStatus(
                $review->reviewDocument,
                ReviewStatus::Approved->value
            );

            return $review;
        });
    }

    public function requestRevision(Review $review, string $notes): Review
    {
        return DB::transaction(function () use ($review, $notes) {
            $review->notes = $notes;
            $review->save();

            $this->reviewDocumentRepository->updateStatus(
                $review->reviewDocument,
                ReviewStatus::RevisionRequired->value
            );

            return $review->fresh();
        });
    }

    public function rejectReview(Review $review, string $notes): Review
    {
        return DB::transaction(function () use ($review, $notes) {
            $review->notes = $notes;
            $review->save();

            $this->reviewDocumentRepository->updateStatus(
                $review->reviewDocument,
                ReviewStatus::Rejected->value
            );

            return $review->fresh();
        });
    }
}
