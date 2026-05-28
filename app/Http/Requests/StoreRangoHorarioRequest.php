<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRangoHorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'diaSemana'  => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
            'horaInicio' => 'required|date_format:H:i',
            'horaFin'    => 'required|date_format:H:i|after:horaInicio',
            'idCiclo'    => 'required|integer|exists:ciclos,idCiclo',
        ];
    }
}
