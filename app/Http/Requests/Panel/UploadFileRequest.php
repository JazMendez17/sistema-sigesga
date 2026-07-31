<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UploadFileRequest extends FormRequest
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
                $value = trim($value);
            }
        });
        $this->merge($data);
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|image|mimes:jpeg,png,jpg,webp|max:5120',
            'type' => 'required|in:logo,imagen_fondo,servicio,foto',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'El archivo es obligatorio.',
            'file.file' => 'El archivo no es válido.',
            'file.image' => 'El archivo debe ser una imagen.',
            'file.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg o webp.',
            'file.max' => 'La imagen no debe superar los 5 MB de tamaño.',
            'type.required' => 'El tipo de archivo es obligatorio.',
            'type.in' => 'El tipo de archivo debe ser: logo, imagen_fondo, servicio o foto.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}
