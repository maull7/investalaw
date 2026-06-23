<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DocumentPartition extends Model
{
    protected $fillable = [
        'review_document_id',
        'parent_id',
        'name',
        'start_page',
        'end_page',
        'description',
        'has_toc',
        'sort_order',
        'level',
    ];

    /** @return BelongsTo<ReviewDocument> */
    public function reviewDocument(): BelongsTo
    {
        return $this->belongsTo(ReviewDocument::class);
    }

    /** @return BelongsTo<DocumentPartition> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<DocumentPartition> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered();
    }

    /** @return HasMany<DocumentPartition> */
    public function allChildren(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return HasOne<PartitionAnalysis> */
    public function analysis(): HasOne
    {
        return $this->hasOne(PartitionAnalysis::class, 'document_partition_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->ordered();
    }

    public function scopeBabs($query)
    {
        return $query->where('level', 1)->ordered();
    }

    public function scopeSubbabs($query)
    {
        return $query->where('level', 2)->ordered();
    }

    public function scopeContents($query)
    {
        return $query->where('level', 3)->ordered();
    }

    public function isPartition(): bool
    {
        return $this->parent_id === null;
    }

    public function isBab(): bool
    {
        return $this->level === 1;
    }

    public function isSubbab(): bool
    {
        return $this->level === 2;
    }

    public function isContent(): bool
    {
        return $this->level === 3;
    }

    public function hasToc(): bool
    {
        return $this->has_toc;
    }
}
