<?php

namespace App\Http\Controllers;

use App\Enums\ComplianceStatus;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Models\Review;
use App\Models\ReviewDocument;
use App\Models\UserActivityLog;
use App\Repositories\ReviewDocumentRepository;
use App\Repositories\ReviewRepository;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewRepository $reviewRepository,
        private readonly ReviewDocumentRepository $reviewDocumentRepository,
        private readonly ReviewService $reviewService
    ) {}

    public function index(Request $request): View
    {
        abort_if($request->user()->isSubAdmin(), 403);
        $user = $request->user();
        $filters = $request->only(['review_document_id']);

        if ($user->isReviewer()) {
            $filters['reviewer_id'] = $user->id;
        }

        $reviews = $this->reviewRepository->search($filters);
        $documents = ReviewDocument::select('id', 'title')->whereIn('status', ['submitted', 'reviewed'])->latest()->get();

        return view('reviews.index', compact('reviews', 'documents'));
    }

    public function create(ReviewDocument $reviewDocument): View
    {
        abort_if(auth()->user()->isSubAdmin(), 403);
        $this->authorize('review', $reviewDocument);

        $document = $this->reviewDocumentRepository->findById($reviewDocument->id);
        $complianceStatuses = ComplianceStatus::cases();

        return view('reviews.create', compact('document', 'complianceStatuses'));
    }

    public function store(StoreReviewRequest $request): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);
        $validated = $request->validated();

        $review = $this->reviewService->createReview(
            ['review_document_id' => $validated['review_document_id'], 'summary' => $validated['summary'], 'notes' => $validated['notes']],
            $validated['findings'],
            $request->user()->id
        );

        UserActivityLog::log('created', Review::class, $review->id, "Membuat review untuk dokumen #{$validated['review_document_id']}");

        return redirect()->route('reviews.show', $review)
            ->with('success', 'Review created successfully.');
    }

    public function show(Review $review): View
    {
        abort_if(auth()->user()->isSubAdmin(), 403);
        $this->authorize('view', $review);

        $review = $this->reviewRepository->findById($review->id);

        return view('reviews.show', compact('review'));
    }

    public function edit(Review $review): View
    {
        abort_if(auth()->user()->isSubAdmin(), 403);
        $this->authorize('update', $review);

        $review = $this->reviewRepository->findById($review->id);
        $complianceStatuses = ComplianceStatus::cases();

        return view('reviews.edit', compact('review', 'complianceStatuses'));
    }

    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);
        $validated = $request->validated();

        $this->reviewService->updateReview(
            $review,
            ['summary' => $validated['summary'], 'notes' => $validated['notes']],
            $validated['findings']
        );

        UserActivityLog::log('updated', Review::class, $review->id, "Memperbarui review untuk dokumen #{$review->review_document_id}");

        return redirect()->route('reviews.show', $review)
            ->with('success', 'Review updated successfully.');
    }

    public function complete(Review $review): RedirectResponse
    {
        abort_if(request()->user()->isSubAdmin(), 403);
        $this->authorize('update', $review);

        $this->reviewService->completeReview($review);

        UserActivityLog::log('completed', Review::class, $review->id, "Menyelesaikan review dan menyetujui dokumen #{$review->review_document_id}");

        return redirect()->route('reviews.show', $review)
            ->with('success', 'Review completed and document approved.');
    }

    public function requestRevision(Request $request, Review $review): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);
        $this->authorize('update', $review);

        $request->validate(['notes' => 'required|string']);

        $this->reviewService->requestRevision($review, $request->input('notes'));

        UserActivityLog::log('revision_requested', Review::class, $review->id, "Meminta revisi untuk dokumen #{$review->review_document_id}");

        return redirect()->route('reviews.show', $review)
            ->with('success', 'Revision requested for document.');
    }

    public function reject(Request $request, Review $review): RedirectResponse
    {
        abort_if($request->user()->isSubAdmin(), 403);
        $this->authorize('update', $review);

        $request->validate(['notes' => 'required|string']);

        $this->reviewService->rejectReview($review, $request->input('notes'));

        UserActivityLog::log('rejected', Review::class, $review->id, "Menolak dokumen #{$review->review_document_id}");

        return redirect()->route('reviews.show', $review)
            ->with('success', 'Document rejected.');
    }
}
