<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route('dashboard')
            : view('auth.verify-email');
    }

    public function verify(Request $request): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        $user = User::findOrFail($request->route('id'));

        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $user->markEmailAsVerified();

        Auth::login($user);

        return redirect()->route('profile.edit')
            ->with('success', 'Akun berhasil diaktivasi. Silakan lengkapi data pribadi Anda.');
    }

    public function send(Request $request): RedirectResponse
    {
        $user = $request->user() ?? User::where('email', $request->input('email'))->first();

        if (! $user || $user->hasVerifiedEmail()) {
            return back()->with('status', 'Jika email terdaftar, link aktivasi telah dikirim.');
        }

        $user->sendEmailVerificationNotification();

        return back()
            ->with('status', 'Link aktivasi baru telah dikirim ke email Anda. Cek inbox atau folder spam.')
            ->with('unverified', true)
            ->with('unverified_email', $user->email);
    }
}
