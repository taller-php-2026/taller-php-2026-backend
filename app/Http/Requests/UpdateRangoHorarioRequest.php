<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRangoHorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'diaSemana'  => 'sometimes|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
            'horaInicio' => 'sometimes|date_format:H:i',
            'horaFin'    => 'sometimes|date_format:H:i|after:horaInicio',
            'idCiclo'    => 'sometimes|integer|exists:ciclos,idCiclo',
        ];
    }
}
