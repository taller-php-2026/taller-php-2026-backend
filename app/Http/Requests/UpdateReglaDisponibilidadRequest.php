<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReglaDisponibilidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dia_semana'     => 'sometimes|string|max:20',
            'horaInicio'     => 'sometimes|date_format:H:i',
            'horaFin'        => 'sometimes|date_format:H:i|after:horaInicio',
            'pausaMinutos'   => 'sometimes|integer|min:0',
            'bufferMinutos'  => 'sometimes|integer|min:0',
            'activa'         => 'sometimes|boolean',
            'idAgenda'       => 'sometimes|integer|exists:agendas,idAgenda',
            'idProfesional'  => 'sometimes|integer|exists:profesionales,idUsuario',
        ];
    }
}
