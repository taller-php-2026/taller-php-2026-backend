<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompletarPerfilRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Cliente;
use App\Models\Profesional;
use App\Models\Usuario;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function buildUsuarioPayload(Usuario $usuario): array
    {
        $usuario->load('administrador', 'profesional', 'cliente');

        return [
            'idUsuario'      => $usuario->idUsuario,
            'nombre'         => $usuario->nombre,
            'email'          => $usuario->email,
            'telefono'       => $usuario->telefono,
            'activo'         => (bool) $usuario->activo,
            'roles'          => $usuario->roles,
            'tipoPrincipal'  => $usuario->tipoPrincipal,
            'imagenPerfilUrl' => $usuario->imagenPerfilUrl,
        ];
    }

    private function buildProfesionalPayload(Usuario $usuario): ?array
    {
        $usuario->loadMissing('profesional');

        if (! $usuario->profesional) {
            return null;
        }

        return [
            'idProfesional'   => $usuario->profesional->idUsuario,
            'idUsuario'       => $usuario->profesional->idUsuario,
            'nombreNegocio'   => $usuario->profesional->nombreNegocio,
            'descripcion'     => $usuario->profesional->descripcion,
            'ratingPromedio'  => $usuario->profesional->ratingPromedio,
            'imagen'          => $usuario->imagenPerfilUrl,
            'imagenPerfilUrl' => $usuario->imagenPerfilUrl,
        ];
    }

    private function buildClientePayload(Usuario $usuario): ?array
    {
        $usuario->loadMissing('cliente');

        if (! $usuario->cliente) {
            return null;
        }

        return [
            'idCliente' => $usuario->cliente->idUsuario,
            'idUsuario' => $usuario->cliente->idUsuario,
        ];
    }

    private function buildAuthResponse(Usuario $usuario): JsonResponse
    {
        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'usuario'      => $this->buildUsuarioPayload($usuario),
        ]);
    }

    // -------------------------------------------------------------------------
    // Endpoints
    // -------------------------------------------------------------------------

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            app(ActivityLogService::class)->log(
                'auth',
                'login_failed',
                'Intento de login fallido',
                $request,
                ['email' => $request->email],
                401
            );

            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        app(ActivityLogService::class)->log(
            'auth',
            'login_success',
            'Usuario inicio sesion correctamente',
            $request,
            ['idUsuario' => $usuario->idUsuario],
            200
        );

        return $this->buildAuthResponse($usuario);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $usuario = DB::transaction(function () use ($data) {
            $usuario = Usuario::create([
                'nombre'   => $data['nombre'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'telefono' => $data['telefono'],
                'activo'   => true,
            ]);

            if ($data['tipo'] === 'cliente') {
                Cliente::create(['idUsuario' => $usuario->idUsuario]);
            } else {
                Profesional::create([
                    'idUsuario'     => $usuario->idUsuario,
                    'nombreNegocio' => $data['nombreNegocio'],
                    'descripcion'   => $data['descripcion'],
                ]);
            }

            return $usuario;
        });

        app(ActivityLogService::class)->log(
            'auth',
            'register',
            'Usuario registrado correctamente',
            $request,
            ['idUsuario' => $usuario->idUsuario, 'tipo' => $data['tipo']],
            201
        );

        return $this->buildAuthResponse($usuario);
    }

    public function logout(Request $request): JsonResponse
    {
        app(ActivityLogService::class)->log(
            'auth',
            'logout',
            'Usuario cerro sesion correctamente',
            $request,
            ['idUsuario' => $request->user()->idUsuario],
            200
        );

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout correcto']);
    }

    public function me(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $usuarioPayload = $this->buildUsuarioPayload($usuario);
        $response = [
            'usuario' => $usuarioPayload,
        ];

        $profesionalPayload = $this->buildProfesionalPayload($usuario);
        if ($profesionalPayload) {
            $response['profesional'] = $profesionalPayload;
            $response['usuario']['profesional'] = $profesionalPayload;
        }

        $clientePayload = $this->buildClientePayload($usuario);
        if ($clientePayload) {
            $response['cliente'] = $clientePayload;
            $response['usuario']['cliente'] = $clientePayload;
        }

        return response()->json($response);
    }

    public function completarPerfil(CompletarPerfilRequest $request): JsonResponse
    {
        $usuario = $request->user();
        $data    = $request->validated();

        $usuario->load('administrador', 'profesional', 'cliente');

        if (! empty($usuario->roles)) {
            return response()->json([
                'message' => 'El usuario ya tiene un perfil asignado.',
            ], 409);
        }

        DB::transaction(function () use ($usuario, $data) {
            $usuario->telefono = $data['telefono'];
            $usuario->save();

            if ($data['tipo'] === 'cliente') {
                Cliente::create(['idUsuario' => $usuario->idUsuario]);
            } else {
                Profesional::create([
                    'idUsuario'     => $usuario->idUsuario,
                    'nombreNegocio' => $data['nombreNegocio'],
                    'descripcion'   => $data['descripcion'],
                ]);
            }
        });

        // Refrescar relaciones para que los accessors reflejen el nuevo rol
        $usuario->unsetRelation('administrador')->unsetRelation('profesional')->unsetRelation('cliente');

        return response()->json([
            'usuario' => $this->buildUsuarioPayload($usuario),
        ]);
    }
}
