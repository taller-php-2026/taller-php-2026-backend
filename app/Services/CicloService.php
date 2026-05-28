<?php

namespace App\Services;

use App\Models\Ciclo;
use Illuminate\Database\Eloquent\Collection;

class CicloService
{
    public function getAll(): Collection
    {
        return Ciclo::all();
    }

    public function getById(int $id): Ciclo
    {
        return Ciclo::findOrFail($id);
    }

    public function create(array $data): Ciclo
    {
        return Ciclo::create($data);
    }

    public function update(Ciclo $ciclo, array $data): Ciclo
    {
        $ciclo->update($data);

        return $ciclo->fresh();
    }

    public function delete(Ciclo $ciclo): void
    {
        $ciclo->delete();
    }
}
