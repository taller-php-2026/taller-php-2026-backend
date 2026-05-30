<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminReservasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado'        => 'nullable|in:cancelada,pendiente,confirmada,enCurso,completada',
            'idProfesional' => 'nullable|integer|exists:profesionales,idUsuario',
            'idServicio'    => 'nullable|integer|exists:servicios,idServicio',
            'idCliente'     => 'nullable|integer|exists:clientes,idUsuario',
            'fechaDesde'    => 'nullable|date',
            'fechaHasta'    => 'nullable|date|after_or_equal:fechaDesde',
            'perPage'       => 'nullable|integer|min:1|max:50',
        ];
    }
}
