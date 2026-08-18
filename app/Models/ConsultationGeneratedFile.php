<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationGeneratedFile extends Model
{
    protected $fillable = [
        'consultation_session_id',
        'user_id',
        'chat_message_id',
        'type',
        'format',
        'filename',
        'path',
        'original_prompt',
        'file_size',
        'ai_response',
    ];

    public const TYPE_DOCUMENT = 'document';

    public const TYPE_IMAGE = 'image';

    public const FORMAT_PDF = 'pdf';

    public const FORMAT_DOCX = 'docx';

    public const FORMAT_PNG = 'png';

    public const FORMAT_JPG = 'jpg';

    public const FORMAT_XLSX = 'xlsx';

    /** @return BelongsTo<ConsultationSession, ConsultationGeneratedFile> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ConsultationSession::class, 'consultation_session_id');
    }

    /** @return BelongsTo<User, ConsultationGeneratedFile> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<ConsultationChatMessage, ConsultationGeneratedFile> */
    public function chatMessage(): BelongsTo
    {
        return $this->belongsTo(ConsultationChatMessage::class, 'chat_message_id');
    }

    public function getUrlAttribute(): string
    {
        return route('consultations.generated.download', $this);
    }
}
