<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['category_id', 'name', 'is_active'])]
class SubCategory extends Model
{
    use HasFactory, SoftDeletes;

    /** @return BelongsTo<RegulationCategory, SubCategory> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(RegulationCategory::class, 'category_id');
    }

    /** @return BelongsToMany<Regulation> */
    public function regulations(): BelongsToMany
    {
        return $this->belongsToMany(Regulation::class, 'regulation_sub_category');
    }

    /** @param \Illuminate\Database\Eloquent\Builder<SubCategory> $query */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
