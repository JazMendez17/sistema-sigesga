<?php

// FormRequest para validar los datos de empleados

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreEmpleadoRequest extends FormRequest
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

        return [
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'sexo' => 'nullable|in:M,F',
            'curp' => ['nullable', 'string', 'size:18', Rule::unique('empleados', 'curp')
                ->where('empresa_id', $this->user()?->empresa_id)
                ->ignore($id)],
            'fecha_nacimiento' => 'nullable|date|before:today',
            'telefono' => 'nullable|string|max:20',
            'telefono_local' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:150',
            'folio_ine' => 'nullable|string|max:20',
            'nacionalidad' => 'nullable|string|max:50',
            'puesto' => 'nullable|string|max:50',
            'sueldo_diario' => 'nullable|numeric|min:0',
            'oficina_id' => 'nullable|exists:oficinas,id',
            'direccion.calle' => 'nullable|string|max:150',
            'direccion.numero_exterior' => 'nullable|string|max:50',
            'direccion.numero_interior' => 'nullable|string|max:50',
            'direccion.colonia' => 'nullable|string|max:100',
            'direccion.codigo_postal' => 'nullable|string|max:10',
            'direccion.municipio_alcaldia' => 'nullable|string|max:100',
            'direccion.ciudad' => 'nullable|string|max:100',
            'direccion.estado' => 'nullable|string|max:60',
            'direccion.pais' => 'nullable|string|max:60',
            'direccion.referencias' => 'nullable|string|max:500',
        ];
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 100 caracteres.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'apellido_paterno.max' => 'El apellido paterno no debe exceder los 100 caracteres.',
            'apellido_materno.max' => 'El apellido materno no debe exceder los 100 caracteres.',
            'sexo.in' => 'El sexo debe ser M o F.',
            'curp.size' => 'El CURP debe tener exactamente 18 caracteres.',
            'curp.unique' => 'El CURP ya está registrado en esta empresa.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no tiene un formato válido.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'correo.email' => 'El formato del correo electrónico no es válido.',
            'correo.max' => 'El correo electrónico no debe exceder los 150 caracteres.',
            'folio_ine.max' => 'El folio INE no debe exceder los 20 caracteres.',
            'puesto.max' => 'El puesto no debe exceder los 50 caracteres.',
            'sueldo_diario.numeric' => 'El sueldo diario debe ser un valor numérico.',
            'sueldo_diario.min' => 'El sueldo diario debe ser mayor o igual a 0.',
            'oficina_id.exists' => 'La oficina seleccionada no existe.',
        ];
    }

    // Lanza excepción con los errores de validación
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
