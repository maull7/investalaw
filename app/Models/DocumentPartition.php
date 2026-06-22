<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DocumentPartition extends Model
{
    protected $fillable = [
        'review_document_id',
        'name',
        'start_page',
        'end_page',
        'description',
        'sort_order',
    ];

    /** @return BelongsTo<ReviewDocument> */
    public function reviewDocument(): BelongsTo
    {
        return $this->belongsTo(ReviewDocument::class);
    }

    /** @return HasOne<PartitionAnalysis> */
    public function analysis(): HasOne
    {
        return $this->hasOne(PartitionAnalysis::class, 'document_partition_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
