<?php

// FormRequest para validar los datos de aseguradoras

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreAseguradoraRequest extends FormRequest
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

        return [
            'nombre' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:13|unique:aseguradoras,rfc,' . $id,
            'telefono' => 'nullable|string|max:20',
            'contactos' => 'nullable|array',
            'contactos.*.departamento' => 'nullable|string|max:150',
            'contactos.*.nombre_contacto' => 'nullable|string|max:255',
            'contactos.*.telefono' => 'nullable|string|max:20',
            'contactos.*.email' => 'nullable|email|max:255',
        ];
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'rfc.unique' => 'El RFC ya está registrado en el sistema.',
            'rfc.max' => 'El RFC no debe exceder los 13 caracteres.',
            'telefono.max' => 'El teléfono no debe exceder los 20 caracteres.',
        ];
    }

    // Lanza excepción con los errores de validación
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
