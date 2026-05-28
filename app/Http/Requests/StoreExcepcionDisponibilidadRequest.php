<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExcepcionDisponibilidadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha'      => 'required|date',
            'horaInicio' => 'required|date_format:H:i',
            'horaFin'    => 'required|date_format:H:i|after:horaInicio',
            'motivo'     => 'nullable|string|max:255',
            'idAgenda'   => 'required|integer|exists:agendas,idAgenda',
        ];
    }
}
