<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\TokenUsage;
use App\Models\User;
use App\Models\UserPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KakVestaQuotaTest extends TestCase
{
    use RefreshDatabase;

    private function activeUserWithQuotaPackage(int $quota): User
    {
        $package = Package::create([
            'name' => 'Bisnis',
            'price' => '12jt',
            'price_period' => '/bulan',
            'benefits' => ['Kuota'],
            'kak_vesta_tokens' => $quota,
        ]);

        $user = User::factory()->create(['role' => 'user']);
        UserPackage::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'type' => 'paid',
            'status' => 'active',
            'confirmed_at' => now(),
        ]);

        return $user;
    }

    private function addConsultationTokens(int $userId, int $tokens): void
    {
        TokenUsage::create([
            'user_id' => $userId,
            'date' => today()->toDateString(),
            'tokens_used' => $tokens,
            'source' => 'consultation_chat',
        ]);
    }

    public function test_kak_vesta_blocked_when_token_quota_exhausted(): void
    {
        $user = $this->activeUserWithQuotaPackage(1000);
        $this->addConsultationTokens($user->id, 1500);

        $this->actingAs($user)
            ->get(route('consultations.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }

    public function test_kak_vesta_allowed_when_quota_remaining(): void
    {
        $user = $this->activeUserWithQuotaPackage(1000);
        $this->addConsultationTokens($user->id, 500);

        $this->actingAs($user)
            ->get(route('consultations.index'))
            ->assertOk();
    }

    public function test_kak_vesta_unlimited_when_quota_empty(): void
    {
        $user = $this->activeUserWithQuotaPackage(0);
        $this->addConsultationTokens($user->id, 999999);

        $this->actingAs($user)
            ->get(route('consultations.index'))
            ->assertOk();
    }
}
