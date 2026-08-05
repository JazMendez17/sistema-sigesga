<?php

// FormRequest para validar los datos de clientes

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class StoreClienteRequest extends FormRequest
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
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'tipo_cliente' => 'nullable|in:persona_fisica,persona_moral',
            'sexo' => 'nullable|in:M,F',
            'curp' => 'nullable|string|max:18|unique:clientes,curp,' . $id,
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:20',
            'telefono_local' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'folio_ine' => 'nullable|string|max:255',
            'nacionalidad' => 'nullable|string|max:255',
            'contacto_enlace' => 'nullable|string|max:255',
            'aseguradora_id' => 'nullable|exists:aseguradoras,id',
            'numero_poliza' => 'nullable|string|max:255',
            'tipo_cobertura_poliza' => 'nullable|string|max:255',
            'calle' => 'nullable|string|max:255',
            'numero_exterior' => 'nullable|string|max:50',
            'numero_interior' => 'nullable|string|max:50',
            'colonia' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:10',
            'municipio_alcaldia' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:255',
            'pais' => 'nullable|string|max:255',
            'referencias' => 'nullable|string|max:500',
        ];
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'tipo_cliente.in' => 'El tipo de cliente debe ser persona_fisica o persona_moral.',
            'sexo.in' => 'El sexo debe ser M o F.',
            'curp.unique' => 'El CURP ya está registrado en el sistema.',
            'curp.max' => 'El CURP no debe exceder los 18 caracteres.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no tiene un formato válido.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.max' => 'El correo electrónico no debe exceder los 255 caracteres.',
            'aseguradora_id.exists' => 'La aseguradora seleccionada no existe.',
            'codigo_postal.max' => 'El código postal no debe exceder los 10 caracteres.',
            'referencias.max' => 'Las referencias no deben exceder los 500 caracteres.',
        ];
    }

    // Lanza excepción con los errores de validación
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
