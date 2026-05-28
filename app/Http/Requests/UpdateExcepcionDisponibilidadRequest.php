<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExcepcionDisponibilidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha'      => 'sometimes|date',
            'horaInicio' => 'sometimes|date_format:H:i',
            'horaFin'    => 'sometimes|date_format:H:i|after:horaInicio',
            'motivo'     => 'sometimes|nullable|string|max:255',
            'idAgenda'   => 'sometimes|integer|exists:agendas,idAgenda',
        ];
    }
}
