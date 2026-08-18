<?php

// FormRequest para validar los datos de operadores

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreOperadorRequest extends FormRequest
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
            'empleado_id' => ['required', 'exists:empleados,id', Rule::unique('operadores', 'empleado_id')
                ->where('empresa_id', $this->user()?->empresa_id)
                ->ignore($id)],
            'tipo_licencia' => 'required|string|max:50',
            'numero_licencia' => 'required|string|max:50',
            'fecha_expedicion' => 'required|date',
            'fecha_vigencia' => ['required', 'date', 'after:today', 'after_or_equal:fecha_expedicion', function ($attribute, $value, $fail) {
                $expedicion = $this->input('fecha_expedicion');
                if (!$expedicion || !$value) return;
                try {
                    $exp = \Carbon\Carbon::parse($expedicion);
                    $vig = \Carbon\Carbon::parse($value);
                } catch (\Exception $e) {
                    $fail('Error en la vigencia: ingrese un formato válido.');
                    return;
                }
                if (!$vig->gt($exp)) {
                    $fail('La fecha de vigencia debe ser posterior a la fecha de expedición.');
                    return;
                }
                $diffYears = $exp->diffInYears($vig);
                if ($diffYears < 2 || $diffYears > 4) {
                    $fail('Error en la vigencia: ingrese un formato válido, solo años exactos (2, 3 o 4 años).');
                    return;
                }
                $expected = $exp->copy()->addYears($diffYears);
                if (!$vig->isSameDay($expected)) {
                    $fail('Error en la vigencia: la fecha debe ser exactamente ' . $diffYears . ' años después (' . $expected->format('d/m/Y') . ').');
                }
            }],
            'disponible' => 'nullable|boolean',
        ];
    }

    // Mensajes de error personalizados en español
    public function messages(): array
    {
        return [
            'empleado_id.required' => 'El empleado es obligatorio.',
            'empleado_id.exists' => 'El empleado seleccionado no existe.',
            'empleado_id.unique' => 'El empleado ya está registrado como operador.',
            'tipo_licencia.required' => 'El tipo de licencia es obligatorio.',
            'tipo_licencia.max' => 'El tipo de licencia no debe exceder los 50 caracteres.',
            'numero_licencia.required' => 'El número de licencia es obligatorio.',
            'numero_licencia.max' => 'El número de licencia no debe exceder los 50 caracteres.',
            'fecha_expedicion.required' => 'La fecha de expedición es obligatoria.',
            'fecha_expedicion.date' => 'La fecha de expedición no tiene un formato válido.',
            'fecha_vigencia.required' => 'La fecha de vigencia es obligatoria.',
            'fecha_vigencia.date' => 'La fecha de vigencia no tiene un formato válido.',
            'fecha_vigencia.after' => 'La licencia no puede estar vencida. La fecha de vigencia debe ser posterior a hoy.',
            'fecha_vigencia.after_or_equal' => 'La fecha de vigencia debe ser posterior o igual a la fecha de expedición.',
            'disponible.boolean' => 'El campo disponible debe ser verdadero o falso.',
        ];
    }

    // Lanza excepción con los errores de validación
    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
