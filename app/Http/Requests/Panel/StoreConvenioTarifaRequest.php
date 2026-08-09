<?php

// FormRequest para validar los datos de tarifas de convenio

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
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
        return [
            'convenio_id' => 'required|exists:convenios,id',
            'servicio_id' => 'required|exists:catalogo_servicios,id',
            'servicio' => 'nullable|string|max:150',
            'alcance' => 'nullable|string|max:100',
            'banderazo' => 'nullable|numeric|min:0',
            'km_incluidos' => 'nullable|integer|min:0',
            'costo_km_extra' => 'nullable|numeric|min:0',
            'tarifa_nocturna_recargo_pct' => 'nullable|numeric|min:0',
            'tarifa_domingo_festivo_recargo_pct' => 'nullable|numeric|min:0',
            'minutos_espera_incluidos' => 'nullable|integer|min:0',
            'costo_espera_adicional_hora' => 'nullable|numeric|min:0',
            'descuento_pct' => 'nullable|numeric|min:0',
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
