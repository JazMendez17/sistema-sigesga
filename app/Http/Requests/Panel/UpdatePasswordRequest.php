<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UpdatePasswordRequest extends FormRequest
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
                $value = trim($value);
            }
        });
        $this->merge($data);
    }

    public function rules(): array
    {
        return [
            'actual' => 'required|current_password',
            'nueva' => 'required|string|min:8|confirmed',
            'nueva_confirmation' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'actual.required' => 'La contraseña actual es obligatoria.',
            'actual.current_password' => 'La contraseña actual no es correcta.',
            'nueva.required' => 'La nueva contraseña es obligatoria.',
            'nueva.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'nueva.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'nueva_confirmation.required' => 'La confirmación de la nueva contraseña es obligatoria.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
