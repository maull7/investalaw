<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['regulation_id', 'name', 'document_type', 'file_path', 'parsed_at', 'parse_status', 'parsed_text', 'parse_stats'])]
class RegulationDocument extends Model
{
    use HasFactory;

    /** @return BelongsTo<Regulation, RegulationDocument> */
    public function regulation(): BelongsTo
    {
        return $this->belongsTo(Regulation::class);
    }

    public function isParsed(): bool
    {
        return $this->parsed_at !== null;
    }

    public function parseStatusLabel(): string
    {
        return match ($this->parse_status) {
            'complete' => 'Complete',
            'incomplete' => 'InComplete',
            default => 'Not Parsed',
        };
    }

    public function parseStatusBadgeColor(): string
    {
        return match ($this->parse_status) {
            'complete' => 'emerald',
            'incomplete' => 'amber',
            default => 'gray',
        };
    }

    protected function casts(): array
    {
        return [
            'parsed_at' => 'datetime',
            'parse_stats' => 'array',
        ];
    }
}
