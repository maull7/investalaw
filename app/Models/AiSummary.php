<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSummary extends Model
{
    protected $fillable = [
        'review_document_id',
        'type',
        'prompt_text',
        'summary',
        'raw_response',
        'provider_used',
        'model_used',
    ];

    public function reviewDocument(): BelongsTo
    {
        return $this->belongsTo(ReviewDocument::class);
    }
}
