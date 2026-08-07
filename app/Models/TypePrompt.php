<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypePrompt extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<AiPrompt, TypePrompt> */
    public function prompts(): HasMany
    {
        return $this->hasMany(AiPrompt::class, 'type_prompt_id');
    }
}
