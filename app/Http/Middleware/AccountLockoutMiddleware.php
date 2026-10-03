<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountLockoutMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->isAccountLocked()) {
            return redirect()->route('dashboard')
                ->with('error', 'Your account is temporarily locked. Please try again later.');
        }

        return $next($request);
    }
}
