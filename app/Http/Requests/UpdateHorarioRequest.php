<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHorarioRequest extends FormRequest
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
        ];
    }
}
