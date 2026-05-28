<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReglaDisponibilidadRequest;
use App\Http\Requests\UpdateReglaDisponibilidadRequest;
use App\Services\ReglaDisponibilidadService;

class ReglaDisponibilidadController extends Controller
{
    public function __construct(private ReglaDisponibilidadService $reglaService) {}

    public function index()
    {
        $reglas = $this->reglaService->getAll();

        return response()->json([
            'data' => $reglas,
        ]);
    }

    public function store(StoreReglaDisponibilidadRequest $request)
    {
        $regla = $this->reglaService->create($request->validated());

        return response()->json([
            'message' => 'Regla de disponibilidad creada correctamente',
            'data'    => $regla,
        ], 201);
    }

    public function show($id)
    {
        $regla = $this->reglaService->getById((int) $id);

        return response()->json([
            'data' => $regla,
        ]);
    }

    public function update(UpdateReglaDisponibilidadRequest $request, $id)
    {
        $regla = $this->reglaService->getById((int) $id);
        $regla = $this->reglaService->update($regla, $request->validated());

        return response()->json([
            'message' => 'Regla de disponibilidad actualizada correctamente',
            'data'    => $regla,
        ]);
    }

    public function destroy($id)
    {
        $regla = $this->reglaService->getById((int) $id);
        $this->reglaService->delete($regla);

        return response()->json([
            'message' => 'Regla de disponibilidad eliminada correctamente',
        ]);
    }
}

