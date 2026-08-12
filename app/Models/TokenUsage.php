<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenUsage extends Model
{
    protected $fillable = ['user_id', 'date', 'tokens_used', 'source', 'source_id'];

    /** @return BelongsTo<User, TokenUsage> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function todayUsage(int $userId): int
    {
        return (int) static::where('user_id', $userId)
            ->where('date', today()->toDateString())
            ->sum('tokens_used');
    }
}
