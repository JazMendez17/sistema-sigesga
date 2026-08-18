<?php

// FormRequest para validar los datos de unidades

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreUnidadRequest extends FormRequest
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
        $empresaId = $this->user()?->empresa_id;

        return [
            'marca' => 'required|string|max:50',
            'tipo' => 'required|string|max:50',
            'modelo' => 'nullable|string|max:45',
            'placas' => ['required', 'string', 'max:20', Rule::unique('unidades', 'placas')
                ->where('empresa_id', $empresaId)
                ->ignore($id)],
            'numero_economico' => ['required', 'string', 'max:50', Rule::unique('unidades', 'numero_economico')
                ->where('empresa_id', $empresaId)
                ->ignore($id)],
            'seguro_vencimiento' => 'nullable|date|after:today',
            'estado_emplacado' => 'nullable|string|max:50',
            'activo' => 'nullable|boolean',
            'oficina_id' => 'nullable|exists:oficinas,id',
            'operador_asignado_id' => ['nullable', 'exists:operadores,id', Rule::unique('unidades', 'operador_asignado_id')
                ->where('empresa_id', $empresaId)
                ->ignore($id)],
        ];
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'marca.required' => 'La marca es obligatoria.',
            'marca.max' => 'La marca no debe exceder los 50 caracteres.',
            'tipo.required' => 'El tipo de unidad es obligatorio.',
            'tipo.max' => 'El tipo no debe exceder los 50 caracteres.',
            'modelo.max' => 'El modelo no debe exceder los 45 caracteres.',
            'placas.required' => 'Las placas son obligatorias.',
            'placas.max' => 'Las placas no deben exceder los 20 caracteres.',
            'placas.unique' => 'Las placas ya están registradas en esta empresa.',
            'numero_economico.required' => 'El número económico es obligatorio.',
            'numero_economico.max' => 'El número económico no debe exceder los 50 caracteres.',
            'numero_economico.unique' => 'El número económico ya está registrado en esta empresa.',
            'seguro_vencimiento.date' => 'La fecha de vencimiento del seguro no tiene un formato válido.',
            'seguro_vencimiento.after' => 'La fecha de vencimiento del seguro debe ser posterior a hoy.',

            'estado_emplacado.max' => 'El estado emplacado no debe exceder los 50 caracteres.',
            'activo.boolean' => 'El campo activo debe ser verdadero o falso.',
            'oficina_id.exists' => 'La oficina seleccionada no existe.',
            'operador_asignado_id.exists' => 'El operador seleccionado no existe.',
            'operador_asignado_id.unique' => 'El operador ya está asignado a otra unidad.',
        ];
    }

    // Lanza excepción con los errores de validación
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
