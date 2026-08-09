<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

// FormRequest para validar maniobras especiales de convenio
class StoreManiobraEspecialRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $data = $this->all();
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) $value = trim($value) === '' ? null : trim($value);
        });
        $this->merge($data);
        if ($this->isJson()) $this->json()->replace($data);
    }

    public function rules(): array
    {
        return [
            'convenio_id' => 'required|exists:convenios,id',
            'concepto' => 'required|string|max:150',
            'aplica' => 'nullable|boolean',
            'forma_cobro' => 'nullable|string|max:50',
            'costo' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'convenio_id.required' => 'El convenio es obligatorio.',
            'convenio_id.exists' => 'El convenio no existe.',
            'concepto.required' => 'El concepto es obligatorio.',
            'concepto.max' => 'Máximo 150 caracteres.',
            'costo.required' => 'El costo es obligatorio.',
            'costo.numeric' => 'El costo debe ser un valor numérico.',
            'costo.min' => 'El costo no puede ser negativo.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
