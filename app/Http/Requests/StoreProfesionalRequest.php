<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfesionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idUsuario'     => 'required|integer|exists:usuarios,idUsuario|unique:profesionales,idUsuario',
            'nombreNegocio' => 'required|string|max:255',
            'descripcion'   => 'required|string|max:255',
        ];
    }
}
