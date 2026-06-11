<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaqueteServicioRequest;
use App\Http\Requests\UpdatePaqueteServicioRequest;
use App\Services\CloudinaryService;
use App\Services\PaqueteServicioService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaqueteServicioController extends Controller
{
    public function __construct(
        private PaqueteServicioService $paqueteServicioService,
        private CloudinaryService $cloudinaryService,
    ) {}

    public function index()
    {
        $paquetes = $this->paqueteServicioService->getAll();

        return response()->json([
            'data' => $paquetes,
        ]);
    }

    public function store(StorePaqueteServicioRequest $request)
    {
        $user = $this->professionalOrAdmin($request);
        $validated = $request->validated();
        $validated['idProfesional'] = $this->resolveProfessionalId($user, $validated);

        $this->ensureOwnsBaseServices($user, $validated['servicios_ids']);

        $paquete = $this->paqueteServicioService->create($validated);

        return response()->json([
            'message' => 'Paquete de servicio creado correctamente',
            'data'    => $paquete,
        ], 201);
    }

    public function show($id)
    {
        $paquete = $this->paqueteServicioService->getById((int) $id);

        return response()->json([
            'data' => $paquete,
        ]);
    }

    public function update(UpdatePaqueteServicioRequest $request, $id)
    {
        $paquete = $this->paqueteServicioService->getById((int) $id);
        $this->ensureOwnsPaquete($request, $paquete);

        $paquete = $this->paqueteServicioService->update($paquete, $request->validated());

        return response()->json([
            'message' => 'Paquete de servicio actualizado correctamente',
            'data'    => $paquete,
        ]);
    }

    public function destroy($id)
    {
        $paquete = $this->paqueteServicioService->getById((int) $id);
        $this->ensureOwnsPaquete(request(), $paquete);

        $this->paqueteServicioService->delete($paquete);

        return response()->json([
            'message' => 'Paquete de servicio eliminado correctamente',
        ]);
    }

    public function subirImagen(Request $request, $id)
    {
        $request->validate([
            'imagen' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $paquete = $this->paqueteServicioService->getById((int) $id);
        $this->ensureOwnsPaquete($request, $paquete);

        $this->cloudinaryService->eliminarImagen($paquete->imagenPublicId);

        $resultado = $this->cloudinaryService->subirImagen($request->file('imagen'), 'taller-php/paquetes');

        $paquete->update([
            'imagenUrl'      => $resultado['url'],
            'imagenPublicId' => $resultado['public_id'],
        ]);

        return response()->json([
            'message' => 'Imagen del paquete subida correctamente',
            'data'    => $paquete,
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
            'message' => 'Solo profesionales o administradores pueden gestionar paquetes.',
        ], 403));
    }

    private function resolveProfessionalId(\App\Models\Usuario $user, array $data): int
    {
        if ($user->profesional) {
            return (int) $user->idUsuario;
        }

        if (!empty($data['idProfesional'])) {
            return (int) $data['idProfesional'];
        }

        throw ValidationException::withMessages([
            'idProfesional' => ['El administrador debe indicar el profesional del paquete.'],
        ]);
    }

    private function ensureOwnsBaseServices(\App\Models\Usuario $user, array $serviceIds): void
    {
        if ($user->administrador) {
            return;
        }

        $ownedCount = DB::table('profesionales_servicios')
            ->where('idProfesional', $user->idUsuario)
            ->whereIn('idServicio', $serviceIds)
            ->distinct()
            ->count('idServicio');

        if ($ownedCount !== count(array_unique($serviceIds))) {
            throw new HttpResponseException(response()->json([
                'message' => 'Solo podés crear paquetes con servicios propios.',
            ], 403));
        }
    }

    private function ensureOwnsPaquete(Request $request, \App\Models\PaqueteServicio $paquete): void
    {
        $user = $this->professionalOrAdmin($request);

        if ($user->administrador) {
            return;
        }

        $owns = $paquete->servicio
            ? $paquete->servicio->profesionales()
                ->where('profesionales.idUsuario', $user->idUsuario)
                ->exists()
            : false;

        if (! $owns) {
            throw new HttpResponseException(response()->json([
                'message' => 'No tenés permisos para modificar este paquete.',
            ], 403));
        }
    }
}
