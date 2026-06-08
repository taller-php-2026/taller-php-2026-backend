<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaqueteServicioRequest;
use App\Http\Requests\UpdatePaqueteServicioRequest;
use App\Services\CloudinaryService;
use App\Services\PaqueteServicioService;
use Illuminate\Http\Request;

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
        $paquete = $this->paqueteServicioService->create($request->validated());

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
        $paquete = $this->paqueteServicioService->update($paquete, $request->validated());

        return response()->json([
            'message' => 'Paquete de servicio actualizado correctamente',
            'data'    => $paquete,
        ]);
    }

    public function destroy($id)
    {
        $paquete = $this->paqueteServicioService->getById((int) $id);
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
}
