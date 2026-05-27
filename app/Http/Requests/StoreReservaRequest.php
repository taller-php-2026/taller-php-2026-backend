<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idCliente'     => 'required|exists:clientes,idUsuario',
            'idProfesional' => 'required|exists:profesionales,idUsuario',
            'idServicio'    => 'required|exists:servicios,idServicio',
            'idHorario'     => 'required|exists:horarios,idHorario',
            'fechaReserva'  => 'required|date',
            'comentarios'   => 'nullable|string|max:255',
            'idPago'        => 'nullable|exists:pagos,idPago',
        ];
    }
}
