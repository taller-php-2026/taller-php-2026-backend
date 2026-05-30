<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminReservasAgrupadasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado'        => 'nullable|in:cancelada,pendiente,confirmada,enCurso,completada',
            'idProfesional' => 'nullable|integer|exists:profesionales,idUsuario',
            'idServicio'    => 'nullable|integer|exists:servicios,idServicio',
            'modalidad'     => 'nullable|in:presencial,virtual,hibrida',
            'fechaDesde'    => 'nullable|date',
            'fechaHasta'    => 'nullable|date|after_or_equal:fechaDesde',
        ];
    }
}
