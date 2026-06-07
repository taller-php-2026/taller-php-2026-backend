<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'        => 'required|string|max:255',
            'email'         => 'required|email|unique:usuarios,email',
            'password'      => 'required|string|min:8|confirmed',
            'telefono'      => 'required|string|max:50',
            'tipo'          => 'required|in:cliente,profesional',
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
            'password.confirmed'        => 'La confirmación de contraseña no coincide.',
            'email.unique'              => 'Este email ya está registrado.',
        ];
    }
}
