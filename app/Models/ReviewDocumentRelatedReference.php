<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewDocumentRelatedReference extends Model
{
    protected $fillable = [
        'review_document_id',
        'name',
        'number',
        'year',
        'relationship',
    ];

    /** @return BelongsTo<ReviewDocument, ReviewDocumentRelatedReference> */
    public function reviewDocument(): BelongsTo
    {
        return $this->belongsTo(ReviewDocument::class);
    }
}
