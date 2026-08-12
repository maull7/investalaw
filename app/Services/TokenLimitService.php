<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\TokenUsage;

class TokenLimitService
{
    public function dailyLimit(): int
    {
        return (int) Setting::get('daily_token_limit', 100000);
    }

    public function todayUsage(int $userId): int
    {
        return TokenUsage::todayUsage($userId);
    }

    public function remaining(int $userId): int
    {
        return max(0, $this->dailyLimit() - $this->todayUsage($userId));
    }

    public function canSend(int $userId): bool
    {
        return $this->remaining($userId) > 0;
    }

    public function record(int $userId, int $tokensUsed, string $source, ?int $sourceId = null): void
    {
        TokenUsage::create([
            'user_id' => $userId,
            'date' => today()->toDateString(),
            'tokens_used' => $tokensUsed,
            'source' => $source,
            'source_id' => $sourceId,
        ]);
    }
}
