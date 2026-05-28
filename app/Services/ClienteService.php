<?php

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Collection;

class ClienteService
{
    private const WITH_RELATIONS = ['usuario'];

    public function getAll(): Collection
    {
        return Cliente::with(self::WITH_RELATIONS)->get();
    }

    public function getById(int $id): Cliente
    {
        return Cliente::with(self::WITH_RELATIONS)->findOrFail($id);
    }

    public function create(array $data): Cliente
    {
        $cliente = Cliente::create($data);

        return $cliente->load(self::WITH_RELATIONS);
    }

    public function update(Cliente $cliente, array $data): Cliente
    {
        $cliente->update($data);

        return $cliente->fresh(self::WITH_RELATIONS);
    }

    public function delete(Cliente $cliente): void
    {
        $cliente->delete();
    }
}
