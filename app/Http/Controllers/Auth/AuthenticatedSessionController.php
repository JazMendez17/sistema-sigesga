<?php

// Controlador de gestión de sesiones, desbloqueo seguro y cambio obligatorio de contraseña

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (Auth::user()->debe_cambiar_password) {
            return redirect()->route('password.cambiar');
        }

        return redirect()->intended(route('panel.dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function unlock(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|max:254',
            'codigo' => 'required|string|size:6',
        ]);

        $emailNorm = Str::lower(trim($request->email));
        $otpKey = 'unlock-otp:' . $emailNorm . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($otpKey, 5)) {
            $seconds = RateLimiter::availableIn($otpKey);
            throw ValidationException::withMessages([
                'codigo' => "Demasiados intentos de desbloqueo. Intenta nuevamente en {$seconds} segundos.",
            ]);
        }

        $user = Usuario::where('email', $emailNorm)->first();

        if (!$user || !$user->cuenta_bloqueada) {
            RateLimiter::hit($otpKey, 600);
            throw ValidationException::withMessages([
                'codigo' => 'El correo o el código de desbloqueo no son válidos.',
            ]);
        }

        if ($user->bloqueo_administrativo) {
            RateLimiter::hit($otpKey, 600);
            throw ValidationException::withMessages([
                'email' => 'Tu cuenta fue suspendida por un administrador. Contacta a soporte para restablecer el acceso.',
            ]);
        }

        if (!$user->codigo_desbloqueo || !hash_equals((string) $user->codigo_desbloqueo, (string) $request->codigo)) {
            RateLimiter::hit($otpKey, 600);
            if (RateLimiter::attempts($otpKey) >= 5) {
                $user->update(['codigo_desbloqueo' => null, 'codigo_desbloqueo_expira' => null]);
            }
            throw ValidationException::withMessages([
                'codigo' => 'El correo o el código de desbloqueo no son válidos.',
            ]);
        }

        if ($user->codigo_desbloqueo_expira && now()->gt($user->codigo_desbloqueo_expira)) {
            throw ValidationException::withMessages([
                'codigo' => 'El código de desbloqueo ha expirado. Solicita un nuevo código.',
            ]);
        }

        $user->update([
            'cuenta_bloqueada' => false,
            'bloqueo_administrativo' => false,
            'intentos_fallidos' => 0,
            'codigo_desbloqueo' => null,
            'codigo_desbloqueo_expira' => null,
        ]);

        RateLimiter::clear($otpKey);

        return redirect()->route('login')->with('status', 'Cuenta desbloqueada correctamente. Ya puedes iniciar sesión.');
    }

    public function cambiarPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?~]/'],
        ], [
            'password.regex' => 'La contraseña debe contener mayúscula, minúscula, número y carácter especial.',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => $request->password,
            'debe_cambiar_password' => false,
        ]);

        return redirect()->route('panel.dashboard')->with('success', 'Contraseña actualizada correctamente.');
    }

    public function reenviarCodigo(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email|max:254']);

        $emailNorm = Str::lower(trim($request->email));
        $user = Usuario::where('email', $emailNorm)->first();

        // Solo genera código si existe, está bloqueada automáticamente y NO fue suspendida por un administrador
        if ($user && $user->cuenta_bloqueada && !$user->bloqueo_administrativo) {
            $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->update([
                'codigo_desbloqueo' => $codigo,
                'codigo_desbloqueo_expira' => now()->addMinutes(30),
            ]);

            try {
                Mail::raw(
                    "Tu nuevo código de desbloqueo para SIGESGA es: {$codigo}\n\nEste código expira en 30 minutos.",
                    function ($message) use ($user) {
                        $message->to($user->email)->subject('Nuevo código de desbloqueo - SIGESGA');
                    }
                );
            } catch (\Throwable $e) {
                Log::error('Error al reenviar código: ' . $e->getMessage());
            }
        }

        // Respuesta neutra para evitar enumeración de cuentas bloqueadas
        return back()->with('success', 'Si la cuenta es elegible para auto-desbloqueo, recibirás un nuevo código en tu correo electrónico.');
    }
}
