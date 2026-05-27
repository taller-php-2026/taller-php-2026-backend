<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'          => 'sometimes|string|max:255',
            'descripcion'     => 'sometimes|string',
            'precio'          => 'sometimes|numeric|min:0',
            'duracionMinutos' => 'sometimes|integer|min:1',
            'activo'          => 'sometimes|boolean',
            'modalidad'       => 'sometimes|in:presencial,virtual,hibrida',
            'idUbicacion'     => 'nullable|exists:ubicaciones,idUbicacion',
            'idVideoSesion'   => 'nullable|exists:video_sesiones,idVideoSesion',
        ];
    }
}
