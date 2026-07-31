<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreAseguradoraRequest extends FormRequest
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
            'nombre' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:13|unique:aseguradoras,rfc,' . $id,
            'telefono' => 'nullable|string|max:20',
        ];
    }

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

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
