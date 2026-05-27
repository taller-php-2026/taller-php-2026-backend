<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idCliente'     => 'sometimes|exists:clientes,idUsuario',
            'idProfesional' => 'sometimes|exists:profesionales,idUsuario',
            'idServicio'    => 'sometimes|exists:servicios,idServicio',
            'idHorario'     => 'sometimes|exists:horarios,idHorario',
            'fechaReserva'  => 'sometimes|date',
            'estado'        => 'sometimes|in:cancelada,pendiente,confirmada,enCurso,completada',
            'comentarios'   => 'nullable|string|max:255',
            'idPago'        => 'nullable|exists:pagos,idPago',
        ];
    }
}
