<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_profile_is_blocked_from_dashboard(): void
    {
        $user = User::factory()->create([
            'institution' => null,
            'position' => null,
            'province' => null,
            'phone' => null,
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertRedirect(route('profile.edit'));

        $this->actingAs($user)->get(route('review-documents.index'))
            ->assertRedirect(route('profile.edit'));
    }

    public function test_user_can_complete_profile(): void
    {
        $user = User::factory()->create([
            'institution' => null,
            'position' => null,
            'province' => null,
            'phone' => null,
        ]);

        $this->actingAs($user)->post(route('profile.update'), [
            'institution' => 'PT Contoh Investasi',
            'position' => 'Compliance Officer',
            'province' => config('provinces')[0],
            'phone' => '081234567890',
        ])->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->actingAs($user->fresh())->get(route('dashboard'))->assertOk();
    }

    public function test_profile_requires_all_fields(): void
    {
        $user = User::factory()->create([
            'institution' => null,
            'position' => null,
            'province' => null,
            'phone' => null,
        ]);

        $this->actingAs($user)->post(route('profile.update'), [])
            ->assertSessionHasErrors(['institution', 'position', 'province', 'phone']);
    }

    public function test_profile_rejects_invalid_province_and_phone(): void
    {
        $user = User::factory()->create([
            'institution' => null,
            'position' => null,
            'province' => null,
            'phone' => null,
        ]);

        $this->actingAs($user)->post(route('profile.update'), [
            'institution' => 'PT Contoh',
            'position' => 'Compliance',
            'province' => 'Bukan Provinsi',
            'phone' => 'abc',
        ])->assertSessionHasErrors(['province', 'phone']);
    }

    public function test_non_user_roles_are_not_blocked_without_profile(): void
    {
        foreach (['admin', 'sub_admin', 'reviewer'] as $role) {
            $staff = User::factory()->create([
                'role' => $role,
                'institution' => null,
                'position' => null,
                'province' => null,
                'phone' => null,
            ]);

            $this->actingAs($staff)->get(route('dashboard'))->assertOk();
        }
    }
}
