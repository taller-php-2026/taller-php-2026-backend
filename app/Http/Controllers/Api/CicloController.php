<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCicloRequest;
use App\Http\Requests\UpdateCicloRequest;
use App\Services\CicloService;

class CicloController extends Controller
{
    public function __construct(private CicloService $cicloService) {}

    public function index()
    {
        $ciclos = $this->cicloService->getAll();

        return response()->json([
            'data' => $ciclos,
        ]);
    }

    public function store(StoreCicloRequest $request)
    {
        $ciclo = $this->cicloService->create($request->validated());

        return response()->json([
            'message' => 'Ciclo creado correctamente',
            'data'    => $ciclo,
        ], 201);
    }

    public function show($id)
    {
        $ciclo = $this->cicloService->getById((int) $id);

        return response()->json([
            'data' => $ciclo,
        ]);
    }

    public function update(UpdateCicloRequest $request, $id)
    {
        $ciclo = $this->cicloService->getById((int) $id);
        $ciclo = $this->cicloService->update($ciclo, $request->validated());

        return response()->json([
            'message' => 'Ciclo actualizado correctamente',
            'data'    => $ciclo,
        ]);
    }

    public function destroy($id)
    {
        $ciclo = $this->cicloService->getById((int) $id);
        $this->cicloService->delete($ciclo);

        return response()->json([
            'message' => 'Ciclo eliminado correctamente',
        ]);
    }
}

