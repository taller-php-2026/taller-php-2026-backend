<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminPaquetesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado'            => 'nullable|in:pendiente,activo,agotado,cancelado',
            'idCliente'         => 'nullable|integer|exists:clientes,idUsuario',
            'idPaqueteServicio' => 'nullable|integer|exists:paquetes_servicios,idPaqueteServicio',
            'fechaDesde'        => 'nullable|date',
            'fechaHasta'        => 'nullable|date|after_or_equal:fechaDesde',
            'perPage'           => 'nullable|integer|min:1|max:50',
        ];
    }
}
