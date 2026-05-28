<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PagarReservaRequest;
use App\Http\Requests\ReprogramarReservaRequest;
use App\Http\Requests\StoreReservaRequest;
use App\Http\Requests\UpdateReservaRequest;
use App\Services\ReservaService;

class ReservaController extends Controller
{
    public function __construct(private ReservaService $reservaService) {}

    public function index()
    {
        $reservas = $this->reservaService->getAll();

        return response()->json([
            'data' => $reservas,
        ]);
    }

    public function store(StoreReservaRequest $request)
    {
        $reserva = $this->reservaService->create($request->validated());

        return response()->json([
            'message' => 'Reserva creada correctamente',
            'data'    => $reserva,
        ], 201);
    }

    public function show($id)
    {
        $reserva = $this->reservaService->getById((int) $id);

        return response()->json([
            'data' => $reserva,
        ]);
    }

    public function update(UpdateReservaRequest $request, $id)
    {
        $reserva = $this->reservaService->getById((int) $id);
        $reserva = $this->reservaService->update($reserva, $request->validated());

        return response()->json([
            'message' => 'Reserva actualizada correctamente',
            'data'    => $reserva,
        ]);
    }

    public function destroy($id)
    {
        $reserva = $this->reservaService->getById((int) $id);
        $this->reservaService->delete($reserva);

        return response()->json([
            'message' => 'Reserva eliminada correctamente',
        ]);
    }

    public function reprogramar(ReprogramarReservaRequest $request, $id)
    {
        $data = $this->reservaService->reprogramar((int) $id, $request->validated());

        return response()->json([
            'message' => 'Reserva reprogramada correctamente',
            'data'    => $data,
        ]);
    }

    public function cancelar($id)
    {
        $reserva = $this->reservaService->cancelar((int) $id);

        return response()->json([
            'message' => 'Reserva cancelada correctamente',
            'data'    => $reserva,
        ]);
    }

    public function pagar(PagarReservaRequest $request, $id)
    {
        $reserva = $this->reservaService->pagar((int) $id, $request->validated());

        return response()->json([
            'message' => 'Pago procesado correctamente',
            'data'    => $reserva,
        ]);
    }
}

