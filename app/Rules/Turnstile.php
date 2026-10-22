<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Translation\PotentiallyTranslatedString;

class Turnstile implements ValidationRule
{
    /**
     * Run even when the token field is missing from the request.
     */
    public bool $implicit = true;

    /**
     * Verify the Cloudflare Turnstile token. Skipped when no secret key is configured.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secretKey = config('services.turnstile.secret_key');

        if (blank($secretKey)) {
            return;
        }

        if (! is_string($value) || $value === '') {
            $fail('Silakan selesaikan verifikasi captcha.');

            return;
        }

        try {
            $isVerified = Http::asForm()->timeout(10)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secretKey,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ])
                ->json('success') === true;
        } catch (\Throwable $e) {
            report($e);
            $isVerified = false;
        }

        if (! $isVerified) {
            $fail('Verifikasi captcha gagal. Silakan coba lagi.');
        }
    }
}
