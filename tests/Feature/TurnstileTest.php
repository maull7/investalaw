<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurnstileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.turnstile.site_key' => 'site-key',
            'services.turnstile.secret_key' => 'secret-key',
        ]);
    }

    private function fakeSiteverify(bool $isSuccess): void
    {
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => $isSuccess]),
        ]);
    }

    public function test_login_and_register_pages_render_widget(): void
    {
        $this->get(route('login'))->assertSee('data-sitekey="site-key"', false);
        $this->get(route('register'))->assertSee('data-sitekey="site-key"', false);
    }

    public function test_login_without_token_fails(): void
    {
        Http::fake();
        $user = User::factory()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertGuest();
        Http::assertNothingSent();
    }

    public function test_login_with_invalid_token_fails(): void
    {
        $this->fakeSiteverify(false);
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'bad-token',
        ])->assertSessionHasErrors('cf-turnstile-response');

        $this->assertGuest();
    }

    public function test_login_with_valid_token_succeeds(): void
    {
        $this->fakeSiteverify(true);
        $user = User::factory()->create(['role' => 'admin']);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
            'cf-turnstile-response' => 'good-token',
        ])->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);
        Http::assertSent(fn ($request) => $request['secret'] === 'secret-key' && $request['response'] === 'good-token');
    }

    public function test_register_with_invalid_token_fails(): void
    {
        $this->fakeSiteverify(false);

        $this->post(route('register'), [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'cf-turnstile-response' => 'bad-token',
        ])->assertSessionHasErrors('cf-turnstile-response');

        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
    }

    public function test_register_with_valid_token_succeeds(): void
    {
        $this->fakeSiteverify(true);

        $this->post(route('register'), [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'cf-turnstile-response' => 'good-token',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
    }

    public function test_verification_is_skipped_without_secret_key(): void
    {
        config(['services.turnstile.secret_key' => null]);
        Http::fake();
        $user = User::factory()->create(['role' => 'admin']);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
        Http::assertNothingSent();
    }
}
