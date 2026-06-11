<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuscarServiciosRequest;
use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use App\Services\CloudinaryService;
use App\Services\ServicioService;
use Illuminate\Http\Request;

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
        $validated = $request->validated();
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
        $servicio = $this->servicioService->update($servicio, $request->validated());

        return response()->json([
            'message' => 'Servicio actualizado correctamente',
            'data'    => $servicio,
        ]);
    }

    public function destroy($id)
    {
        $servicio = $this->servicioService->getById((int) $id);
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
}
