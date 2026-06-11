<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExcepcionDisponibilidadRequest;
use App\Http\Requests\UpdateExcepcionDisponibilidadRequest;
use App\Services\ExcepcionDisponibilidadService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExcepcionDisponibilidadController extends Controller
{
    public function __construct(private ExcepcionDisponibilidadService $excepcionService) {}

    public function index(Request $request)
    {
        $user = $this->professionalOrAdmin($request);

        $excepciones = $user->administrador
            ? $this->excepcionService->getAll()
            : \App\Models\ExcepcionDisponibilidad::with('agenda')
                ->whereIn('idAgenda', $this->ownedAgendaIds((int) $user->idUsuario))
                ->get();

        return response()->json([
            'data' => $excepciones,
        ]);
    }

    public function store(StoreExcepcionDisponibilidadRequest $request)
    {
        $this->ensureOwnsAgenda($request, (int) $request->validated()['idAgenda']);

        $excepcion = $this->excepcionService->create($request->validated());

        return response()->json([
            'message' => 'Excepción de disponibilidad creada correctamente',
            'data'    => $excepcion,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $excepcion = $this->excepcionService->getById((int) $id);
        $this->ensureOwnsExcepcion($request, $excepcion);

        return response()->json([
            'data' => $excepcion,
        ]);
    }

    public function update(UpdateExcepcionDisponibilidadRequest $request, $id)
    {
        $excepcion = $this->excepcionService->getById((int) $id);
        $this->ensureOwnsExcepcion($request, $excepcion);

        $data = $request->validated();
        if (isset($data['idAgenda'])) {
            $this->ensureOwnsAgenda($request, (int) $data['idAgenda']);
        }

        $excepcion = $this->excepcionService->update($excepcion, $data);

        return response()->json([
            'message' => 'Excepción de disponibilidad actualizada correctamente',
            'data'    => $excepcion,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $excepcion = $this->excepcionService->getById((int) $id);
        $this->ensureOwnsExcepcion($request, $excepcion);

        $this->excepcionService->delete($excepcion);

        return response()->json([
            'message' => 'Excepción de disponibilidad eliminada correctamente',
        ]);
    }

    public function misExcepcionesProfesional(Request $request)
    {
        $user = $this->professionalOrAdmin($request, allowAdmin: false);

        $excepciones = \App\Models\ExcepcionDisponibilidad::with('agenda.ciclo')
            ->whereIn('idAgenda', $this->ownedAgendaIds((int) $user->idUsuario))
            ->orderBy('fecha')
            ->orderBy('horaInicio')
            ->get();

        return response()->json([
            'message' => 'Excepciones del profesional obtenidas correctamente',
            'data'    => $excepciones,
        ]);
    }

    private function professionalOrAdmin(Request $request, bool $allowAdmin = true): \App\Models\Usuario
    {
        $user = $request->user();
        $user?->loadMissing(['profesional', 'administrador']);

        if ($user && $user->profesional) {
            return $user;
        }

        if ($allowAdmin && $user && $user->administrador) {
            return $user;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'Solo profesionales o administradores pueden gestionar excepciones.',
        ], 403));
    }

    private function ensureOwnsExcepcion(Request $request, \App\Models\ExcepcionDisponibilidad $excepcion): void
    {
        $this->ensureOwnsAgenda($request, (int) $excepcion->idAgenda);
    }

    private function ensureOwnsAgenda(Request $request, int $idAgenda): void
    {
        $user = $this->professionalOrAdmin($request);

        if ($user->administrador || in_array($idAgenda, $this->ownedAgendaIds((int) $user->idUsuario), true)) {
            return;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'No tenés permisos para modificar esta excepción.',
        ], 403));
    }

    private function ownedAgendaIds(int $idProfesional): array
    {
        return DB::table('reglas_disponibilidad')
            ->where('idProfesional', $idProfesional)
            ->pluck('idAgenda')
            ->unique()
            ->values()
            ->all();
    }
}
