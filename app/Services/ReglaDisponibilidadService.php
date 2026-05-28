<?php

namespace App\Services;

use App\Models\ReglaDisponibilidad;
use Illuminate\Database\Eloquent\Collection;

class ReglaDisponibilidadService
{
    private const WITH_RELATIONS = ['agenda', 'profesional'];

    public function getAll(): Collection
    {
        return ReglaDisponibilidad::with(self::WITH_RELATIONS)->get();
    }

    public function getById(int $id): ReglaDisponibilidad
    {
        return ReglaDisponibilidad::with(self::WITH_RELATIONS)->findOrFail($id);
    }

    public function create(array $data): ReglaDisponibilidad
    {
        $regla = ReglaDisponibilidad::create($data);

        return $regla->load(self::WITH_RELATIONS);
    }

    public function update(ReglaDisponibilidad $regla, array $data): ReglaDisponibilidad
    {
        $regla->update($data);

        return $regla->fresh(self::WITH_RELATIONS);
    }

    public function delete(ReglaDisponibilidad $regla): void
    {
        $regla->delete();
    }
}
