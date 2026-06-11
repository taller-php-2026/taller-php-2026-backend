<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuscarServiciosRequest;
use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use App\Services\CloudinaryService;
use App\Services\ServicioService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServicioController extends Controller
{
    public function __construct(
        private ServicioService $servicioService,
        private CloudinaryService $cloudinaryService,
    ) {}

    public function index()
    {
        $servicios = $this->servicioService->getAll();

        return response()->json([
            'data' => $servicios,
        ]);
    }

    public function store(StoreServicioRequest $request)
    {
        $user = $this->professionalOrAdmin($request);
        $validated = $request->validated();
        $validated['idProfesional'] = $this->resolveProfessionalId($user, $validated);

        $servicio = $this->servicioService->create($validated);
        
        // Asociar profesional al servicio creado
        $servicio->profesionales()->attach($validated['idProfesional']);

        return response()->json([
            'message' => 'Servicio creado correctamente',
            'data'    => $servicio->load('profesionales'),
        ], 201);
    }

    public function show($id)
    {
        $servicio = $this->servicioService->getById((int) $id);

        return response()->json([
            'data' => $servicio,
        ]);
    }

    public function update(UpdateServicioRequest $request, $id)
    {
        $servicio = $this->servicioService->getById((int) $id);
        $this->ensureOwnsServicio($request, $servicio);

        $servicio = $this->servicioService->update($servicio, $request->validated());

        return response()->json([
            'message' => 'Servicio actualizado correctamente',
            'data'    => $servicio,
        ]);
    }

    public function destroy($id)
    {
        $servicio = $this->servicioService->getById((int) $id);
        $this->ensureOwnsServicio(request(), $servicio);

        $this->servicioService->delete($servicio);

        return response()->json([
            'message' => 'Servicio eliminado correctamente',
        ]);
    }

    public function buscar(BuscarServiciosRequest $request)
    {
        $paginator = $this->servicioService->buscar($request->validated());

        return response()->json([
            'message' => 'Servicios filtrados correctamente',
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    // obtener profesionales que ofrecen un servicio específico
    public function profesionales($id)
    {
        $profesionales = $this->servicioService->getProfesionalesByServicioId((int) $id);

        return response()->json([
            'data' => $profesionales,
        ]);
    }

    public function subirImagen(Request $request, $id)
    {
        $request->validate([
            'imagen' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $servicio = $this->servicioService->getById((int) $id);
        $this->ensureOwnsServicio($request, $servicio);

        $this->cloudinaryService->eliminarImagen($servicio->imagenPublicId);

        $resultado = $this->cloudinaryService->subirImagen($request->file('imagen'), 'taller-php/servicios');

        $servicio->update([
            'imagenUrl'      => $resultado['url'],
            'imagenPublicId' => $resultado['public_id'],
        ]);

        return response()->json([
            'message' => 'Imagen del servicio subida correctamente',
            'data'    => $servicio,
        ]);
    }

    public function misServiciosProfesional(Request $request)
    {
        $user = $this->professionalOrAdmin($request, allowAdminWithoutProfessional: false);

        $servicios = \App\Models\Servicio::query()
            ->with(['paqueteServicio', 'ubicacion', 'videoSesion'])
            ->whereHas('profesionales', fn (Builder $query) => $query->where('profesionales.idUsuario', $user->idUsuario))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Servicios del profesional obtenidos correctamente',
            'data'    => $servicios,
        ]);
    }

    private function professionalOrAdmin(Request $request, bool $allowAdminWithoutProfessional = true): \App\Models\Usuario
    {
        $user = $request->user();
        $user?->loadMissing(['profesional', 'administrador']);

        if ($user && $user->profesional) {
            return $user;
        }

        if ($allowAdminWithoutProfessional && $user && $user->administrador) {
            return $user;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'Solo los profesionales pueden realizar esta acción.',
        ], 403));
    }

    private function resolveProfessionalId(\App\Models\Usuario $user, array $data): int
    {
        if ($user->profesional) {
            return (int) $user->idUsuario;
        }

        if ($user->administrador && !empty($data['idProfesional'])) {
            return (int) $data['idProfesional'];
        }

        throw ValidationException::withMessages([
            'idProfesional' => ['El administrador debe indicar el profesional del servicio.'],
        ]);
    }

    private function ensureOwnsServicio(Request $request, \App\Models\Servicio $servicio): void
    {
        $user = $this->professionalOrAdmin($request);

        if ($user->administrador) {
            return;
        }

        $owns = $servicio->profesionales()
            ->where('profesionales.idUsuario', $user->idUsuario)
            ->exists();

        if (! $owns) {
            throw new HttpResponseException(response()->json([
                'message' => 'No tenés permisos para modificar este servicio.',
            ], 403));
        }
    }
}
