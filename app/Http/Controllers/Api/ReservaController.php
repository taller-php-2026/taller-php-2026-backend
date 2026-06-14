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
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function __construct(
        private ReservaService $reservaService,
        private LiveKitService $liveKitService,
    ) {}

    public function index(\Illuminate\Http\Request $request)
    {
        $user = $request->user();
        $user?->loadMissing(['administrador', 'profesional', 'cliente']);

        $idProfesional = $request->query('idProfesional');

        if ($user?->administrador) {
            $query = \App\Models\Reserva::with(['cliente.usuario', 'profesional.usuario', 'servicio', 'pago', 'horario', 'paqueteComprado.paqueteServicio.servicio']);
            if ($idProfesional) {
                $query->where('idProfesional', $idProfesional);
            }
            $reservas = $query->get();
        } elseif ($user?->profesional) {
            if ($idProfesional && (int) $idProfesional !== (int) $user->idUsuario) {
                throw new HttpResponseException(response()->json([
                    'message' => 'No tenés permisos para consultar reservas de otro profesional.',
                ], 403));
            }

            $reservas = \App\Models\Reserva::with(['cliente.usuario', 'profesional.usuario', 'servicio', 'pago', 'horario', 'paqueteComprado.paqueteServicio.servicio'])
                ->where('idProfesional', $user->idUsuario)
                ->get();
        } elseif ($user?->cliente) {
            $reservas = $this->reservaService->getReservasByCliente((int) $user->idUsuario);
        } else {
            throw new HttpResponseException(response()->json([
                'message' => 'No tenés permisos para consultar reservas.',
            ], 403));
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

    public function show(Request $request, int $id)
    {
        $reserva = $this->reservaService->getById((int) $id);
        $this->reservaService->authorizeReservaAction($reserva, $request->user(), 'view');

        return response()->json([
            'data' => $reserva,
        ]);
    }

    public function misReservas(Request $request)
    {
        $user = $request->user();

        if (! $user->cliente) {
            return response()->json([
                'message' => 'Solo los clientes pueden consultar sus reservas.',
            ], 403);
        }

        $reservas = $this->reservaService->getReservasByCliente((int) $user->idUsuario);

        return response()->json([
            'message' => 'Reservas del cliente obtenidas correctamente',
            'data'    => $reservas,
        ]);
    }

    public function misReservasProfesional(Request $request)
    {
        $user = $request->user();
        $user?->loadMissing('profesional');

        if (! $user || ! $user->profesional) {
            return response()->json([
                'message' => 'Solo los profesionales pueden consultar sus reservas.',
            ], 403);
        }

        $reservas = \App\Models\Reserva::with(['cliente.usuario', 'profesional.usuario', 'servicio', 'pago', 'horario', 'paqueteComprado.paqueteServicio.servicio'])
            ->where('idProfesional', $user->idUsuario)
            ->orderBy('fechaReserva')
            ->get();

        return response()->json([
            'message' => 'Reservas del profesional obtenidas correctamente',
            'data'    => $reservas,
        ]);
    }

    public function update(UpdateReservaRequest $request, int $id)
    {
        $reserva = $this->reservaService->getById((int) $id);
        $this->reservaService->authorizeReservaAction($reserva, $request->user(), 'update');
        $reserva = $this->reservaService->update($reserva, $request->validated());

        return response()->json([
            'message' => 'Reserva actualizada correctamente',
            'data'    => $reserva,
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $reserva = $this->reservaService->getById((int) $id);
        $this->reservaService->authorizeReservaAction($reserva, $request->user(), 'delete');
        $this->reservaService->delete($reserva);

        return response()->json([
            'message' => 'Reserva eliminada correctamente',
        ]);
    }

    public function reprogramar(ReprogramarReservaRequest $request, int $id)
    {
        $reserva = $this->reservaService->getById((int) $id);
        $this->reservaService->authorizeReservaAction($reserva, $request->user(), 'reprogram');

        $data = $this->reservaService->reprogramar((int) $id, $request->validated());

        return response()->json([
            'message' => 'Reserva reprogramada correctamente',
            'data'    => $data,
        ]);
    }

    public function cancelar(Request $request, int $id)
    {
        $data = $this->reservaService->cancelar((int) $id, $request->user());

        return response()->json([
            'message' => 'Reserva cancelada correctamente',
            'data'    => $data,
        ]);
    }

    public function pagar(PagarReservaRequest $request, int $id)
    {
        $reservaActual = $this->reservaService->getById((int) $id);
        $this->reservaService->authorizeReservaAction($reservaActual, $request->user(), 'pay');

        $reserva = $this->reservaService->pagar((int) $id, $request->validated());

        return response()->json([
            'message' => 'Pago procesado correctamente',
            'data'    => $reserva,
        ]);
    }

    public function completar(Request $request, int $id)
    {
        $reservaActual = $this->reservaService->getById((int) $id);
        $this->reservaService->authorizeReservaAction($reservaActual, $request->user(), 'complete');

        $reserva = $this->reservaService->completar((int) $id);

        return response()->json([
            'message' => 'Reserva completada correctamente',
            'data'    => $reserva,
        ]);
    }

    public function resena(StoreResenaRequest $request, int $id)
    {
        $reserva = $this->reservaService->getById((int) $id);
        $this->reservaService->authorizeReservaAction($reserva, $request->user(), 'review');
        $this->reservaService->assertReviewable($reserva);

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
