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
        $user = $request->user();

        if (! $user->cliente) {
            return response()->json([
                'message' => 'Solo los clientes pueden realizar reservas.',
            ], 403);
        }

        $validated              = $request->validated();
        $validated['idCliente'] = $user->idUsuario;

        $data = $this->service->reservar(
            (int) $id,
            $validated['idCliente'],
            $validated['idServicio'],
            $validated['fecha'],
            $validated['horaInicio'],
            isset($validated['idPaqueteComprado']) ? (int) $validated['idPaqueteComprado'] : null
        );

        return response()->json([
            'message' => 'Reserva creada correctamente',
            'data'    => $data,
        ], 201);
    }
}
