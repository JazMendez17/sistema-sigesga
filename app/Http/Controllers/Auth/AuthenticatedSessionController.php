<?php

// Controlador de gestión de sesiones (inicio y cierre de sesión)

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    // Muestra la vista de inicio de sesión
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    // Procesa la petición de inicio de sesión
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Si debe cambiar contraseña, redirigir a la página de cambio
        if (Auth::user()->debe_cambiar_password) {
            return redirect()->route('password.cambiar');
        }

        return redirect()->intended(route('panel.dashboard', absolute: false));
    }

    // Cierra la sesión del usuario autenticado
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    // Desbloquea la cuenta del usuario mediante código enviado por correo
    public function unlock(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'codigo' => 'required|string|size:6',
        ]);

        $user = Usuario::where('email', $request->email)->first();

        if (!$user || !$user->cuenta_bloqueada) {
            throw ValidationException::withMessages([
                'email' => 'No se encontró una cuenta bloqueada con ese correo.',
            ]);
        }

        if ($user->codigo_desbloqueo !== $request->codigo) {
            throw ValidationException::withMessages([
                'codigo' => 'El código de desbloqueo no es válido.',
            ]);
        }

        if ($user->codigo_desbloqueo_expira && now()->gt($user->codigo_desbloqueo_expira)) {
            throw ValidationException::withMessages([
                'codigo' => 'El código de desbloqueo ha expirado. Solicita un nuevo código.',
            ]);
        }

        $user->update([
            'cuenta_bloqueada' => false,
            'intentos_fallidos' => 0,
            'codigo_desbloqueo' => null,
            'codigo_desbloqueo_expira' => null,
        ]);

        return redirect()->route('login')->with('status', 'Cuenta desbloqueada correctamente. Ya puedes iniciar sesión.');
    }

    // Cambiar contraseña obligatoria al primer inicio de sesión
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

    // Reenviar código de desbloqueo por correo
    public function reenviarCodigo(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = Usuario::where('email', $request->email)->first();

        if (!$user || !$user->cuenta_bloqueada) {
            return back()->with('error', 'No se encontró una cuenta bloqueada con ese correo.');
        }

        $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update([
            'codigo_desbloqueo' => $codigo,
            'codigo_desbloqueo_expira' => now()->addMinutes(30),
        ]);

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Tu nuevo código de desbloqueo para SIGESGA es: {$codigo}\n\nEste código expira en 30 minutos.",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Nuevo código de desbloqueo - SIGESGA');
                }
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al reenviar código: ' . $e->getMessage());
        }

        return back()->with('success', 'Se ha enviado un nuevo código a tu correo electrónico.');
    }
}
