<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['regulation_number', 'title', 'regulation_type_id', 'category_id', 'year', 'file_path'])]
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

    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }
}
