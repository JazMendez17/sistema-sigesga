<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreConvenioRequest extends FormRequest
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
        return [
            'nombre_convenio_poliza' => 'required|string|max:255',
            'aseguradora_id' => 'required|exists:aseguradoras,id',
            'tipo_servicio_id' => 'required|exists:catalogo_servicios,id',
            'tipo_ruta' => 'required|in:local,foraneo',
            'tipo_cobertura' => 'nullable|string|max:255',
            'alcance_geografico' => 'nullable|string|max:255',
            'costo_banderazo' => 'nullable|numeric|min:0',
            'costo_km' => 'nullable|numeric|min:0',
            'km_seguros_incluidos' => 'nullable|numeric|min:0',
            'km_maximo_amparado' => 'nullable|numeric|min:0',
            'tope_presupuesto' => 'nullable|numeric|min:0',
            'cubre_casetas_peaje' => 'nullable|boolean',
            'dias_credito' => 'nullable|integer|min:0',
            'proceso_envio_facturas' => 'nullable|string|max:255',
            'estatus' => 'nullable|in:vigente,vencido,en_negociacion,cancelado',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_convenio_poliza.required' => 'El nombre del convenio o póliza es obligatorio.',
            'nombre_convenio_poliza.max' => 'El nombre del convenio no debe exceder los 255 caracteres.',
            'aseguradora_id.required' => 'La aseguradora es obligatoria.',
            'aseguradora_id.exists' => 'La aseguradora seleccionada no existe.',
            'tipo_servicio_id.required' => 'El tipo de servicio es obligatorio.',
            'tipo_servicio_id.exists' => 'El tipo de servicio seleccionado no existe.',
            'tipo_ruta.required' => 'El tipo de ruta es obligatorio.',
            'tipo_ruta.in' => 'El tipo de ruta debe ser local o foraneo.',
            'costo_banderazo.numeric' => 'El costo de banderazo debe ser un valor numérico.',
            'costo_banderazo.min' => 'El costo de banderazo debe ser mayor o igual a 0.',
            'costo_km.numeric' => 'El costo por km debe ser un valor numérico.',
            'costo_km.min' => 'El costo por km debe ser mayor o igual a 0.',
            'km_seguros_incluidos.numeric' => 'Los km seguros incluidos deben ser un valor numérico.',
            'km_seguros_incluidos.min' => 'Los km seguros incluidos deben ser mayor o igual a 0.',
            'km_maximo_amparado.numeric' => 'El km máximo amparado debe ser un valor numérico.',
            'km_maximo_amparado.min' => 'El km máximo amparado debe ser mayor o igual a 0.',
            'tope_presupuesto.numeric' => 'El tope de presupuesto debe ser un valor numérico.',
            'tope_presupuesto.min' => 'El tope de presupuesto debe ser mayor o igual a 0.',
            'cubre_casetas_peaje.boolean' => 'El campo cubre casetas de peaje debe ser verdadero o falso.',
            'dias_credito.integer' => 'Los días de crédito deben ser un número entero.',
            'dias_credito.min' => 'Los días de crédito deben ser mayor o igual a 0.',
            'estatus.in' => 'El estatus debe ser vigente, vencido, en_negociacion o cancelado.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
