<?php

// FormRequest para validar los datos de tarifas propias

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreTarifaPropiaRequest extends FormRequest
{
    // Autoriza la petición
    public function authorize(): bool
    {
        return true;
    }

    // Limpia los campos antes de la validación
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

    // Define las reglas de validación
    public function rules(): array
    {
        return [
            'nombre_tarifa' => 'required|string|max:255',
            'tipo_servicio_id' => 'required|exists:catalogo_servicios,id',
            'tipo_ruta' => 'required|in:local,foraneo',
            'costo_banderazo' => 'nullable|numeric|min:0',
            'costo_km' => 'nullable|numeric|min:0',
            'km_incluidos' => 'nullable|numeric|min:0',
            'cubre_casetas_peaje' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ];
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'nombre_tarifa.required' => 'El nombre de la tarifa es obligatorio.',
            'nombre_tarifa.max' => 'El nombre de la tarifa no debe exceder los 255 caracteres.',
            'tipo_servicio_id.required' => 'El tipo de servicio es obligatorio.',
            'tipo_servicio_id.exists' => 'El tipo de servicio seleccionado no existe.',
            'tipo_ruta.required' => 'El tipo de ruta es obligatorio.',
            'tipo_ruta.in' => 'El tipo de ruta debe ser local o foraneo.',
            'costo_banderazo.numeric' => 'El costo de banderazo debe ser un valor numérico.',
            'costo_banderazo.min' => 'El costo de banderazo debe ser mayor o igual a 0.',
            'costo_km.numeric' => 'El costo por km debe ser un valor numérico.',
            'costo_km.min' => 'El costo por km debe ser mayor o igual a 0.',
            'km_incluidos.numeric' => 'Los km incluidos deben ser un valor numérico.',
            'km_incluidos.min' => 'Los km incluidos deben ser mayor o igual a 0.',
            'cubre_casetas_peaje.boolean' => 'El campo cubre casetas de peaje debe ser verdadero o falso.',
            'activo.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }

    // Lanza excepción con los errores de validación
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
