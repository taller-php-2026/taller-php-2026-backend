<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UsuarioService
{
    public function getAll(): Collection
    {
        return Usuario::all();
    }

    public function getById(int $id): Usuario
    {
        return Usuario::findOrFail($id);
    }

    public function create(array $data): Usuario
    {
        $data['password'] = Hash::make($data['password']);

        return Usuario::create($data);
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
