<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'description'])]
class RegulationCategory extends Model
{
    use HasFactory, SoftDeletes;

    /** @return HasMany<CategoryFile> */
    public function files(): HasMany
    {
        return $this->hasMany(CategoryFile::class, 'category_id');
    }

    /** @return HasMany<SubCategory> */
    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class, 'category_id');
    }

    /** @return HasMany<Regulation> */
    public function regulations(): HasMany
    {
        return $this->hasMany(Regulation::class, 'category_id');
    }
}
