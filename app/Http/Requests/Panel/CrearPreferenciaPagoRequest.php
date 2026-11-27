<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;

class CrearPreferenciaPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mp_idempotency_key' => trim((string) $this->input('mp_idempotency_key')),
        ]);
    }

    public function rules(): array
    {
        return [
            'mp_idempotency_key' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'mp_idempotency_key.required' => 'No se pudo identificar este intento de pago. Actualiza la página e intenta de nuevo.',
            'mp_idempotency_key.uuid' => 'No se pudo identificar este intento de pago. Actualiza la página e intenta de nuevo.',
        ];
    }
}
