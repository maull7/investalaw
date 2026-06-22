<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentParsedText extends Model
{
    protected $fillable = [
        'review_document_id',
        'source_type',
        'source_id',
        'page',
        'parsed_text',
        'char_count',
    ];

    /** @return BelongsTo<ReviewDocument> */
    public function reviewDocument(): BelongsTo
    {
        return $this->belongsTo(ReviewDocument::class);
    }

    /** @param Builder<DocumentParsedText> $query */
    public function scopeForRegulation(Builder $query, int $reviewDocumentId, int $regulationId): Builder
    {
        return $query->where('review_document_id', $reviewDocumentId)
            ->where('source_type', 'regulation')
            ->where('source_id', $regulationId);
    }

    /** @param Builder<DocumentParsedText> $query */
    public function scopeForDocument(Builder $query, int $reviewDocumentId): Builder
    {
        return $query->where('review_document_id', $reviewDocumentId)
            ->where('source_type', 'document');
    }
}
