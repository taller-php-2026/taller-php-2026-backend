<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCicloRequest;
use App\Http\Requests\UpdateCicloRequest;
use App\Services\CicloService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CicloController extends Controller
{
    public function __construct(private CicloService $cicloService) {}

    public function index(Request $request)
    {
        $user = $this->professionalOrAdmin($request);

        $ciclos = $user->administrador
            ? $this->cicloService->getAll()
            : \App\Models\Ciclo::with('rangoHorarios')
                ->whereIn('idCiclo', $this->ownedCycleIds((int) $user->idUsuario))
                ->get();

        return response()->json([
            'data' => $ciclos,
        ]);
    }

    public function store(StoreCicloRequest $request)
    {
        $this->professionalOrAdmin($request);

        $ciclo = $this->cicloService->create($request->validated());

        return response()->json([
            'message' => 'Ciclo creado correctamente',
            'data'    => $ciclo,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $ciclo = $this->cicloService->getById((int) $id);
        $this->ensureOwnsCycle($request, (int) $id);

        return response()->json([
            'data' => $ciclo,
        ]);
    }

    public function update(UpdateCicloRequest $request, $id)
    {
        $ciclo = $this->cicloService->getById((int) $id);
        $this->ensureOwnsCycle($request, (int) $id);

        $ciclo = $this->cicloService->update($ciclo, $request->validated());

        return response()->json([
            'message' => 'Ciclo actualizado correctamente',
            'data'    => $ciclo,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $ciclo = $this->cicloService->getById((int) $id);
        $this->ensureOwnsCycle($request, (int) $id);

        $this->cicloService->delete($ciclo);

        return response()->json([
            'message' => 'Ciclo eliminado correctamente',
        ]);
    }

    private function professionalOrAdmin(Request $request): \App\Models\Usuario
    {
        $user = $request->user();
        $user?->loadMissing(['profesional', 'administrador']);

        if ($user && ($user->profesional || $user->administrador)) {
            return $user;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'Solo profesionales o administradores pueden gestionar ciclos.',
        ], 403));
    }

    private function ensureOwnsCycle(Request $request, int $idCiclo): void
    {
        $user = $this->professionalOrAdmin($request);

        if ($user->administrador) {
            return;
        }

        if ($this->cycleHasNoOwner($idCiclo) || in_array($idCiclo, $this->ownedCycleIds((int) $user->idUsuario), true)) {
            return;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'No tenés permisos para modificar este ciclo.',
        ], 403));
    }

    private function ownedCycleIds(int $idProfesional): array
    {
        return DB::table('agendas')
            ->join('reglas_disponibilidad', 'agendas.idAgenda', '=', 'reglas_disponibilidad.idAgenda')
            ->where('reglas_disponibilidad.idProfesional', $idProfesional)
            ->pluck('agendas.idCiclo')
            ->unique()
            ->values()
            ->all();
    }

    private function cycleHasNoOwner(int $idCiclo): bool
    {
        return ! DB::table('agendas')
            ->join('reglas_disponibilidad', 'agendas.idAgenda', '=', 'reglas_disponibilidad.idAgenda')
            ->where('agendas.idCiclo', $idCiclo)
            ->exists();
    }
}
