<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Services\UsuarioService;

class UsuarioController extends Controller
{
    public function __construct(private UsuarioService $usuarioService) {}

    public function index()
    {
        $usuarios = $this->usuarioService->getAll();

        return response()->json([
            'data' => $usuarios,
        ]);
    }

    public function store(StoreUsuarioRequest $request)
    {
        $usuario = $this->usuarioService->create($request->validated());

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'data'    => $usuario,
        ], 201);
    }

    public function show($id)
    {
        $usuario = $this->usuarioService->getById((int) $id);

        return response()->json([
            'data' => $usuario,
        ]);
    }

    public function update(UpdateUsuarioRequest $request, $id)
    {
        $usuario = $this->usuarioService->getById((int) $id);
        $usuario = $this->usuarioService->update($usuario, $request->validated());

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data'    => $usuario,
        ]);
    }

    public function destroy($id)
    {
        $usuario = $this->usuarioService->getById((int) $id);
        $this->usuarioService->delete($usuario);

        return response()->json([
            'message' => 'Usuario eliminado correctamente',
        ]);
    }
}

