<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->cuenta_bloqueada && $user->rol !== 'admin') {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta está bloqueada. Contacta al administrador.',
            ]);
        }

        return $next($request);
    }
}
