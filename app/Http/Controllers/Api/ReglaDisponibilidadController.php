<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReglaDisponibilidadRequest;
use App\Http\Requests\UpdateReglaDisponibilidadRequest;
use App\Services\ReglaDisponibilidadService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReglaDisponibilidadController extends Controller
{
    public function __construct(private ReglaDisponibilidadService $reglaService) {}

    public function index(Request $request)
    {
        $user = $this->professionalOrAdmin($request);

        $reglas = $user->administrador
            ? $this->reglaService->getAll()
            : \App\Models\ReglaDisponibilidad::with(['agenda', 'profesional'])
                ->where('idProfesional', $user->idUsuario)
                ->get();

        return response()->json([
            'data' => $reglas,
        ]);
    }

    public function store(StoreReglaDisponibilidadRequest $request)
    {
        $user = $this->professionalOrAdmin($request);
        $data = $request->validated();

        if ($user->profesional) {
            $data['idProfesional'] = (int) $user->idUsuario;
        } elseif (empty($data['idProfesional'])) {
            throw ValidationException::withMessages([
                'idProfesional' => ['El administrador debe indicar el profesional de la regla.'],
            ]);
        }

        $this->ensureOwnsAgenda($request, (int) $data['idAgenda']);

        $regla = $this->reglaService->create($data);

        return response()->json([
            'message' => 'Regla de disponibilidad creada correctamente',
            'data'    => $regla,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $regla = $this->reglaService->getById((int) $id);
        $this->ensureOwnsRegla($request, $regla);

        return response()->json([
            'data' => $regla,
        ]);
    }

    public function update(UpdateReglaDisponibilidadRequest $request, $id)
    {
        $regla = $this->reglaService->getById((int) $id);
        $this->ensureOwnsRegla($request, $regla);

        $data = $request->validated();
        if (isset($data['idAgenda'])) {
            $this->ensureOwnsAgenda($request, (int) $data['idAgenda']);
        }

        if ($request->user()->profesional) {
            unset($data['idProfesional']);
        }

        $regla = $this->reglaService->update($regla, $data);

        return response()->json([
            'message' => 'Regla de disponibilidad actualizada correctamente',
            'data'    => $regla,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $regla = $this->reglaService->getById((int) $id);
        $this->ensureOwnsRegla($request, $regla);

        $this->reglaService->delete($regla);

        return response()->json([
            'message' => 'Regla de disponibilidad eliminada correctamente',
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
            'message' => 'Solo profesionales o administradores pueden gestionar reglas de disponibilidad.',
        ], 403));
    }

    private function ensureOwnsRegla(Request $request, \App\Models\ReglaDisponibilidad $regla): void
    {
        $user = $this->professionalOrAdmin($request);

        if ($user->administrador || (int) $regla->idProfesional === (int) $user->idUsuario) {
            return;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'No tenés permisos para modificar esta regla.',
        ], 403));
    }

    private function ensureOwnsAgenda(Request $request, int $idAgenda): void
    {
        $user = $this->professionalOrAdmin($request);

        if ($user->administrador || $this->agendaHasNoOwner($idAgenda) || $this->agendaBelongsTo($idAgenda, (int) $user->idUsuario)) {
            return;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'No tenés permisos para usar esta agenda.',
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
}
