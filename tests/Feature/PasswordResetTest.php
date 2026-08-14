<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'email' => 'user@example.com',
            'email_verified_at' => now(),
        ]);
    }

    public function test_guest_sees_forgot_password_form(): void
    {
        $this->get(route('password.request'))->assertOk()->assertSee('Lupa Password');
    }

    public function test_sends_reset_link_for_role_user(): void
    {
        Notification::fake();
        $user = $this->user();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_does_not_send_reset_link_for_non_user_role(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@example.com']);

        $this->post(route('password.email'), ['email' => $admin->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertNothingSent();
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $admin->email]);
    }

    public function test_does_not_leak_unregistered_email(): void
    {
        $this->post(route('password.email'), ['email' => 'nobody@example.com'])
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_guest_sees_reset_form(): void
    {
        $this->get(route('password.reset', ['token' => 'token123', 'email' => 'user@example.com']))
            ->assertOk()
            ->assertSee('Buat Password Baru');
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = $this->user();
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_reset_rejects_invalid_token(): void
    {
        $user = $this->user();

        $this->post(route('password.update'), [
            'token' => Str::random(64),
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertSessionHasErrors('email');

        $this->assertFalse(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_reset_rejects_short_password(): void
    {
        $user = $this->user();
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');
    }
}
