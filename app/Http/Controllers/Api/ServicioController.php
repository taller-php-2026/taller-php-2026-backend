<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use App\Services\ServicioService;

class ServicioController extends Controller
{
    public function __construct(private ServicioService $servicioService) {}

    public function index()
    {
        $servicios = $this->servicioService->getAll();

        return response()->json([
            'data' => $servicios,
        ]);
    }

    public function store(StoreServicioRequest $request)
    {
        $servicio = $this->servicioService->create($request->validated());

        return response()->json([
            'message' => 'Servicio creado correctamente',
            'data'    => $servicio,
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
}

