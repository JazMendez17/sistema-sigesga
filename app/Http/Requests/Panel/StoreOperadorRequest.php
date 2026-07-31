<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreOperadorRequest extends FormRequest
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
        $id = $this->route('id');

        return [
            'empleado_id' => 'required|exists:empleados,id|unique:operadores,empleado_id,' . $id,
            'tipo_licencia' => 'nullable|string|max:255',
            'numero_licencia' => 'nullable|string|max:255',
            'fecha_expedicion' => 'nullable|date',
            'fecha_vigencia' => 'nullable|date|after_or_equal:fecha_expedicion',
            'disponible' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'empleado_id.required' => 'El empleado es obligatorio.',
            'empleado_id.exists' => 'El empleado seleccionado no existe.',
            'empleado_id.unique' => 'El empleado ya está registrado como operador.',
            'tipo_licencia.max' => 'El tipo de licencia no debe exceder los 255 caracteres.',
            'numero_licencia.max' => 'El número de licencia no debe exceder los 255 caracteres.',
            'fecha_expedicion.date' => 'La fecha de expedición no tiene un formato válido.',
            'fecha_vigencia.date' => 'La fecha de vigencia no tiene un formato válido.',
            'fecha_vigencia.after_or_equal' => 'La fecha de vigencia debe ser posterior o igual a la fecha de expedición.',
            'disponible.boolean' => 'El campo disponible debe ser verdadero o falso.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
