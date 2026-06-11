<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaqueteServicioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'           => 'required|string|max:255',
            'descripcion'      => 'required|string',
            'duracionMinutos'  => 'required|integer|min:1',
            'modalidad'        => 'sometimes|in:presencial,virtual,hibrida',
            'idUbicacion'      => 'nullable|exists:ubicaciones,idUbicacion',
            'idVideoSesion'    => 'nullable|exists:video_sesiones,idVideoSesion',
            'idProfesional'    => 'required|integer|exists:profesionales,idUsuario',
            'servicios_ids'    => 'required|array|min:1',
            'servicios_ids.*'  => 'integer|exists:servicios,idServicio',
            'totalSesiones'    => 'required|integer|min:1',
            'precio'           => 'required|numeric|min:0',
            'activo'           => 'sometimes|boolean',
        ];
    }
}