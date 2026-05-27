<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monto'             => 'sometimes|numeric|min:0',
            'metodoPago'        => 'sometimes|nullable|string|max:255',
            'estado'            => 'sometimes|in:pendiente,aprobado,rechazado,cancelado,reembolsado',
            'fechaPago'         => 'sometimes|nullable|date',
            'referenciaExterna' => 'sometimes|nullable|string|max:255',
        ];
    }
}
