<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('usuario');

        return [
            'nombre'   => 'sometimes|string|max:255',
            'email'    => ['sometimes', 'email', Rule::unique('usuarios', 'email')->ignore($id, 'idUsuario')],
            'password' => 'sometimes|nullable|string|min:8',
            'telefono' => 'sometimes|string|max:50',
            'activo'   => 'sometimes|boolean',
        ];
    }
}
