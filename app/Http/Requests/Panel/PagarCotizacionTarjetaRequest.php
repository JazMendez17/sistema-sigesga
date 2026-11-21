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
        $numero = preg_replace('/\D+/', '', (string) $this->input('tarjeta_numero', '')) ?? '';
        $cvv = preg_replace('/\D+/', '', (string) $this->input('tarjeta_cvv', '')) ?? '';
        $titular = trim((string) $this->input('tarjeta_titular', ''));

        $anio = $this->input('tarjeta_exp_anio');
        if (is_string($anio) && strlen(trim($anio)) === 2 && ctype_digit(trim($anio))) {
            $anio = 2000 + (int) trim($anio);
        }

        $this->merge([
            'tarjeta_titular' => $titular,
            'tarjeta_numero' => $numero,
            'tarjeta_exp_mes' => $this->input('tarjeta_exp_mes') !== null ? (int) $this->input('tarjeta_exp_mes') : null,
            'tarjeta_exp_anio' => $anio !== null ? (int) $anio : null,
            'tarjeta_cvv' => $cvv,
        ]);
    }

    public function rules(): array
    {
        return [
            'tarjeta_titular' => ['required', 'string', 'min:3', 'max:150'],
            'tarjeta_numero' => [
                'required',
                'digits_between:13,19',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (!PagoTarjetaService::validarLuhn((string) $value)) {
                        $fail('El número de tarjeta no es válido.');
                    }
                },
            ],
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
            'tarjeta_cvv' => ['required', 'digits_between:3,4'],
        ];
    }

    public function messages(): array
    {
        return [
            'tarjeta_titular.required' => 'El nombre del titular de la tarjeta es obligatorio.',
            'tarjeta_titular.min' => 'Ingresa el nombre completo del titular.',
            'tarjeta_numero.required' => 'El número de tarjeta es obligatorio.',
            'tarjeta_numero.digits_between' => 'El número de tarjeta debe contener entre 13 y 19 dígitos.',
            'tarjeta_exp_mes.required' => 'El mes de vencimiento es obligatorio.',
            'tarjeta_exp_mes.between' => 'El mes de vencimiento debe estar entre 01 y 12.',
            'tarjeta_exp_anio.required' => 'El año de vencimiento es obligatorio.',
            'tarjeta_cvv.required' => 'El código de seguridad (CVV) es obligatorio.',
            'tarjeta_cvv.digits_between' => 'El CVV debe tener 3 o 4 dígitos.',
        ];
    }
}
