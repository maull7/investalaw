<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'regulation_id', 'role', 'content'])]
class RegulationChatMessage extends Model
{
    /** @return BelongsTo<User, RegulationChatMessage> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Regulation, RegulationChatMessage> */
    public function regulation(): BelongsTo
    {
        return $this->belongsTo(Regulation::class);
    }
}
