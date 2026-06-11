<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PagarReservaRequest;
use App\Http\Requests\ReprogramarReservaRequest;
use App\Http\Requests\StoreResenaRequest;
use App\Http\Requests\StoreReservaRequest;
use App\Http\Requests\UpdateReservaRequest;
use App\Http\Requests\VideoTokenRequest;
use App\Services\LiveKitService;
use App\Services\ReservaService;

class ReservaController extends Controller
{
    public function __construct(
        private ReservaService $reservaService,
        private LiveKitService $liveKitService,
    ) {}

    public function index(\Illuminate\Http\Request $request)
    {
        $idProfesional = $request->query('idProfesional');
        if ($idProfesional) {
            $reservas = \App\Models\Reserva::with(['cliente.usuario', 'profesional.usuario', 'servicio', 'pago', 'horario'])
                ->where('idProfesional', $idProfesional)
                ->get();
        } else {
            $reservas = $this->reservaService->getAll();
        }

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

    public function show(int $id)
    {
        $reserva = $this->reservaService->getById((int) $id);

        return response()->json([
            'data' => $reserva,
        ]);
    }

    public function update(UpdateReservaRequest $request, int $id)
    {
        $reserva = $this->reservaService->getById((int) $id);
        $reserva = $this->reservaService->update($reserva, $request->validated());

        return response()->json([
            'message' => 'Reserva actualizada correctamente',
            'data'    => $reserva,
        ]);
    }

    public function destroy(int $id)
    {
        $reserva = $this->reservaService->getById((int) $id);
        $this->reservaService->delete($reserva);

        return response()->json([
            'message' => 'Reserva eliminada correctamente',
        ]);
    }

    public function reprogramar(ReprogramarReservaRequest $request, int $id)
    {
        $data = $this->reservaService->reprogramar((int) $id, $request->validated());

        return response()->json([
            'message' => 'Reserva reprogramada correctamente',
            'data'    => $data,
        ]);
    }

    public function cancelar(int $id)
    {
        $data = $this->reservaService->cancelar((int) $id);

        return response()->json([
            'message' => 'Reserva cancelada correctamente',
            'data'    => $data,
        ]);
    }

    public function pagar(PagarReservaRequest $request, int $id)
    {
        $reserva = $this->reservaService->pagar((int) $id, $request->validated());

        return response()->json([
            'message' => 'Pago procesado correctamente',
            'data'    => $reserva,
        ]);
    }

    public function completar(int $id)
    {
        $reserva = $this->reservaService->completar((int) $id);

        return response()->json([
            'message' => 'Reserva completada correctamente',
            'data'    => $reserva,
        ]);
    }

    public function resena(StoreResenaRequest $request, int $id)
    {
        $result = $this->reservaService->resena((int) $id, $request->validated());

        return response()->json([
            'message' => 'Reseña creada correctamente',
            'data'    => $result,
        ], 201);
    }

    public function cancelarVencidas()
    {
        $result = $this->reservaService->cancelarVencidas();

        return response()->json([
            'message' => 'Reservas vencidas canceladas correctamente',
            'data'    => $result,
        ]);
    }

    public function videoToken(VideoTokenRequest $request, int $id)
    {
        $idUsuario = (int) $request->user()->idUsuario;

        $data = $this->liveKitService->generarTokenParaReserva(
            (int) $id,
            $idUsuario
        );

        return response()->json([
            'message' => 'Token de videollamada generado correctamente',
            'data'    => $data,
        ]);
    }
}

