<?php

namespace App\Http\Controllers;

use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationRelatedReference;
use App\Models\Review;
use App\Models\ReviewDocument;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $documentsQuery = ReviewDocument::query();
        $reviewsQuery = Review::query();

        if (! $user->isAdmin() && ! $user->isSubAdmin() && ! $user->isReviewer()) {
            $documentsQuery->where('user_id', $user->id);
            $reviewsQuery->whereHas('reviewDocument', fn ($q) => $q->where('user_id', $user->id));
        }

        if ($user->isReviewer()) {
            $reviewsQuery->where('reviewer_id', $user->id);
        }

        $stats = [
            'total_documents' => $documentsQuery->count(),
            'pending_documents' => (clone $documentsQuery)->where('status', 'submitted')->count(),
            'approved_documents' => (clone $documentsQuery)->where('status', 'approved')->count(),
            'total_reviews' => $reviewsQuery->count(),
        ];

        $recentDocuments = $documentsQuery->with('user')->latest()->take(5)->get();

        $latestRegulations = Regulation::with(['type', 'category'])
            ->orderByDesc('tanggal_tetapkan')
            ->take(5)
            ->get();

        $regulationRelated = RegulationRelatedReference::with('regulation')
            ->join('regulations', 'regulations.id', '=', 'regulation_related_references.regulation_id')
            ->orderByDesc('regulations.tanggal_tetapkan')
            ->select('regulation_related_references.*')
            ->take(5)
            ->get();

        $regulationFilterOptions = [
            'categories' => RegulationCategory::orderBy('name')->get(),
            'years' => Regulation::distinct()->orderByDesc('year')->pluck('year'),
        ];

        return view('dashboard.index', compact('stats', 'recentDocuments', 'latestRegulations', 'regulationRelated', 'regulationFilterOptions'));
    }

    public function compliance(Request $request): View
    {
        $user = $request->user();

        $documentsQuery = ReviewDocument::query();
        $reviewsQuery = Review::query();

        if (! $user->isAdmin() && ! $user->isSubAdmin() && ! $user->isReviewer()) {
            $documentsQuery->where('user_id', $user->id);
            $reviewsQuery->whereHas('reviewDocument', fn ($q) => $q->where('user_id', $user->id));
        }

        if ($user->isReviewer()) {
            $reviewsQuery->where('reviewer_id', $user->id);
        }

        $stats = [
            'total_documents' => $documentsQuery->count(),
            'pending_documents' => (clone $documentsQuery)->where('status', 'submitted')->count(),
            'approved_documents' => (clone $documentsQuery)->where('status', 'approved')->count(),
            'total_reviews' => $reviewsQuery->count(),
        ];

        $recentDocuments = $documentsQuery->with('user')->latest()->take(5)->get();

        return view('dashboard.compliance', compact('stats', 'recentDocuments'));
    }

    public function landing(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $categoryId = trim((string) $request->query('category_id', ''));
        $year = trim((string) $request->query('year', ''));
        $hasFilters = $request->hasAny(['search', 'category_id', 'year']);
        $showAllRegulations = $request->boolean('all');

        $documentsQuery = ReviewDocument::query();
        $reviewsQuery = Review::query();

        $stats = [
            'total_documents' => $documentsQuery->count(),
            'pending_documents' => (clone $documentsQuery)->where('status', 'submitted')->count(),
            'approved_documents' => (clone $documentsQuery)->where('status', 'approved')->count(),
            'total_reviews' => $reviewsQuery->count(),
        ];

        $recentDocuments = $documentsQuery
            ->with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 'user');
            })
            ->latest()
            ->take(5)
            ->get();

        $regulationsQuery = Regulation::with(['type', 'category'])
            ->when($hasFilters, fn ($query) => $query->whereRaw('1 = 0'))
            ->orderByDesc('tanggal_tetapkan');

        $latestRegulations = $showAllRegulations && ! $hasFilters
            ? $regulationsQuery->paginate(15)->withQueryString()
            : $regulationsQuery->take(5)->get();

        $regulationRelated = RegulationRelatedReference::with('regulation')
            ->latest()
            ->take(5)
            ->get();

        $regulationFilterOptions = [
            'categories' => RegulationCategory::orderBy('name')->get(),
            'years' => Regulation::distinct()->orderByDesc('year')->pluck('year'),
        ];

        return view('index-dashboard', compact(
            'stats',
            'recentDocuments',
            'latestRegulations',
            'regulationRelated',
            'search',
            'categoryId',
            'year',
            'hasFilters',
            'showAllRegulations',
            'regulationFilterOptions',
        ));
    }
}
