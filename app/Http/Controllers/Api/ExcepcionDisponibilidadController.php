<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExcepcionDisponibilidadRequest;
use App\Http\Requests\UpdateExcepcionDisponibilidadRequest;
use App\Services\ExcepcionDisponibilidadService;

class ExcepcionDisponibilidadController extends Controller
{
    public function __construct(private ExcepcionDisponibilidadService $excepcionService) {}

    public function index()
    {
        $excepciones = $this->excepcionService->getAll();

        return response()->json([
            'data' => $excepciones,
        ]);
    }

    public function store(StoreExcepcionDisponibilidadRequest $request)
    {
        $excepcion = $this->excepcionService->create($request->validated());

        return response()->json([
            'message' => 'Excepción de disponibilidad creada correctamente',
            'data'    => $excepcion,
        ], 201);
    }

    public function show($id)
    {
        $excepcion = $this->excepcionService->getById((int) $id);

        return response()->json([
            'data' => $excepcion,
        ]);
    }

    public function update(UpdateExcepcionDisponibilidadRequest $request, $id)
    {
        $excepcion = $this->excepcionService->getById((int) $id);
        $excepcion = $this->excepcionService->update($excepcion, $request->validated());

        return response()->json([
            'message' => 'Excepción de disponibilidad actualizada correctamente',
            'data'    => $excepcion,
        ]);
    }

    public function destroy($id)
    {
        $excepcion = $this->excepcionService->getById((int) $id);
        $this->excepcionService->delete($excepcion);

        return response()->json([
            'message' => 'Excepción de disponibilidad eliminada correctamente',
        ]);
    }
}

