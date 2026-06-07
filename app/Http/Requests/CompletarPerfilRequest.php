<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompletarPerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo'          => 'required|in:cliente,profesional',
            'telefono'      => 'required|string|max:50',
            'nombreNegocio' => 'required_if:tipo,profesional|string|max:255',
            'descripcion'   => 'required_if:tipo,profesional|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.in'                   => 'El tipo debe ser cliente o profesional.',
            'nombreNegocio.required_if' => 'El nombre del negocio es obligatorio para profesionales.',
            'descripcion.required_if'   => 'La descripción es obligatoria para profesionales.',
        ];
    }
}
