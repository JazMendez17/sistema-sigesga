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
            'origen_lat' => 'required|numeric|between:-90,90',
            'origen_lng' => 'required|numeric|between:-180,180',
            'destino_lat' => 'required|numeric|between:-90,90',
            'destino_lng' => 'required|numeric|between:-180,180',
        ];
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'origen_lat.required' => 'La latitud de origen es obligatoria (selecciona una dirección con autocompletado).',
            'origen_lat.numeric' => 'La latitud de origen debe ser numérica.',
            'origen_lat.between' => 'La latitud de origen debe estar entre -90 y 90.',
            'origen_lng.required' => 'La longitud de origen es obligatoria (selecciona una dirección con autocompletado).',
            'origen_lng.numeric' => 'La longitud de origen debe ser numérica.',
            'origen_lng.between' => 'La longitud de origen debe estar entre -180 y 180.',
            'destino_lat.required' => 'La latitud de destino es obligatoria (selecciona una dirección con autocompletado).',
            'destino_lat.numeric' => 'La latitud de destino debe ser numérica.',
            'destino_lat.between' => 'La latitud de destino debe estar entre -90 y 90.',
            'destino_lng.required' => 'La longitud de destino es obligatoria (selecciona una dirección con autocompletado).',
            'destino_lng.numeric' => 'La longitud de destino debe ser numérica.',
            'destino_lng.between' => 'La longitud de destino debe estar entre -180 y 180.',
        ];
    }

    // Lanza excepción con los errores de validación
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
