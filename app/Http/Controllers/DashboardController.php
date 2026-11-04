<?php

namespace App\Http\Controllers;

use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationRelatedReference;
use App\Models\RegulationType;
use App\Models\Review;
use App\Models\ReviewDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Columns needed by the regulation listings; skips the huge parsed_text/parse_stats columns.
     */
    private const LISTING_COLUMNS = ['id', 'regulation_number', 'title', 'regulation_type_id', 'category_id', 'year', 'tanggal_tetapkan'];

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

        if (! $user->isUser()) {
            $recentDocuments = $documentsQuery->with('user')->latest()->take(5)->get();
        } else {
            $recentDocuments = $documentsQuery->with('user')->where('user_id', $user->id)->latest()->take(5)->get();
        }

        $latestRegulations = Regulation::query()
            ->select(self::LISTING_COLUMNS)
            ->forceIndex('regulations_public_listing_idx')
            ->with(['type', 'category'])
            ->whereHas('category', function ($query) {
                $query->where('sector_id', 1);
            })
            ->orderByDesc('tanggal_tetapkan')
            ->take(5)
            ->get();

        $relatedRegulation = $this->findRelatedRegulation($request);
        $regulationRelated = RegulationRelatedReference::with('regulation:id,regulation_number,title,year')
            ->join('regulations', 'regulations.id', '=', 'regulation_related_references.regulation_id')
            ->when($relatedRegulation, fn ($query) => $query->where('regulation_related_references.regulation_id', $relatedRegulation->id))
            ->when(! $relatedRegulation, fn ($query) => $query->take(5))
            ->orderByDesc('regulations.tanggal_tetapkan')
            ->select('regulation_related_references.*')
            ->get();

        $regulationFilterOptions = [
            'types' => RegulationType::orderBy('level')->get(),
            'categories' => RegulationCategory::orderBy('name')->get(),
            'years' => $this->regulationYears(),
        ];

        return view('dashboard.index', compact('stats', 'recentDocuments', 'latestRegulations', 'regulationRelated', 'relatedRegulation', 'regulationFilterOptions'));
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
        abort_if($request->integer('page', 1) > 100, 404);

        $search = trim((string) $request->query('search', ''));
        $categoryId = trim((string) $request->query('category_id', ''));
        $year = trim((string) $request->query('year', ''));
        $hasFilters = $search !== '' || $categoryId !== '' || $year !== '';
        $showAllRegulations = $request->boolean('all');

        $stats = Cache::remember('landing.stats', now()->addMinutes(10), fn (): array => [
            'total_documents' => ReviewDocument::count(),
            'pending_documents' => ReviewDocument::where('status', 'submitted')->count(),
            'approved_documents' => ReviewDocument::where('status', 'approved')->count(),
            'total_reviews' => Review::count(),
        ]);

        // $recentDocuments = $documentsQuery
        //     ->with('user')
        //     ->whereHas('user', function ($query) {
        //         $query->where('role', 'user');
        //     })
        //     ->latest()
        //     ->take(5)
        //     ->get();

        $regulationsQuery = Regulation::query()
            ->select(self::LISTING_COLUMNS)
            ->forceIndex('regulations_public_listing_idx')
            ->with(['type', 'category'])
            ->whereHas('category', function ($query) {
                $query->where('sector_id', 1);
            })
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('regulation_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%");
                });
            })
            ->when($categoryId !== '', fn (Builder $query) => $query->where('category_id', $categoryId))
            ->when($year !== '', fn (Builder $query) => $query->where('year', $year))
            ->orderByDesc('tanggal_tetapkan');

        $latestRegulations = $showAllRegulations || $hasFilters
            ? $regulationsQuery->paginate(15)->withQueryString()
            : $regulationsQuery->take(5)->get();

        $relatedRegulation = $this->findRelatedRegulation($request);
        $regulationRelated = RegulationRelatedReference::with('regulation:id,regulation_number,title,year')
            ->when($relatedRegulation, fn ($query) => $query->where('regulation_id', $relatedRegulation->id))
            ->when(! $relatedRegulation, fn ($query) => $query->take(5))
            ->latest()
            ->get();

        $regulationFilterOptions = [
            'categories' => RegulationCategory::orderBy('name')->get(),
            'years' => $this->regulationYears(),
        ];

        return view('index-dashboard', compact(
            'stats',
            'latestRegulations',
            'regulationRelated',
            'relatedRegulation',
            'search',
            'categoryId',
            'year',
            'hasFilters',
            'showAllRegulations',
            'regulationFilterOptions',
        ));
    }

    /**
     * @return Collection<int, int>
     */
    private function regulationYears(): Collection
    {
        return collect(Cache::remember('regulations.years.v2', now()->addHour(), fn (): array => Regulation::distinct()->orderByDesc('year')->pluck('year')->all()));
    }

    /**
     * Regulation selected via the "Terkait" button on the Peraturan Terkini table.
     */
    private function findRelatedRegulation(Request $request): ?Regulation
    {
        $regulationId = $request->integer('related');

        return $regulationId > 0 ? Regulation::select(self::LISTING_COLUMNS)->find($regulationId) : null;
    }
}
