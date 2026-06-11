<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgendaRequest;
use App\Http\Requests\UpdateAgendaRequest;
use App\Services\AgendaService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendaController extends Controller
{
    public function __construct(private AgendaService $agendaService) {}

    public function index(Request $request)
    {
        $user = $this->professionalOrAdmin($request);

        $agendas = $user->administrador
            ? $this->agendaService->getAll()
            : \App\Models\Agenda::with([
                'ciclo',
                'reglasDisponibilidad' => fn ($query) => $query->where('idProfesional', $user->idUsuario),
                'excepcionesDisponibilidad',
            ])
                ->whereHas('reglasDisponibilidad', fn ($query) => $query->where('idProfesional', $user->idUsuario))
                ->get();

        return response()->json([
            'data' => $agendas,
        ]);
    }

    public function store(StoreAgendaRequest $request)
    {
        $this->ensureOwnsCycle($request, (int) $request->validated()['idCiclo']);

        $agenda = $this->agendaService->create($request->validated());

        return response()->json([
            'message' => 'Agenda creada correctamente',
            'data'    => $agenda,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $agenda = $this->agendaService->getById((int) $id);
        $this->ensureOwnsAgenda($request, (int) $id);

        return response()->json([
            'data' => $agenda,
        ]);
    }

    public function update(UpdateAgendaRequest $request, $id)
    {
        $agenda = $this->agendaService->getById((int) $id);
        $this->ensureOwnsAgenda($request, (int) $id);

        $data = $request->validated();
        if (isset($data['idCiclo'])) {
            $this->ensureOwnsCycle($request, (int) $data['idCiclo']);
        }

        $agenda = $this->agendaService->update($agenda, $data);

        return response()->json([
            'message' => 'Agenda actualizada correctamente',
            'data'    => $agenda,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $agenda = $this->agendaService->getById((int) $id);
        $this->ensureOwnsAgenda($request, (int) $id);

        $this->agendaService->delete($agenda);

        return response()->json([
            'message' => 'Agenda eliminada correctamente',
        ]);
    }

    public function misAgendasProfesional(Request $request)
    {
        $user = $this->professionalOrAdmin($request, allowAdmin: false);

        $agendas = \App\Models\Agenda::with([
            'ciclo.rangoHorarios',
            'reglasDisponibilidad' => fn ($query) => $query->where('idProfesional', $user->idUsuario),
            'excepcionesDisponibilidad',
        ])
            ->whereHas('reglasDisponibilidad', fn ($query) => $query->where('idProfesional', $user->idUsuario))
            ->get();

        return response()->json([
            'message' => 'Agendas del profesional obtenidas correctamente',
            'data'    => $agendas,
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
            'message' => 'Solo profesionales o administradores pueden gestionar agendas.',
        ], 403));
    }

    private function ensureOwnsAgenda(Request $request, int $idAgenda): void
    {
        $user = $this->professionalOrAdmin($request);

        if ($user->administrador || $this->agendaHasNoOwner($idAgenda) || $this->agendaBelongsTo($idAgenda, (int) $user->idUsuario)) {
            return;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'No tenés permisos para modificar esta agenda.',
        ], 403));
    }

    private function ensureOwnsCycle(Request $request, int $idCiclo): void
    {
        $user = $this->professionalOrAdmin($request);

        if ($user->administrador || $this->cycleHasNoOwner($idCiclo) || in_array($idCiclo, $this->ownedCycleIds((int) $user->idUsuario), true)) {
            return;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'No tenés permisos para usar este ciclo.',
        ], 403));
    }

    private function agendaBelongsTo(int $idAgenda, int $idProfesional): bool
    {
        return DB::table('reglas_disponibilidad')
            ->where('idAgenda', $idAgenda)
            ->where('idProfesional', $idProfesional)
            ->exists();
    }

    private function agendaHasNoOwner(int $idAgenda): bool
    {
        return ! DB::table('reglas_disponibilidad')->where('idAgenda', $idAgenda)->exists();
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
