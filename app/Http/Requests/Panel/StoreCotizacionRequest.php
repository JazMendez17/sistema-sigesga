<?php

// FormRequest para validar los datos de cotizaciones

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreCotizacionRequest extends FormRequest
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
            'cliente_id' => 'required|exists:clientes,id',
            'tipo_servicio_id' => 'required|exists:catalogo_servicios,id',
            'origen_direccion' => 'required|string|max:500',
            'destino_direccion' => 'required|string|max:500',
            'distancia_km' => 'required|numeric|min:0.1',
            'costo_banderazo' => 'required|numeric|min:0',
            'costo_km' => 'required|numeric|min:0',
            'costo_total' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
            'folio' => 'nullable|string|max:50|unique:cotizaciones,folio,' . $id,
            'origen_lat' => 'nullable|numeric|between:-90,90',
            'origen_lng' => 'nullable|numeric|between:-180,180',
            'destino_lat' => 'nullable|numeric|between:-90,90',
            'destino_lng' => 'nullable|numeric|between:-180,180',
        ];

        if ($id) {
            $rules['estatus'] = 'nullable|in:pendiente,aprobado,rechazado';
        }

        return $rules;
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'tipo_servicio_id.required' => 'El tipo de servicio es obligatorio.',
            'tipo_servicio_id.exists' => 'El tipo de servicio seleccionado no existe.',
            'origen_direccion.required' => 'La dirección de origen es obligatoria.',
            'origen_direccion.max' => 'La dirección de origen no debe exceder los 500 caracteres.',
            'destino_direccion.required' => 'La dirección de destino es obligatoria.',
            'destino_direccion.max' => 'La dirección de destino no debe exceder los 500 caracteres.',
            'distancia_km.required' => 'La distancia es obligatoria.',
            'distancia_km.numeric' => 'La distancia debe ser un valor numérico.',
            'distancia_km.min' => 'La distancia mínima es de 0.1 km.',
            'costo_total.required' => 'El costo total es obligatorio.',
            'costo_total.numeric' => 'El costo total debe ser un valor numérico.',
            'costo_total.min' => 'El costo total debe ser mayor o igual a 0.',
            'costo_banderazo.required' => 'El costo de banderazo es obligatorio.',
            'costo_km.required' => 'El costo por km es obligatorio.',
            'folio.unique' => 'El folio ya está registrado en el sistema.',
            'folio.max' => 'El folio no debe exceder los 50 caracteres.',
            'origen_lat.numeric' => 'La latitud de origen debe ser un valor numérico.',
            'origen_lat.between' => 'La latitud de origen debe estar entre -90 y 90.',
            'origen_lng.numeric' => 'La longitud de origen debe ser un valor numérico.',
            'origen_lng.between' => 'La longitud de origen debe estar entre -180 y 180.',
            'destino_lat.numeric' => 'La latitud de destino debe ser un valor numérico.',
            'destino_lat.between' => 'La latitud de destino debe estar entre -90 y 90.',
            'destino_lng.numeric' => 'La longitud de destino debe ser un valor numérico.',
            'destino_lng.between' => 'La longitud de destino debe estar entre -180 y 180.',
            'costo_banderazo.numeric' => 'El costo de banderazo debe ser un valor numérico.',
            'costo_banderazo.min' => 'El costo de banderazo debe ser mayor o igual a 0.',
            'costo_km.numeric' => 'El costo por km debe ser un valor numérico.',
            'costo_km.min' => 'El costo por km debe ser mayor o igual a 0.',
            'estatus.in' => 'El estatus debe ser pendiente, aprobado o rechazado.',
            'observaciones.max' => 'Las observaciones no deben exceder los 1000 caracteres.',
        ];
    }

    // Lanza excepción con los errores de validación
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
