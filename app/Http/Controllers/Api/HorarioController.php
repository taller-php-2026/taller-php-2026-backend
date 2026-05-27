<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHorarioRequest;
use App\Http\Requests\UpdateHorarioRequest;
use App\Services\HorarioService;

class HorarioController extends Controller
{
    public function __construct(private HorarioService $horarioService) {}

    public function index()
    {
        $horarios = $this->horarioService->getAll();

        return response()->json([
            'data' => $horarios,
        ]);
    }

    public function store(StoreHorarioRequest $request)
    {
        $horario = $this->horarioService->create($request->validated());

        return response()->json([
            'message' => 'Horario creado correctamente',
            'data'    => $horario,
        ], 201);
    }

    public function show($id)
    {
        $horario = $this->horarioService->getById((int) $id);

        return response()->json([
            'data' => $horario,
        ]);
    }

    public function update(UpdateHorarioRequest $request, $id)
    {
        $horario = $this->horarioService->getById((int) $id);
        $horario = $this->horarioService->update($horario, $request->validated());

        return response()->json([
            'message' => 'Horario actualizado correctamente',
            'data'    => $horario,
        ]);
    }

    public function destroy($id)
    {
        $horario = $this->horarioService->getById((int) $id);
        $this->horarioService->delete($horario);

        return response()->json([
            'message' => 'Horario eliminado correctamente',
        ]);
    }
}

