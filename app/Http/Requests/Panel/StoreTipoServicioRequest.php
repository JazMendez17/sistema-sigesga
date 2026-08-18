<?php

// FormRequest para validar los datos de tipos de servicio

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreTipoServicioRequest extends FormRequest
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
        $id = $this->route('id');

        $rules = [
            'requiere_maniobra' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ];

        if (!$id || $this->has('nombre')) {
            $empresaId = $this->user()?->empresa_id;
            $rules['nombre'] = ['required', 'string', 'max:100', Rule::unique('catalogo_servicios', 'nombre')
                ->where('empresa_id', $empresaId)
                ->whereNull('deleted_at')
                ->ignore($id)];
        }

        return $rules;
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del tipo de servicio es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 100 caracteres.',
            'nombre.unique' => 'El nombre del tipo de servicio ya está registrado.',
            'requiere_maniobra.boolean' => 'El campo requiere maniobra debe ser verdadero o falso.',
            'activo.boolean' => 'El campo activo debe ser verdadero o falso.',
        ];
    }

    // Lanza excepción con los errores de validación
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
