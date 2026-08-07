<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiPrompt extends Model
{
    protected $fillable = [
        'type_prompt_id',
        'type',
        'title',
        'prompt_text',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** @return BelongsTo<TypePrompt, AiPrompt> */
    public function typePrompt(): BelongsTo
    {
        return $this->belongsTo(TypePrompt::class);
    }
}
