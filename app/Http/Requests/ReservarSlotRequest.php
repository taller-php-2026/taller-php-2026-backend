<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservarSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idServicio'        => 'required|integer|exists:servicios,idServicio',
            'fecha'             => 'required|date_format:Y-m-d|after_or_equal:today',
            'horaInicio'        => 'required|date_format:H:i',
            'idPaqueteComprado' => 'sometimes|nullable|integer|exists:paquetes_comprados,idPaqueteComprado',
        ];
    }

    public function messages(): array
    {
        return [
            'idServicio.required'        => 'El servicio es obligatorio.',
            'idServicio.exists'          => 'El servicio indicado no existe.',
            'fecha.required'             => 'La fecha es obligatoria.',
            'fecha.date_format'          => 'El formato de fecha debe ser YYYY-MM-DD.',
            'fecha.after_or_equal'       => 'La fecha no puede ser anterior a hoy.',
            'horaInicio.required'        => 'La hora de inicio es obligatoria.',
            'horaInicio.date_format'     => 'El formato de hora debe ser HH:MM.',
            'idPaqueteComprado.exists'   => 'El paquete comprado indicado no existe.',
        ];
    }
}
