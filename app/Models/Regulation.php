<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['regulation_number', 'title', 'regulation_type_id', 'category_id', 'year', 'effective_date', 'file_path', 'parsed_at', 'parse_status', 'parsed_text', 'parse_stats'])]
class Regulation extends Model
{
    use HasFactory, SoftDeletes;

    /** @return BelongsTo<RegulationType, Regulation> */
    public function type(): BelongsTo
    {
        return $this->belongsTo(RegulationType::class, 'regulation_type_id');
    }

    /** @return BelongsTo<RegulationCategory, Regulation> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(RegulationCategory::class, 'category_id');
    }

    /** @return BelongsToMany<SubCategory> */
    public function subCategories(): BelongsToMany
    {
        return $this->belongsToMany(SubCategory::class, 'regulation_sub_category');
    }

    /** @return BelongsToMany<Regulation> */
    public function relatedRegulations(): BelongsToMany
    {
        return $this->belongsToMany(
            Regulation::class,
            'regulation_related',
            'regulation_id',
            'related_regulation_id',
        );
    }

    /** @return HasMany<RegulationDocument> */
    public function documents(): HasMany
    {
        return $this->hasMany(RegulationDocument::class);
    }

    public function isParsed(): bool
    {
        return $this->parsed_at !== null;
    }

    public function documentsParseProgress(): array
    {
        $total = $this->documents->count();
        $parsed = $this->documents->filter(fn ($d) => $d->isParsed())->count();

        return [
            'total' => $total,
            'parsed' => $parsed,
            'pending' => $total - $parsed,
            'percentage' => $total > 0 ? round(($parsed / $total) * 100) : 0,
        ];
    }

    public function parseStatusLabel(): string
    {
        return match ($this->parse_status) {
            'complete' => 'Complete',
            'incomplete' => 'InComplete',
            default => 'Not Parsed',
        };
    }

    public function parseStatusBadgeColor(): string
    {
        return match ($this->parse_status) {
            'complete' => 'emerald',
            'incomplete' => 'amber',
            default => 'gray',
        };
    }

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'effective_date' => 'date',
            'parsed_at' => 'datetime',
            'parse_stats' => 'array',
        ];
    }
}
