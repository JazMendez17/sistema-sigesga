<?php

// Controlador para solicitar el enlace de restablecimiento de contraseña

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    // Muestra el formulario de solicitud de enlace de restablecimiento
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    // Envía el enlace de restablecimiento de contraseña al correo
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => is_string($request->email) ? trim(strip_tags($request->email)) : $request->email,
        ]);

        $request->validate([
            'email' => 'required|email:rfc,dns|max:254',
        ]);

        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.');
    }
}
