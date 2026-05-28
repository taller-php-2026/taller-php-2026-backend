<?php

namespace App\Services;

use App\Models\RangoHorario;
use Illuminate\Database\Eloquent\Collection;

class RangoHorarioService
{
    private const WITH_RELATIONS = ['ciclo'];

    public function getAll(): Collection
    {
        return RangoHorario::with(self::WITH_RELATIONS)->get();
    }

    public function getById(int $id): RangoHorario
    {
        return RangoHorario::with(self::WITH_RELATIONS)->findOrFail($id);
    }

    public function create(array $data): RangoHorario
    {
        $rango = RangoHorario::create($data);

        return $rango->load(self::WITH_RELATIONS);
    }

    public function update(RangoHorario $rango, array $data): RangoHorario
    {
        $rango->update($data);

        return $rango->fresh(self::WITH_RELATIONS);
    }

    public function delete(RangoHorario $rango): void
    {
        $rango->delete();
    }
}
