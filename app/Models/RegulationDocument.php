<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['regulation_id', 'name', 'document_type', 'file_path', 'parsed_at', 'parse_status', 'parsed_text', 'parse_stats', 'parse_progress', 'parse_error'])]
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

    public function effectiveParseStatus(): string
    {
        if ($this->shouldTreatUnreadableOcrAsComplete()) {
            return 'complete';
        }

        return $this->parse_status ?? 'not_parsed';
    }

    public function effectiveParseError(): ?string
    {
        if ($this->shouldTreatUnreadableOcrAsComplete()) {
            return null;
        }

        return $this->parse_error;
    }

    public function parseStatusLabel(): string
    {
        return match ($this->effectiveParseStatus()) {
            'complete' => 'Complete',
            'incomplete' => 'InComplete',
            'parsing' => 'Parsing',
            'failed' => 'Failed',
            default => 'Not Parsed',
        };
    }

    public function parseStatusBadgeColor(): string
    {
        return match ($this->effectiveParseStatus()) {
            'complete' => 'emerald',
            'incomplete' => 'amber',
            'parsing' => 'blue',
            'failed' => 'rose',
            default => 'gray',
        };
    }

    protected function casts(): array
    {
        return [
            'parsed_at' => 'datetime',
            'parse_stats' => 'array',
            'parse_progress' => 'integer',
        ];
    }

    private function shouldTreatUnreadableOcrAsComplete(): bool
    {
        return $this->parsed_at !== null
            && $this->parse_status === 'failed'
            && str_starts_with((string) $this->parse_error, 'OCR hanya berhasil membaca');
    }
}
