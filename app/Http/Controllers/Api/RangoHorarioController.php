<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRangoHorarioRequest;
use App\Http\Requests\UpdateRangoHorarioRequest;
use App\Services\RangoHorarioService;

class RangoHorarioController extends Controller
{
    public function __construct(private RangoHorarioService $rangoHorarioService) {}

    public function index()
    {
        $rangos = $this->rangoHorarioService->getAll();

        return response()->json([
            'data' => $rangos,
        ]);
    }

    public function store(StoreRangoHorarioRequest $request)
    {
        $rango = $this->rangoHorarioService->create($request->validated());

        return response()->json([
            'message' => 'Rango horario creado correctamente',
            'data'    => $rango,
        ], 201);
    }

    public function show($id)
    {
        $rango = $this->rangoHorarioService->getById((int) $id);

        return response()->json([
            'data' => $rango,
        ]);
    }

    public function update(UpdateRangoHorarioRequest $request, $id)
    {
        $rango = $this->rangoHorarioService->getById((int) $id);
        $rango = $this->rangoHorarioService->update($rango, $request->validated());

        return response()->json([
            'message' => 'Rango horario actualizado correctamente',
            'data'    => $rango,
        ]);
    }

    public function destroy($id)
    {
        $rango = $this->rangoHorarioService->getById((int) $id);
        $this->rangoHorarioService->delete($rango);

        return response()->json([
            'message' => 'Rango horario eliminado correctamente',
        ]);
    }
}

