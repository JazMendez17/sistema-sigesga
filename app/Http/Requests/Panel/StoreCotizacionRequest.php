<?php

// FormRequest para validar los datos de cotizaciones (borrado físico, multi-tenant y km_excedente)

namespace App\Http\Requests\Panel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreCotizacionRequest extends FormRequest
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
        $empresaId = $this->user()?->empresa_id;

        return [
            'cliente_id' => ['required', Rule::exists('clientes', 'id')->where('empresa_id', $empresaId)],
            'tipo_servicio_id' => ['required', Rule::exists('catalogo_servicios', 'id')->where('empresa_id', $empresaId)],
            'convenio_aplicado_id' => ['nullable', Rule::exists('convenios', 'id')->where('empresa_id', $empresaId)],
            'tarifa_empresa_aplicada_id' => ['nullable', Rule::exists('tarifas_empresa', 'id')->where('empresa_id', $empresaId)],
            'origen_direccion' => 'required|string|max:500',
            'destino_direccion' => 'required|string|max:500',
            'distancia_km' => 'required|numeric|min:0.1',
            'costo_banderazo' => 'required|numeric|min:0',
            'costo_km' => 'required|numeric|min:0',
            'costo_total' => 'required|numeric|min:0',
            'subtotal' => 'nullable|numeric|min:0',
            'monto_descuento' => 'nullable|numeric|min:0',
            'descuento_pct' => 'nullable|numeric|between:0,100',
            'monto_iva' => 'nullable|numeric|min:0',
            'km_incluidos' => 'nullable|numeric|min:0',
            'km_excedente' => 'nullable|numeric|min:0',
            'costo_aprox_casetas' => 'nullable|numeric|min:0',
            'incluye_peajes' => 'nullable|boolean',
            'cargo_zona_especial' => 'nullable|numeric|min:0',
            'folio' => ['nullable', 'string', 'max:50', Rule::unique('cotizaciones', 'folio')->ignore($id)],
            'origen_lat' => 'nullable|numeric|between:-90,90',
            'origen_lng' => 'nullable|numeric|between:-180,180',
            'destino_lat' => 'nullable|numeric|between:-90,90',
            'destino_lng' => 'nullable|numeric|between:-180,180',
        ];
    }

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
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
