<?php

// Middleware para verificar que la cuenta no esté bloqueada y hacer cumplir el cambio obligatorio de contraseña.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->cuenta_bloqueada && !$user->isSuperAdmin()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta está bloqueada. Contacta al administrador.',
            ]);
        }

        if ($user->debe_cambiar_password && !$request->routeIs('password.cambiar', 'password.cambiar.store', 'logout')) {
            return redirect()->route('password.cambiar');
        }

        return $next($request);
    }
}
