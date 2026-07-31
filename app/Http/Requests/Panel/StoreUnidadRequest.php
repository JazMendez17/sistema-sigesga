<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreUnidadRequest extends FormRequest
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
            'marca' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'placas' => 'required|string|max:20|unique:unidades,placas,' . $id,
            'numero_economico' => 'required|string|max:50|unique:unidades,numero_economico,' . $id,
            'seguro_vencimiento' => 'nullable|date',
            'estado_emplacado' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
            'oficina_id' => 'nullable|exists:oficinas,id',
            'operador_asignado_id' => 'nullable|exists:operadores,id|unique:unidades,operador_asignado_id,' . $id,
        ];
    }

    public function messages(): array
    {
        return [
            'marca.required' => 'La marca es obligatoria.',
            'marca.max' => 'La marca no debe exceder los 255 caracteres.',
            'tipo.required' => 'El tipo de unidad es obligatorio.',
            'tipo.max' => 'El tipo no debe exceder los 255 caracteres.',
            'modelo.max' => 'El modelo no debe exceder los 255 caracteres.',
            'placas.required' => 'Las placas son obligatorias.',
            'placas.max' => 'Las placas no deben exceder los 20 caracteres.',
            'placas.unique' => 'Las placas ya están registradas en el sistema.',
            'numero_economico.required' => 'El número económico es obligatorio.',
            'numero_economico.max' => 'El número económico no debe exceder los 50 caracteres.',
            'numero_economico.unique' => 'El número económico ya está registrado en el sistema.',
            'seguro_vencimiento.date' => 'La fecha de vencimiento del seguro no tiene un formato válido.',

            'estado_emplacado.max' => 'El estado emplacado no debe exceder los 255 caracteres.',
            'activo.boolean' => 'El campo activo debe ser verdadero o falso.',
            'oficina_id.exists' => 'La oficina seleccionada no existe.',
            'operador_asignado_id.exists' => 'El operador seleccionado no existe.',
            'operador_asignado_id.unique' => 'El operador ya está asignado a otra unidad.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
