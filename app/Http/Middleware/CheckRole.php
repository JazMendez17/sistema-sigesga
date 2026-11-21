<?php

// Middleware para verificar que el usuario tenga el rol requerido.
// El rol `superadmin` tiene acceso completo a todos los módulos administrativos y operativos.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->rol === 'superadmin') {
            // Superadmin administra todo (solo excluido de vistas estrictas de cliente sin otros roles)
            if ($roles !== ['cliente']) {
                return $next($request);
            }
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permisos para acceder a esta sección.');
    }
}
