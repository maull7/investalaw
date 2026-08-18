<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentBabAnalysis extends Model
{
    protected $fillable = ['review_document_id', 'bab_index', 'label', 'result'];

    protected function casts(): array
    {
        return [
            'result' => 'array',
        ];
    }

    public function reviewDocument(): BelongsTo
    {
        return $this->belongsTo(ReviewDocument::class);
    }
}
