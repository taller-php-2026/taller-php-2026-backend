<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DisponibilidadProfesionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha'      => 'required|date_format:Y-m-d',
            'idServicio' => 'required|integer|exists:servicios,idServicio',
        ];
    }
}
