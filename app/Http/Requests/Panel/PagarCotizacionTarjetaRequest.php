<?php

namespace App\Http\Requests\Panel;

use App\Services\PagoTarjetaService;
use Illuminate\Foundation\Http\FormRequest;

class PagarCotizacionTarjetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $ultimos4 = preg_replace('/\D+/', '', (string) $this->input('tarjeta_ultimos4', '')) ?? '';
        $titular = trim((string) $this->input('tarjeta_titular', ''));

        $anio = $this->input('tarjeta_exp_anio');
        if (is_string($anio) && strlen(trim($anio)) === 2 && ctype_digit(trim($anio))) {
            $anio = 2000 + (int) trim($anio);
        }

        $this->merge([
            'mp_token' => is_string($this->input('mp_token')) ? trim($this->input('mp_token')) : null,
            'mp_idempotency_key' => is_string($this->input('mp_idempotency_key')) ? trim($this->input('mp_idempotency_key')) : null,
            'mp_payment_method_id' => is_string($this->input('mp_payment_method_id')) ? strtolower(trim($this->input('mp_payment_method_id'))) : null,
            'mp_payment_type_id' => is_string($this->input('mp_payment_type_id')) ? strtolower(trim($this->input('mp_payment_type_id'))) : null,
            'mp_installments' => $this->input('mp_installments') !== null ? (int) $this->input('mp_installments') : 1,
            'tarjeta_titular' => $titular,
            'tarjeta_ultimos4' => $ultimos4,
            'tarjeta_marca' => is_string($this->input('tarjeta_marca')) ? trim($this->input('tarjeta_marca')) : null,
            'tarjeta_exp_mes' => $this->input('tarjeta_exp_mes') !== null ? (int) $this->input('tarjeta_exp_mes') : null,
            'tarjeta_exp_anio' => $anio !== null ? (int) $anio : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'tarjeta_titular' => ['required', 'string', 'min:3', 'max:150'],
            'tarjeta_exp_mes' => ['required', 'integer', 'between:1,12'],
            'tarjeta_exp_anio' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $mes = (int) $this->input('tarjeta_exp_mes');
                    if (!PagoTarjetaService::validarVigencia($mes, (int) $value)) {
                        $fail('La tarjeta está vencida o el año de vencimiento no es válido.');
                    }
                },
            ],
            'mp_token' => ['required', 'string', 'min:32', 'max:33'],
            'mp_idempotency_key' => ['required', 'uuid'],
            'mp_payment_method_id' => ['required', 'string', 'max:40'],
            'mp_payment_type_id' => ['required', 'string', 'in:credit_card,debit_card'],
            'mp_installments' => ['required', 'integer', 'between:1,12'],
            'mp_device_id' => ['nullable', 'string', 'max:255'],
            'tarjeta_ultimos4' => ['required', 'digits:4'],
            'tarjeta_marca' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function messages(): array
    {
        return [
            'tarjeta_titular.required' => 'El nombre del titular de la tarjeta es obligatorio.',
            'tarjeta_titular.min' => 'Ingresa el nombre completo del titular.',
            'tarjeta_exp_mes.required' => 'El mes de vencimiento es obligatorio.',
            'tarjeta_exp_mes.between' => 'El mes de vencimiento debe estar entre 01 y 12.',
            'tarjeta_exp_anio.required' => 'El año de vencimiento es obligatorio.',
            'mp_token.required' => 'No se pudo generar el token seguro de Mercado Pago. Verifica los datos de tu tarjeta.',
            'mp_idempotency_key.required' => 'No se pudo identificar este intento de pago. Actualiza la página e intenta de nuevo.',
            'mp_idempotency_key.uuid' => 'El identificador del intento de pago no es válido.',
            'mp_payment_method_id.required' => 'No se pudo identificar el tipo de tarjeta en Mercado Pago.',
            'tarjeta_ultimos4.required' => 'No se pudieron leer los últimos 4 dígitos de la tarjeta.',
        ];
    }
}
