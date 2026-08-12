<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified extends \Illuminate\Auth\Middleware\EnsureEmailIsVerified
{
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if ($request->user() && $request->user()->role !== 'user') {
            return $next($request);
        }

        return parent::handle($request, $next, $redirectToRoute);
    }
}