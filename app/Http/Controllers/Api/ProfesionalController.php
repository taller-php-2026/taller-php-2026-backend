<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfesionalRequest;
use App\Http\Requests\UpdateProfesionalRequest;
use App\Services\ProfesionalService;
use Illuminate\Http\Request;
use App\Models\Profesional;

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

    public function show(int $id)
    {
        $profesional = $this->profesionalService->getById((int) $id);

        return response()->json([
            'data' => $profesional,
        ]);
    }

    public function update(UpdateProfesionalRequest $request, int $id)
    {
        $profesional = $this->profesionalService->getById((int) $id);
        $profesional = $this->profesionalService->update($profesional, $request->validated());

        return response()->json([
            'message' => 'Profesional actualizado correctamente',
            'data'    => $profesional,
        ]);
    }
    public function updatePerfil(int $id, Request $request)
    {
        $profesional = Profesional::find($id);

        if (!$profesional) {
            return response()->json([
                'message' => 'Profesional no encontrado'
            ], 404);
        }

        $usuario = $profesional->usuario;

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // 🔹 actualizar usuario
        $usuario->update($request->only([
            'nombre',
            'email',
            'telefono'
        ]));

        // 🔹 actualizar profesional (incluye nombreNegocio)
        $profesional->update($request->only([
            'descripcion',
            'especialidad',
            'ubicacion',
            'nombreNegocio'
        ]));

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'usuario' => $usuario,
            'profesional' => $profesional
        ]);
    }
    public function destroy(int $id)
    {
        $profesional = $this->profesionalService->getById((int) $id);
        $this->profesionalService->delete($profesional);

        return response()->json([
            'message' => 'Profesional eliminado correctamente',
        ]);
    }
}

