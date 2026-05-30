<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PagarPaqueteCompradoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'metodoPago' => 'required|string|max:255',
            'referenciaExterna' => 'nullable|string|max:255',
        ];
    }
}
