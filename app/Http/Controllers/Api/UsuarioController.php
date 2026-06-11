<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Services\CloudinaryService;
use App\Services\UsuarioService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function __construct(
        private UsuarioService $usuarioService,
        private CloudinaryService $cloudinaryService,
    ) {}

    public function index()
    {
        $this->adminOnly(request());

        $usuarios = $this->usuarioService->getAll();

        return response()->json([
            'data' => $usuarios,
        ]);
    }

    public function store(StoreUsuarioRequest $request)
    {
        $this->adminOnly($request);

        $usuario = $this->usuarioService->create($request->validated());

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'data'    => $usuario,
        ], 201);
    }

    public function show($id)
    {
        $this->ensureOwnUserOrAdmin(request(), (int) $id);

        $usuario = $this->usuarioService->getById((int) $id);

        return response()->json([
            'data' => $usuario,
        ]);
    }

    public function update(UpdateUsuarioRequest $request, $id)
    {
        $this->ensureOwnUserOrAdmin($request, (int) $id);

        $usuario = $this->usuarioService->getById((int) $id);
        $data = $request->validated();

        if (! $request->user()->administrador) {
            unset($data['activo']);
        }

        $usuario = $this->usuarioService->update($usuario, $data);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data'    => $usuario,
        ]);
    }

    public function destroy($id)
    {
        $this->adminOnly(request());

        $usuario = $this->usuarioService->getById((int) $id);
        $this->usuarioService->delete($usuario);

        return response()->json([
            'message' => 'Usuario eliminado correctamente',
        ]);
    }

    public function subirImagen(Request $request, $id)
    {
        $this->ensureOwnUserOrAdmin($request, (int) $id);

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

    public function actualizarMiPerfil(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'nombre'        => 'sometimes|string|max:255',
            'email'         => ['sometimes', 'email', Rule::unique('usuarios', 'email')->ignore($user->idUsuario, 'idUsuario')],
            'password'      => 'sometimes|nullable|string|min:8',
            'telefono'      => 'sometimes|string|max:50',
            'nombreNegocio' => 'sometimes|string|max:255',
            'descripcion'   => 'sometimes|string|max:255',
        ]);

        $usuarioData = array_intersect_key($data, array_flip(['nombre', 'email', 'password', 'telefono']));
        $usuario = $this->usuarioService->update($user, $usuarioData);

        if ($user->profesional && array_intersect_key($data, array_flip(['nombreNegocio', 'descripcion']))) {
            $user->profesional->update(array_intersect_key($data, array_flip(['nombreNegocio', 'descripcion'])));
        }

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'data'    => $usuario->fresh(['profesional', 'cliente', 'administrador']),
        ]);
    }

    public function subirMiImagen(Request $request)
    {
        return $this->subirImagen($request, (int) $request->user()->idUsuario);
    }

    private function ensureOwnUserOrAdmin(Request $request, int $idUsuario): void
    {
        $user = $request->user();
        $user?->loadMissing('administrador');

        if ($user && ($user->administrador || (int) $user->idUsuario === $idUsuario)) {
            return;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'No tenés permisos para modificar este usuario.',
        ], 403));
    }

    private function adminOnly(Request $request): void
    {
        $user = $request->user();
        $user?->loadMissing('administrador');

        if ($user && $user->administrador) {
            return;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'Solo administradores pueden realizar esta acción.',
        ], 403));
    }
}
