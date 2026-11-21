<?php

// FormRequest para validar los datos de tarifas de convenio

namespace App\Http\Requests\Panel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreConvenioTarifaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = trim($value) === '' ? null : trim($value);
            }
        });
        $this->merge($data);
        if ($this->isJson()) {
            $this->json()->replace($data);
        }
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'convenio_id' => ['required', Rule::exists('convenios', 'id')->where('empresa_id', $empresaId)],
            'servicio_id' => ['required', Rule::exists('catalogo_servicios', 'id')->where('empresa_id', $empresaId)],
            'servicio' => 'nullable|string|max:150',
            'alcance' => 'nullable|string|max:100',
            'banderazo' => 'nullable|numeric|min:0',
            'km_incluidos' => 'nullable|integer|min:0',
            'costo_km_extra' => 'nullable|numeric|min:0',
            'tarifa_nocturna_recargo_pct' => 'nullable|numeric|between:0,999.99',
            'tarifa_domingo_festivo_recargo_pct' => 'nullable|numeric|between:0,999.99',
            'minutos_espera_incluidos' => 'nullable|integer|min:0',
            'costo_espera_adicional_hora' => 'nullable|numeric|min:0',
            'descuento_pct' => 'nullable|numeric|between:0,999.99',
            'tipo_descuento' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'convenio_id.required' => 'El convenio es obligatorio.',
            'convenio_id.exists' => 'El convenio seleccionado no existe.',
            'servicio_id.required' => 'El tipo de servicio es obligatorio.',
            'servicio_id.exists' => 'El tipo de servicio seleccionado no existe.',
            'banderazo.numeric' => 'El banderazo debe ser un valor numérico.',
            'banderazo.min' => 'El banderazo debe ser mayor o igual a 0.',
            'km_incluidos.integer' => 'Los km incluidos deben ser un número entero.',
            'km_incluidos.min' => 'Los km incluidos deben ser mayor o igual a 0.',
            'costo_km_extra.numeric' => 'El costo km extra debe ser un valor numérico.',
            'costo_km_extra.min' => 'El costo km extra debe ser mayor o igual a 0.',
            'tarifa_nocturna_recargo_pct.numeric' => 'El recargo nocturno debe ser un valor numérico.',
            'tarifa_domingo_festivo_recargo_pct.numeric' => 'El recargo dom/festivo debe ser un valor numérico.',
            'minutos_espera_incluidos.integer' => 'Los minutos de espera deben ser un número entero.',
            'costo_espera_adicional_hora.numeric' => 'El costo de espera adicional debe ser un valor numérico.',
            'descuento_pct.numeric' => 'El descuento debe ser un valor numérico.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
