<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfesionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombreNegocio' => 'sometimes|string|max:255',
            'descripcion'   => 'sometimes|string|max:255',
            'color'         => 'sometimes|string|max:7',
        ];
    }
}
