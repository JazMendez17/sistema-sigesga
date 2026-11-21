<?php

// FormRequest para validar y procesar el inicio de sesión con rate limiting completo

namespace App\Http\Requests\Auth;

use App\Models\Usuario;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => is_string($this->email) ? trim(strip_tags($this->email)) : $this->email,
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'password' => ['required', 'string', 'min:6', 'max:128'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Credenciales incorrectas.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'Credenciales incorrectas.',
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = Usuario::where('email', $this->email)->first();

        if ($user && $user->cuenta_bloqueada && !$user->isSuperAdmin()) {
            $this->hitRateLimiter();
            $mensaje = $user->bloqueo_administrativo
                ? 'Tu cuenta ha sido suspendida por un administrador. Contacta a soporte.'
                : 'Tu cuenta está bloqueada temporalmente. Usa la opción de desbloqueo con el código enviado a tu correo.';

            throw ValidationException::withMessages(['email' => $mensaje]);
        }

        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $this->hitRateLimiter();

            if ($user && !$user->isSuperAdmin()) {
                $user->increment('intentos_fallidos');
                $user->refresh();

                if ($user->intentos_fallidos >= 4) {
                    $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $user->update([
                        'cuenta_bloqueada' => true,
                        'bloqueo_administrativo' => false,
                        'bloqueada_en' => now(),
                        'codigo_desbloqueo' => $codigo,
                        'codigo_desbloqueo_expira' => now()->addMinutes(30),
                    ]);

                    try {
                        Mail::raw(
                            "Tu cuenta en SIGESGA ha sido bloqueada por múltiples intentos fallidos.\n\n" .
                            "Código de desbloqueo: {$codigo}\n" .
                            "Este código expira en 30 minutos.\n\n" .
                            "Si no fuiste tú, contacta al administrador.",
                            function ($message) use ($user) {
                                $message->to($user->email)->subject('Código de desbloqueo - SIGESGA');
                            }
                        );
                    } catch (\Throwable $e) {
                        Log::error('Error al enviar correo de desbloqueo: ' . $e->getMessage());
                    }

                    throw ValidationException::withMessages([
                        'email' => 'Tu cuenta ha sido bloqueada por seguridad. Se ha enviado un código de desbloqueo a tu correo electrónico.',
                    ]);
                }
            }

            // Mensaje genérico para evitar enumeración de cuentas y roles
            throw ValidationException::withMessages(['email' => 'Credenciales incorrectas.']);
        }

        if ($user) {
            $user->update([
                'intentos_fallidos' => 0,
                'codigo_desbloqueo' => null,
                'codigo_desbloqueo_expira' => null,
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        RateLimiter::clear($this->emailThrottleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        $limits = [
            [$this->throttleKey(), 5],
            [$this->ipThrottleKey(), 25],
            [$this->emailThrottleKey(), 10],
        ];

        foreach ($limits as [$key, $maxAttempts]) {
            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                event(new Lockout($this));
                $seconds = RateLimiter::availableIn($key);

                throw ValidationException::withMessages([
                    'email' => trans('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => ceil($seconds / 60),
                    ]),
                ]);
            }
        }
    }

    protected function hitRateLimiter(): void
    {
        RateLimiter::hit($this->throttleKey(), 300);
        RateLimiter::hit($this->ipThrottleKey(), 900);
        RateLimiter::hit($this->emailThrottleKey(), 300);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }

    protected function ipThrottleKey(): string
    {
        return 'login-ip:' . $this->ip();
    }

    protected function emailThrottleKey(): string
    {
        return 'login-email:' . Str::lower((string) $this->email);
    }
}
