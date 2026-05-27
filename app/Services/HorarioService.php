<?php

namespace App\Services;

use App\Models\Horario;
use Illuminate\Database\Eloquent\Collection;

class HorarioService
{
    public function getAll(): Collection
    {
        return Horario::all();
    }

    public function getById(int $id): Horario
    {
        return Horario::findOrFail($id);
    }

    public function create(array $data): Horario
    {
        return Horario::create($data);
    }

    public function update(Horario $horario, array $data): Horario
    {
        $horario->update($data);

        return $horario->fresh();
    }

    public function delete(Horario $horario): void
    {
        $horario->delete();
    }
}
