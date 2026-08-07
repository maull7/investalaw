<?php

namespace App\Repositories;

use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RegulationRepository
{
    public function paginateWithFilters(array $filters): LengthAwarePaginator
    {
        $sortField = $filters['sort'] ?? 'year';
        $sortDirection = $filters['direction'] ?? 'desc';

        $allowedSorts = ['regulation_number', 'title', 'year', 'regulation_type_id', 'category_id'];

        if (! in_array($sortField, $allowedSorts)) {
            $sortField = 'year';
        }

        if (! in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $query = Regulation::with(['type', 'category', 'subCategories', 'documents'])
            ->withCount('documents');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('regulation_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['search_content'])) {
            $searchContent = $filters['search_content'];

            $query->where(function (Builder $q) use ($searchContent) {
                $q->where('parsed_text', 'like', "%{$searchContent}%")
                    ->orWhereHas('documents', function (Builder $docQuery) use ($searchContent) {
                        $docQuery->where('parsed_text', 'like', "%{$searchContent}%");
                    });
            })
                ->selectRaw('regulations.*, 
                CASE 
                    WHEN parsed_text LIKE ? THEN 1 
                    ELSE 0 
                END as relevance', ["%{$searchContent}%"])
                ->limit(1);
        }

        if (! empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        if (! empty($filters['type_id'])) {
            $query->where('regulation_type_id', $filters['type_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['search_content'])) {
            $query->orderByDesc('relevance');
        } elseif ($sortField === 'regulation_type_id') {
            $query->orderBy(
                RegulationType::select('level')
                    ->whereColumn('regulation_types.id', 'regulations.regulation_type_id'),
                $sortDirection
            );
        } elseif ($sortField === 'category_id') {
            $query->orderBy(
                RegulationCategory::select('name')
                    ->whereColumn('regulation_categories.id', 'regulations.category_id'),
                $sortDirection
            );
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        $query->orderByDesc('id');

        return $query->paginate(15)->withQueryString();
    }

    public function findByIdWithRelations(int $id): Regulation
    {
        return Regulation::with([
            'type',
            'category',
            'subCategories',
            'relatedRegulations.type',
            'documents',
            'relatedReferences',
        ])->findOrFail($id);
    }

    public function search(string $query, ?int $excludeId = null): Collection
    {
        return Regulation::where(function (Builder $q) use ($query) {
            $q->where('regulation_number', 'like', "%{$query}%")
                ->orWhere('title', 'like', "%{$query}%")
                ->orWhere('year', 'like', "%{$query}%");
        })
            ->when($excludeId, fn (Builder $q) => $q->where('id', '!=', $excludeId))
            ->with('type')
            ->orderByDesc('year')
            ->limit(20)
            ->get();
    }

    public function getFilterOptions(): array
    {
        return [
            'types' => RegulationType::orderBy('level')->get(),
            'categories' => RegulationCategory::orderBy('name')->get(),
            'years' => Regulation::select('year')
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year'),
        ];
    }

    public function getFormOptions(): array
    {
        return [
            'types' => RegulationType::where('is_active', true)->orderBy('level')->get(),
            'categories' => RegulationCategory::with(['subCategories' => fn ($q) => $q->where('is_active', true)])->orderBy('name')->get(),
        ];
    }

    public function buildSnippet(?string $text, string $keyword, int $length = 200): ?string
    {
        if (! $text || ! $keyword) {
            return null;
        }

        $pos = mb_stripos($text, $keyword);
        if ($pos === false) {
            return mb_substr($text, 0, $length).'...';
        }

        $start = max(0, $pos - 100);
        $snippet = mb_substr($text, $start, $length);

        return ($start > 0 ? '...' : '').$snippet.'...';
    }
}
