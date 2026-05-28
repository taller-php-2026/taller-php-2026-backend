<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservarSlotRequest;
use App\Services\ReservaSlotService;

class ReservaSlotController extends Controller
{
    public function __construct(private ReservaSlotService $service) {}

    public function reservar(ReservarSlotRequest $request, $id)
    {
        $validated = $request->validated();

        $data = $this->service->reservar(
            (int) $id,
            $validated['idCliente'],
            $validated['idServicio'],
            $validated['fecha'],
            $validated['horaInicio']
        );

        return response()->json([
            'message' => 'Reserva creada correctamente',
            'data'    => $data,
        ], 201);
    }
}
