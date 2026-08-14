<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrialSettingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function user(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'institution' => 'PT Contoh',
            'position' => 'Compliance',
            'province' => config('provinces')[0],
            'phone' => '081234567890',
        ]);
    }

    private function trialPackage(): Package
    {
        return Package::create([
            'name' => 'Trial',
            'price' => '0',
            'price_period' => '1 bulan',
            'is_active' => true,
        ]);
    }

    public function test_non_admin_cannot_access_settings(): void
    {
        $this->actingAs($this->user())->get(route('settings.index'))->assertForbidden();
    }

    public function test_admin_can_update_trial_settings(): void
    {
        $this->actingAs($this->admin())->post(route('settings.update'), [
            'trial_requires_confirmation' => '1',
            'trial_max_hours' => '24',
        ])->assertRedirect(route('settings.index'));

        $this->assertSame('1', Setting::get('trial_requires_confirmation'));
        $this->assertSame('24', Setting::get('trial_max_hours'));
    }

    public function test_trial_auto_activates_when_confirmation_disabled(): void
    {
        Setting::where('key', 'trial_requires_confirmation')->update(['value' => '0']);
        $user = $this->user();
        $package = $this->trialPackage();

        $this->actingAs($user)->post(route('profile.update'), [
            'institution' => $user->institution,
            'position' => $user->position,
            'province' => $user->province,
            'phone' => $user->phone,
            'package_id' => $package->id,
        ])->assertRedirect(route('dashboard'));

        $this->assertSame('active', $user->userPackages()->first()->status);
    }

    public function test_trial_pends_when_confirmation_enabled(): void
    {
        Setting::where('key', 'trial_requires_confirmation')->update(['value' => '1']);
        $user = $this->user();
        $package = $this->trialPackage();

        $this->actingAs($user)->post(route('profile.update'), [
            'institution' => $user->institution,
            'position' => $user->position,
            'province' => $user->province,
            'phone' => $user->phone,
            'package_id' => $package->id,
        ])->assertRedirect(route('dashboard'));

        $this->assertSame('pending', $user->userPackages()->first()->status);
    }
}
