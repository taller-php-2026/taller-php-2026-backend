<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Services\CloudinaryService;
use App\Services\UsuarioService;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function __construct(
        private UsuarioService $usuarioService,
        private CloudinaryService $cloudinaryService,
    ) {}

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

    public function subirImagen(Request $request, $id)
    {
        $request->validate([
            'imagen' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $usuario = $this->usuarioService->getById((int) $id);

        $this->cloudinaryService->eliminarImagen($usuario->imagenPerfilPublicId);

        $resultado = $this->cloudinaryService->subirImagen($request->file('imagen'), 'taller-php/usuarios');

        $usuario->update([
            'imagenPerfilUrl'      => $resultado['url'],
            'imagenPerfilPublicId' => $resultado['public_id'],
        ]);

        return response()->json([
            'message' => 'Imagen de perfil subida correctamente',
            'data'    => $usuario,
        ]);
    }
}

