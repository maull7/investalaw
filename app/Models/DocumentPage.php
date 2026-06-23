<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentPage extends Model
{
    protected $fillable = [
        'review_document_id',
        'page_number',
        'content',
        'char_count',
    ];

    public function reviewDocument(): BelongsTo
    {
        return $this->belongsTo(ReviewDocument::class);
    }
}
