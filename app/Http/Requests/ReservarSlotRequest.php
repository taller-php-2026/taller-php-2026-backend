<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservarSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'idCliente'         => 'required|integer|exists:clientes,idUsuario',
            'idServicio'        => 'required|integer|exists:servicios,idServicio',
            'fecha'             => 'required|date_format:Y-m-d',
            'horaInicio'        => 'required|date_format:H:i',
            'idPaqueteComprado' => 'sometimes|nullable|integer|exists:paquetes_comprados,idPaqueteComprado',
        ];
    }
}
