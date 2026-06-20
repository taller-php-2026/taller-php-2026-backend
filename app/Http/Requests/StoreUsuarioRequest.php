<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'   => 'required|string|max:255',
            'email'    => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:8',
            'telefono' => 'required|string|max:50',
            'activo'   => 'nullable|boolean',
            'rol'      => 'nullable|string|in:administrador,cliente,profesional',
        ];
    }
}
