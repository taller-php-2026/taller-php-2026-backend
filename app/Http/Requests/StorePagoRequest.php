<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monto'             => 'required|numeric|min:0',
            'metodoPago'        => 'nullable|string|max:255',
            'estado'            => 'nullable|in:pendiente,aprobado,rechazado,cancelado,reembolsado',
            'fechaPago'         => 'nullable|date',
            'referenciaExterna' => 'nullable|string|max:255',
        ];
    }
}
