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
        $data = $this->disponibilidadService->getDisponibilidad(
            (int) $id,
            $request->validated()['fecha']
        );

        return response()->json([
            'message' => 'Disponibilidad consultada correctamente',
            'data'    => $data,
        ]);
    }
}
