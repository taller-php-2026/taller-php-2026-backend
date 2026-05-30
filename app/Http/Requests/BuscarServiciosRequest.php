<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuscarServiciosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'texto'       => 'nullable|string|max:255',
            'modalidad'   => 'nullable|in:presencial,virtual,hibrida',
            'precioMin'   => 'nullable|numeric|min:0',
            'precioMax'   => 'nullable|numeric|min:0',
            'ratingMin'   => 'nullable|numeric|min:0|max:5',
            'activo'      => 'nullable|boolean',
            'idProfesional' => 'nullable|integer|exists:profesionales,idUsuario',
            'ordenarPor'  => 'nullable|in:precio,rating,nombre,recientes',
            'orden'       => 'nullable|in:asc,desc',
            'perPage'     => 'nullable|integer|min:1|max:50',
        ];
    }
}
