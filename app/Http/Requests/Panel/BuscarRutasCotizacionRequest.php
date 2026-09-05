<?php

// FormRequest para la búsqueda de rutas de cotización (Google Routes API).

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class BuscarRutasCotizacionRequest extends FormRequest
{
    // Autoriza la petición
    public function authorize(): bool
    {
        return true;
    }

    // Define las reglas de validación
    public function rules(): array
    {
        return [
            'origen' => 'required|string|max:500',
            'destino' => 'required|string|max:500',
        ];
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'origen.required' => 'La dirección de origen es obligatoria.',
            'origen.max' => 'La dirección de origen no debe exceder 500 caracteres.',
            'destino.required' => 'La dirección de destino es obligatoria.',
            'destino.max' => 'La dirección de destino no debe exceder 500 caracteres.',
        ];
    }

    // Lanza excepción con los errores de validación
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
