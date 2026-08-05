<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureRegistrationIsNotRateLimited($request);

        $request->merge([
            'name' => is_string($request->name) ? trim(strip_tags($request->name)) : $request->name,
            'email' => is_string($request->email) ? trim(strip_tags($request->email)) : $request->email,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email:rfc,dns|max:254|unique:'.Usuario::class,
            'password' => ['required', 'confirmed', Rules\Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ], [
            'email.unique' => 'No se pudo completar el registro.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        RateLimiter::hit($this->registrationThrottleKey($request), 3600);

        $user = Usuario::create([
            'empresa_id' => 1,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => 'cliente',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('panel.dashboard', absolute: false));
    }

    protected function ensureRegistrationIsNotRateLimited(Request $request): void
    {
        $key = $this->registrationThrottleKey($request);

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Demasiados intentos de registro. Intenta de nuevo en {$seconds} segundos.",
            ]);
        }
    }

    protected function registrationThrottleKey(Request $request): string
    {
        return 'register:' . $request->ip();
    }
}
