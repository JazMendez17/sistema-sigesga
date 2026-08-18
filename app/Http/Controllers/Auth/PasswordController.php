<?php

// Controlador para actualizar la contraseña del usuario autenticado

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    // Actualiza la contraseña del usuario
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::min(8)->letters()->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back();
    }
}
