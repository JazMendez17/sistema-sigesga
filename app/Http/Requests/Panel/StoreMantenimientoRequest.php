<?php

// FormRequest para validar los datos de mantenimientos

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreMantenimientoRequest extends FormRequest
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
            'unidad_id' => 'required|exists:unidades,id',
            'tipo' => 'required|string|max:255',
            'fecha' => 'required|date',
            'kilometraje' => 'required|integer|min:0',
            'costo' => 'required|numeric|min:0',
            'proximo_mantenimiento_fecha' => 'nullable|date|after:today',
            'proximo_mantenimiento_km' => 'nullable|integer|min:0',
            'observaciones' => 'nullable|string|max:1000',
        ];
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'unidad_id.required' => 'La unidad es obligatoria.',
            'unidad_id.exists' => 'La unidad seleccionada no existe.',
            'tipo.required' => 'El tipo de mantenimiento es obligatorio.',
            'tipo.max' => 'El tipo de mantenimiento no debe exceder los 255 caracteres.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha no tiene un formato válido.',
            'kilometraje.required' => 'El kilometraje es obligatorio.',
            'kilometraje.integer' => 'El kilometraje debe ser un número entero.',
            'kilometraje.min' => 'El kilometraje debe ser mayor o igual a 0.',
            'costo.required' => 'El costo es obligatorio.',
            'costo.numeric' => 'El costo debe ser un valor numérico.',
            'costo.min' => 'El costo debe ser mayor o igual a 0.',
            'proximo_mantenimiento_fecha.date' => 'La fecha del próximo mantenimiento no tiene un formato válido.',
            'proximo_mantenimiento_fecha.after' => 'La fecha del próximo mantenimiento debe ser posterior a hoy.',
            'proximo_mantenimiento_km.integer' => 'El km del próximo mantenimiento debe ser un número entero.',
            'proximo_mantenimiento_km.min' => 'El km del próximo mantenimiento debe ser mayor o igual a 0.',
            'observaciones.max' => 'Las observaciones no deben exceder los 1000 caracteres.',
        ];
    }

    // Lanza excepción con los errores de validación
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
