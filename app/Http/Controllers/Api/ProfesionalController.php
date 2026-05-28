<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfesionalRequest;
use App\Http\Requests\UpdateProfesionalRequest;
use App\Services\ProfesionalService;

class ProfesionalController extends Controller
{
    public function __construct(private ProfesionalService $profesionalService) {}

    public function index()
    {
        $profesionales = $this->profesionalService->getAll();

        return response()->json([
            'data' => $profesionales,
        ]);
    }

    public function store(StoreProfesionalRequest $request)
    {
        $profesional = $this->profesionalService->create($request->validated());

        return response()->json([
            'message' => 'Profesional creado correctamente',
            'data'    => $profesional,
        ], 201);
    }

    public function show($id)
    {
        $profesional = $this->profesionalService->getById((int) $id);

        return response()->json([
            'data' => $profesional,
        ]);
    }

    public function update(UpdateProfesionalRequest $request, $id)
    {
        $profesional = $this->profesionalService->getById((int) $id);
        $profesional = $this->profesionalService->update($profesional, $request->validated());

        return response()->json([
            'message' => 'Profesional actualizado correctamente',
            'data'    => $profesional,
        ]);
    }

    public function destroy($id)
    {
        $profesional = $this->profesionalService->getById((int) $id);
        $this->profesionalService->delete($profesional);

        return response()->json([
            'message' => 'Profesional eliminado correctamente',
        ]);
    }
}

