<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRangoHorarioRequest;
use App\Http\Requests\UpdateRangoHorarioRequest;
use App\Services\RangoHorarioService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RangoHorarioController extends Controller
{
    public function __construct(private RangoHorarioService $rangoHorarioService) {}

    public function index(Request $request)
    {
        $user = $this->professionalOrAdmin($request);

        $rangos = $user->administrador
            ? $this->rangoHorarioService->getAll()
            : \App\Models\RangoHorario::with('ciclo')
                ->whereIn('idCiclo', $this->ownedCycleIds((int) $user->idUsuario))
                ->get();

        return response()->json([
            'data' => $rangos,
        ]);
    }

    public function store(StoreRangoHorarioRequest $request)
    {
        $this->ensureOwnsCycle($request, (int) $request->validated()['idCiclo']);

        $rango = $this->rangoHorarioService->create($request->validated());

        return response()->json([
            'message' => 'Rango horario creado correctamente',
            'data'    => $rango,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $rango = $this->rangoHorarioService->getById((int) $id);
        $this->ensureOwnsCycle($request, (int) $rango->idCiclo);

        return response()->json([
            'data' => $rango,
        ]);
    }

    public function update(UpdateRangoHorarioRequest $request, $id)
    {
        $rango = $this->rangoHorarioService->getById((int) $id);
        $this->ensureOwnsCycle($request, (int) $rango->idCiclo);

        $data = $request->validated();
        if (isset($data['idCiclo'])) {
            $this->ensureOwnsCycle($request, (int) $data['idCiclo']);
        }

        $rango = $this->rangoHorarioService->update($rango, $data);

        return response()->json([
            'message' => 'Rango horario actualizado correctamente',
            'data'    => $rango,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $rango = $this->rangoHorarioService->getById((int) $id);
        $this->ensureOwnsCycle($request, (int) $rango->idCiclo);

        $this->rangoHorarioService->delete($rango);

        return response()->json([
            'message' => 'Rango horario eliminado correctamente',
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
            'message' => 'Solo profesionales o administradores pueden gestionar rangos horarios.',
        ], 403));
    }

    private function ensureOwnsCycle(Request $request, int $idCiclo): void
    {
        $user = $this->professionalOrAdmin($request);

        if ($user->administrador || $this->cycleHasNoOwner($idCiclo) || in_array($idCiclo, $this->ownedCycleIds((int) $user->idUsuario), true)) {
            return;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'No tenés permisos para modificar este rango horario.',
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
