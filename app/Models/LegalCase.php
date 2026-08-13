<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LegalCase extends Model
{
    protected $fillable = [
        'user_id', 'title', 'case_number', 'court', 'status_case',
        'file_path', 'parsed_text', 'parsed_at', 'analysis', 'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'status_case' => 'string',
            'parsed_at' => 'datetime',
            'analyzed_at' => 'datetime',
            'analysis' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function regulations(): BelongsToMany
    {
        return $this->belongsToMany(Regulation::class, 'case_regulation')
            ->withPivot('explanation')
            ->withTimestamps();
    }

    public function isParsed(): bool
    {
        return $this->parsed_at !== null;
    }

    public function isAnalyzed(): bool
    {
        return $this->analyzed_at !== null;
    }

    public function aiStatus(string $action): ?AiJobStatus
    {
        return AiJobStatus::where('model_type', $this->getMorphClass())
            ->where('model_id', $this->getKey())
            ->where('action', $action)
            ->first();
    }

    public function isAiProcessing(string $action): bool
    {
        return ($this->aiStatus($action)?->status ?? null) === 'processing';
    }
}
