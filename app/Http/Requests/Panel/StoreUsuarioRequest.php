<?php

// FormRequest para validar los datos de usuarios (con control de rol superadmin y aislamiento multi-tenant)

namespace App\Http\Requests\Panel;

use App\Models\Usuario;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();
        array_walk_recursive($data, function (&$value, $key) {
            if (is_string($value) && $key !== 'password') {
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
        $user = $this->user();
        $empresaId = $user?->empresa_id;
        $esSuperAdmin = $user?->isSuperAdmin() ?? false;

        $rolesPermitidos = $esSuperAdmin
            ? ['admin', 'cotizador', 'operador', 'cliente']
            : ['cotizador', 'operador', 'cliente'];

        if ($id && $esSuperAdmin) {
            $objetivo = Usuario::find($id);
            if ($objetivo && $objetivo->isSuperAdmin()) {
                $rolesPermitidos = ['superadmin'];
            }
        }

        $empleadoExists = Rule::exists('empleados', 'id');
        if (!$esSuperAdmin && $empresaId) {
            $empleadoExists->where('empresa_id', $empresaId);
        }

        $rules = [
            'name' => 'required|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'email')->ignore($id)],
            'rol' => ['required', Rule::in($rolesPermitidos)],
            'empleado_id' => ['nullable', $empleadoExists, Rule::unique('usuarios', 'empleado_id')->ignore($id)],
            'telefono' => 'nullable|string|max:20',
        ];

        $passwordPolicy = ['string', 'min:8', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[!@#$%^&*()_+\-=\[\]{}|;:,.<>?~]/'];

        if ($id) {
            $rules['password'] = array_merge(['nullable'], $passwordPolicy);
            $rules['cuenta_bloqueada'] = 'nullable|boolean';
        } else {
            $rules['password'] = array_merge(['required'], $passwordPolicy);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de usuario es obligatorio.',
            'name.max' => 'El nombre no debe exceder los 150 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.max' => 'El correo electrónico no debe exceder los 150 caracteres.',
            'email.unique' => 'El correo electrónico ya está registrado en el sistema.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula, un número y un carácter especial (!@#$%^&*()_+-=[]{}|;:,.<>?~).',
            'password.string' => 'La contraseña debe ser una cadena de texto.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.in' => 'No tienes permisos para asignar ese rol o el rol no es válido.',
            'empleado_id.exists' => 'El empleado seleccionado no existe en tu empresa.',
            'empleado_id.unique' => 'El empleado seleccionado ya tiene un usuario asignado.',
            'telefono.max' => 'El teléfono no debe exceder los 20 caracteres.',
            'cuenta_bloqueada.boolean' => 'El campo cuenta bloqueada debe ser verdadero o falso.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
