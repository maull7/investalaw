<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationChatMessage extends Model
{
    protected $fillable = ['consultation_session_id', 'user_id', 'role', 'content', 'attachments'];

    protected $casts = [
        'attachments' => 'array',
    ];

    /** @return BelongsTo<ConsultationSession, ConsultationChatMessage> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ConsultationSession::class);
    }

    /** @return BelongsTo<User, ConsultationChatMessage> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
