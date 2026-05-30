<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaqueteServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('paquete_servicio');

        return [
            'idServicio'    => [
                'sometimes',
                'integer',
                Rule::exists('servicios', 'idServicio'),
                Rule::unique('paquetes_servicios', 'idServicio')->ignore($id, 'idPaqueteServicio'),
            ],
            'totalSesiones' => 'sometimes|integer|min:1',
            'precio'        => 'sometimes|numeric|min:0',
            'activo'        => 'sometimes|boolean',
        ];
    }
}