<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReglaDisponibilidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dia_semana'     => 'required|string|max:20',
            'horaInicio'     => 'required|date_format:H:i',
            'horaFin'        => 'required|date_format:H:i|after:horaInicio',
            'pausaMinutos'   => 'required|integer|min:0',
            'bufferMinutos'  => 'required|integer|min:0',
            'activa'         => 'nullable|boolean',
            'idAgenda'       => 'required|integer|exists:agendas,idAgenda',
            'idProfesional'  => 'sometimes|integer|exists:profesionales,idUsuario',
        ];
    }
}
