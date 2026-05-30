<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaqueteServicioRequest;
use App\Http\Requests\UpdatePaqueteServicioRequest;
use App\Services\PaqueteServicioService;

class PaqueteServicioController extends Controller
{
    public function __construct(private PaqueteServicioService $paqueteServicioService) {}

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
}
