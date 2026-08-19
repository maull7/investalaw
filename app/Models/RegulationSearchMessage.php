<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegulationSearchMessage extends Model
{
    protected $fillable = ['regulation_search_session_id', 'role', 'content', 'regulation_ids'];

    protected function casts(): array
    {
        return [
            'regulation_ids' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(RegulationSearchSession::class, 'regulation_search_session_id');
    }
}
