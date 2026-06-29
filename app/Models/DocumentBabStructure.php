<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentBabStructure extends Model
{
    protected $fillable = [
        'review_document_id',
        'parent_id',
        'name',
        'start_page',
        'end_page',
        'toc_page',
        'pdf_page',
        'pdf_end_page',
        'sort_order',
        'level',
    ];

    public function reviewDocument(): BelongsTo
    {
        return $this->belongsTo(ReviewDocument::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->ordered();
    }

    public function scopeBabs($query)
    {
        return $query->where('level', 0)->ordered();
    }

    public function scopeSubbabs($query)
    {
        return $query->where('level', 1)->ordered();
    }

    public function scopeContents($query)
    {
        return $query->where('level', 2)->ordered();
    }
}
