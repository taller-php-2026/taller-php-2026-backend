<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaqueteServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idServicio'    => 'required|integer|exists:servicios,idServicio|unique:paquetes_servicios,idServicio',
            'totalSesiones' => 'required|integer|min:1',
            'precio'        => 'required|numeric|min:0',
            'activo'        => 'sometimes|boolean',
        ];
    }
}