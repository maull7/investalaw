<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasCompletedProfile() && ! $request->routeIs('profile.edit', 'profile.update', 'logout')) {
            return redirect()->route('profile.edit')
                ->with('info', 'Lengkapi data pribadi Anda terlebih dahulu.');
        }

        return $next($request);
    }
}
