<?php

// FormRequest para validar los datos de servicios (borrado físico, multi-tenant y unique en update)

namespace App\Http\Requests\Panel;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreServicioRequest extends FormRequest
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

        $rules = [
            'observaciones' => 'nullable|string|max:1000',
        ];

        if ($id) {
            $rules['cotizacion_id'] = [
                'sometimes',
                'required',
                Rule::exists('cotizaciones', 'id')->where('empresa_id', $empresaId),
                Rule::unique('servicios', 'cotizacion_id')->ignore($id),
            ];
            $rules['operador_id'] = ['sometimes', 'required', Rule::exists('operadores', 'id')->where('empresa_id', $empresaId)];
            $rules['unidad_id'] = ['sometimes', 'required', Rule::exists('unidades', 'id')->where('empresa_id', $empresaId)];
            $rules['oficina_id'] = ['nullable', Rule::exists('oficinas', 'id')->where('empresa_id', $empresaId)];
            $rules['estado'] = 'nullable|in:asignado,inicio_servicio,en_sitio_origen,salida_destino,en_destino,finalizado,solicitud_cancelacion,cancelado';
            $rules['kms_salida'] = 'nullable|integer|min:0';
            $rules['kms_llegada_cliente'] = 'nullable|integer|min:0';
            $rules['kms_termino_servicio'] = 'nullable|integer|min:0';
            $rules['kms_regreso_base'] = 'nullable|integer|min:0';
            $rules['kms_cobrados_reales'] = 'nullable|integer|min:0';
            $rules['cargo_zona_especial'] = 'nullable|numeric|min:0';
            $rules['costo_final_real'] = 'nullable|numeric|min:0';
        } else {
            $rules['cotizacion_id'] = [
                'required',
                Rule::exists('cotizaciones', 'id')->where('empresa_id', $empresaId),
                Rule::unique('servicios', 'cotizacion_id'),
            ];
            $rules['operador_id'] = ['required', Rule::exists('operadores', 'id')->where('empresa_id', $empresaId)];
            $rules['unidad_id'] = ['required', Rule::exists('unidades', 'id')->where('empresa_id', $empresaId)];
            $rules['oficina_id'] = ['nullable', Rule::exists('oficinas', 'id')->where('empresa_id', $empresaId)];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'cotizacion_id.required' => 'La cotización es obligatoria.',
            'cotizacion_id.exists' => 'La cotización seleccionada no existe.',
            'cotizacion_id.unique' => 'Esta cotización ya tiene un servicio asignado.',
            'operador_id.required' => 'El operador es obligatorio.',
            'operador_id.exists' => 'El operador seleccionado no existe.',
            'unidad_id.required' => 'La unidad es obligatoria.',
            'unidad_id.exists' => 'La unidad seleccionada no existe.',
            'oficina_id.exists' => 'La oficina seleccionada no existe.',
            'observaciones.max' => 'Las observaciones no deben exceder los 1000 caracteres.',
            'estado.in' => 'El estado del servicio no es válido.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
