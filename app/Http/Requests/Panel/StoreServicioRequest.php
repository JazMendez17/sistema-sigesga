<?php

// FormRequest para validar los datos de servicios

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreServicioRequest extends FormRequest
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
            'cotizacion_id' => 'nullable|exists:cotizaciones,id',
            'operador_id' => 'nullable|exists:operadores,id',
            'unidad_id' => 'nullable|exists:unidades,id',
            'oficina_id' => 'nullable|exists:oficinas,id',
            'observaciones' => 'nullable|string|max:1000',
        ];

        if ($id) {
            $rules['estado'] = 'nullable|in:asignado,inicio_servicio,en_sitio_origen,salida_destino,en_destino,finalizado,solicitud_cancelacion,cancelado';
            $rules['kms_salida'] = 'nullable|integer|min:0';
            $rules['kms_llegada_cliente'] = 'nullable|integer|min:0';
            $rules['kms_termino_servicio'] = 'nullable|integer|min:0';
            $rules['kms_regreso_base'] = 'nullable|integer|min:0';
            $rules['kms_cobrados_reales'] = 'nullable|integer|min:0';
            $rules['cargo_zona_especial'] = 'nullable|numeric|min:0';
            $rules['costo_final_real'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'cotizacion_id.exists' => 'La cotización seleccionada no existe.',
            'operador_id.exists' => 'El operador seleccionado no existe.',
            'unidad_id.exists' => 'La unidad seleccionada no existe.',
            'oficina_id.exists' => 'La oficina seleccionada no existe.',
            'observaciones.max' => 'Las observaciones no deben exceder los 1000 caracteres.',
            'estado.in' => 'El estado del servicio no es válido.',
            'kms_salida.integer' => 'Los km de salida deben ser un número entero.',
            'kms_salida.min' => 'Los km de salida deben ser mayor o igual a 0.',
            'kms_llegada_cliente.integer' => 'Los km de llegada al cliente deben ser un número entero.',
            'kms_llegada_cliente.min' => 'Los km de llegada al cliente deben ser mayor o igual a 0.',
            'kms_termino_servicio.integer' => 'Los km de término de servicio deben ser un número entero.',
            'kms_termino_servicio.min' => 'Los km de término de servicio deben ser mayor o igual a 0.',
            'kms_regreso_base.integer' => 'Los km de regreso a base deben ser un número entero.',
            'kms_regreso_base.min' => 'Los km de regreso a base deben ser mayor o igual a 0.',
            'kms_cobrados_reales.integer' => 'Los km cobrados reales deben ser un número entero.',
            'kms_cobrados_reales.min' => 'Los km cobrados reales deben ser mayor o igual a 0.',
            'cargo_zona_especial.numeric' => 'El cargo por zona especial debe ser un valor numérico.',
            'cargo_zona_especial.min' => 'El cargo por zona especial debe ser mayor o igual a 0.',
            'costo_final_real.numeric' => 'El costo final real debe ser un valor numérico.',
            'costo_final_real.min' => 'El costo final real debe ser mayor o igual a 0.',
        ];
    }

    // Lanza excepción con los errores de validación
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
