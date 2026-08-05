<?php

// FormRequest para validar y procesar el inicio de sesión

namespace App\Http\Requests\Auth;

use App\Models\Usuario;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    // Autoriza la petición de login
    public function authorize(): bool
    {
        return true;
    }

    // Limpia los campos de entrada antes de validar
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => is_string($this->email) ? trim(strip_tags($this->email)) : $this->email,
            'password' => is_string($this->password) ? trim($this->password) : $this->password,
        ]);
    }

    // Reglas de validación para el login
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc,dns', 'max:254'],
            'password' => ['required', 'string', 'min:6', 'max:128'],
        ];
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Credenciales incorrectas.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'Credenciales incorrectas.',
        ];
    }

    // Intenta autenticar al usuario con bloqueo progresivo de cuenta
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = Usuario::where('email', $this->email)->first();

        if ($user && $user->cuenta_bloqueada && $user->rol !== 'admin') {
            $this->hitRateLimiter();
            throw ValidationException::withMessages([
                'email' => 'Credenciales incorrectas.',
            ]);
        }

        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            if ($user && $user->rol !== 'admin') {
                $user->increment('intentos_fallidos');
                if ($user->intentos_fallidos >= 5) {
                    $user->update([
                        'cuenta_bloqueada' => true,
                        'bloqueada_en' => now(),
                    ]);
                }
            }

            $this->hitRateLimiter();

            throw ValidationException::withMessages([
                'email' => 'Credenciales incorrectas.',
            ]);
        }

        if ($user) {
            $user->update(['intentos_fallidos' => 0]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    // Verifica que no se exceda el límite de intentos de inicio de sesión
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    // Registra un intento fallido en el limitador de tasa
    protected function hitRateLimiter(): void
    {
        RateLimiter::hit($this->throttleKey(), 300);

        $ipKey = 'login-ip:' . $this->ip();
        RateLimiter::hit($ipKey, 900);

        $emailKey = 'login-email:' . Str::lower($this->email);
        RateLimiter::hit($emailKey, 300);
    }

    // Genera la clave única para el limitador de tasa (email + IP)
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
