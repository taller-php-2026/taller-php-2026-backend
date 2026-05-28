<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DisponibilidadProfesionalRequest;
use App\Services\DisponibilidadService;

class DisponibilidadController extends Controller
{
    public function __construct(private DisponibilidadService $disponibilidadService) {}

    public function porProfesional(DisponibilidadProfesionalRequest $request, $id)
    {
        $validated = $request->validated();

        $data = $this->disponibilidadService->getDisponibilidad(
            (int) $id,
            $validated['fecha'],
            (int) $validated['idServicio']
        );

        return response()->json([
            'message' => 'Disponibilidad consultada correctamente',
            'data'    => $data,
        ]);
    }
}
