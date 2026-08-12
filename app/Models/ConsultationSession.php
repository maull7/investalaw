<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsultationSession extends Model
{
    protected $fillable = ['user_id', 'title'];

    /** @return BelongsTo<User, ConsultationSession> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<ConsultationChatMessage> */
    public function messages(): HasMany
    {
        return $this->hasMany(ConsultationChatMessage::class);
    }

    /** @return BelongsToMany<Regulation> */
    public function regulations(): BelongsToMany
    {
        return $this->belongsToMany(Regulation::class);
    }
}