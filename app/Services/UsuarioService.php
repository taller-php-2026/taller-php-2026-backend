<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UsuarioService
{
    // Obtener todos los usuarios con sus relaciones.
    public function getAll(): Collection
    {
        return Usuario::with(['administrador', 'profesional', 'cliente'])->get();
    }

    public function getById(int $id): Usuario
    {
        return Usuario::findOrFail($id);
    }

    // Crear un usuario con su rol asociado.
    public function create(array $data): Usuario
    {
        $data['password'] = Hash::make($data['password']);
        $rol = $data['rol'] ?? null;
        unset($data['rol']);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($data, $rol) {
            $usuario = Usuario::create($data);

            if ($rol === 'administrador') {
                \App\Models\Administrador::create([
                    'idUsuario' => $usuario->idUsuario,
                    'nivelAcceso' => 'superadmin',
                ]);
            } elseif ($rol === 'cliente') {
                \App\Models\Cliente::create([
                    'idUsuario' => $usuario->idUsuario,
                ]);
            } elseif ($rol === 'profesional') {
                \App\Models\Profesional::create([
                    'idUsuario' => $usuario->idUsuario,
                    'nombreNegocio' => $usuario->nombre,
                    'descripcion' => 'Profesional registrado por el administrador.',
                ]);
            }

            return $usuario->fresh(['administrador', 'profesional', 'cliente']);
        });
    }

    public function update(Usuario $usuario, array $data): Usuario
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $usuario->update($data);

        return $usuario->fresh();
    }

    public function delete(Usuario $usuario): void
    {
        $usuario->delete();
    }
}
