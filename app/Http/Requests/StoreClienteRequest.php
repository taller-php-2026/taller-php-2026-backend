<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idUsuario' => 'required|integer|exists:usuarios,idUsuario|unique:clientes,idUsuario',
        ];
    }
}
