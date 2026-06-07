<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $esNuevo = false;

        $usuario = DB::transaction(function () use ($googleUser, &$esNuevo) {
            $usuario = Usuario::where('email', $googleUser->getEmail())->first();

            if (! $usuario) {
                $esNuevo = true;
                $usuario = Usuario::create([
                    'nombre'   => $googleUser->getName(),
                    'email'    => $googleUser->getEmail(),
                    'password' => bcrypt(str()->random(32)),
                    'telefono' => '',
                    'activo'   => true,
                ]);
            }

            return $usuario;
        });

        $usuario->load('administrador', 'profesional', 'cliente');

        $token                  = $usuario->createToken('auth_token')->plainTextToken;
        $roles                  = implode(',', $usuario->roles);
        $tipoPrincipal          = $usuario->tipoPrincipal ?? '';
        $necesitaCompletarPerfil = $esNuevo || empty($usuario->roles);

        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:4200'), '/');

        $query = http_build_query([
            'token'                  => $token,
            'idUsuario'              => $usuario->idUsuario,
            'nombre'                 => $usuario->nombre,
            'email'                  => $usuario->email,
            'roles'                  => $roles,
            'tipoPrincipal'          => $tipoPrincipal,
            'necesitaCompletarPerfil' => $necesitaCompletarPerfil ? 'true' : 'false',
        ]);

        return redirect("{$frontendUrl}/auth/google/callback?{$query}");
    }
}
