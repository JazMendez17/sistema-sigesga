<?php

// FormRequest para validar la actualización de contraseña desde el panel

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UpdatePasswordRequest extends FormRequest
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
                $value = trim($value);
            }
        });
        $this->merge($data);
    }

    // Define las reglas de validación
    public function rules(): array
    {
        return [
            'actual' => 'required|current_password',
            'nueva' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?~]/'],
            'nueva_confirmation' => 'required',
        ];
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'actual.required' => 'La contraseña actual es obligatoria.',
            'actual.current_password' => 'La contraseña actual no es correcta.',
            'nueva.required' => 'La nueva contraseña es obligatoria.',
            'nueva.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'nueva.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'nueva.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula, un número y un carácter especial (!@#$%^&*()_+-=[]{}|;:,.<>?~).',
            'nueva_confirmation.required' => 'La confirmación de la nueva contraseña es obligatoria.',
        ];
    }

    // Lanza excepción con los errores de validación
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
