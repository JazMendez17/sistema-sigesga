<?php

// FormRequest para validar los datos de oficinas

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreOficinaRequest extends FormRequest
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
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:150',
            'encargado' => 'nullable|string|max:150',
            'calle' => 'nullable|string|max:150',
            'numero_exterior' => 'nullable|string|max:50',
            'numero_interior' => 'nullable|string|max:50',
            'colonia' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'municipio_alcaldia' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:60',
            'pais' => 'nullable|string|max:60',
            'referencias' => 'nullable|string|max:500',
        ];
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la oficina es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 100 caracteres.',
            'telefono.max' => 'El teléfono no debe exceder los 25 caracteres.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.max' => 'El correo electrónico no debe exceder los 150 caracteres.',
            'encargado.max' => 'El encargado no debe exceder los 150 caracteres.',
            'codigo_postal.max' => 'El código postal no debe exceder los 10 caracteres.',
            'referencias.max' => 'Las referencias no deben exceder los 500 caracteres.',
        ];
    }

    // Lanza excepción con los errores de validación
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
