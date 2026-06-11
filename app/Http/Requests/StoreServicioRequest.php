<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'          => 'required|string|max:255',
            'descripcion'     => 'required|string',
            'precio'          => 'required|numeric|min:0',
            'duracionMinutos' => 'required|integer|min:1',
            'activo'          => 'sometimes|boolean',
            'modalidad'       => 'sometimes|in:presencial,virtual,hibrida',
            'idUbicacion'     => 'nullable|exists:ubicaciones,idUbicacion',
            'idVideoSesion'   => 'nullable|exists:video_sesiones,idVideoSesion',
            'idProfesional'   => 'sometimes|integer|exists:profesionales,idUsuario',
        ];
    }
}
