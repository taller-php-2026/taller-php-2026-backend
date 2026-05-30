<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResenaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario'   => 'nullable|string|max:500',
        ];
    }
}
