<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = $request->user();
            if (! $user->is_active) {
                Auth::logout();

                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors(['email' => 'Akun Anda sedang nonaktif. Silakan hubungi admin.']);
            }
            if ($user->role == 'user') {
                if (! $user->hasVerifiedEmail() && $user->role === 'user') {
                    Auth::logout();

                    return back()
                        ->withInput($request->only('email', 'remember'))
                        ->withErrors(['email' => 'Akun belum diaktivasi. Silakan cek email Anda dan klik link verifikasi yang telah dikirim.'])
                        ->with('unverified', true)
                        ->with('unverified_email', $user->email);
                }
            }

            $user->update(['last_login_at' => now()]);

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => __('auth.failed')]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        // ponytail: sessio only counts on explicit logout; browser-close is lost
        if (($user = $request->user()) && $user->last_login_at) {
            $user->increment('total_active_minutes', $user->last_login_at->diffInMinutes(now()));
            $user->update(['last_login_at' => null]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
