<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegulationAiResult extends Model
{
    protected $fillable = [
        'regulation_id',
        'type_prompt_id',
        'type',
        'prompt_title',
        'prompt_text',
        'result',
        'provider_used',
        'model_used',
    ];

    /** @return BelongsTo<Regulation, RegulationAiResult> */
    public function regulation(): BelongsTo
    {
        return $this->belongsTo(Regulation::class);
    }

    /** @return BelongsTo<TypePrompt, RegulationAiResult> */
    public function typePrompt(): BelongsTo
    {
        return $this->belongsTo(TypePrompt::class);
    }
}
