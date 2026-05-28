<?php

namespace App\Services;

use App\Models\ExcepcionDisponibilidad;
use Illuminate\Database\Eloquent\Collection;

class ExcepcionDisponibilidadService
{
    private const WITH_RELATIONS = ['agenda'];

    public function getAll(): Collection
    {
        return ExcepcionDisponibilidad::with(self::WITH_RELATIONS)->get();
    }

    public function getById(int $id): ExcepcionDisponibilidad
    {
        return ExcepcionDisponibilidad::with(self::WITH_RELATIONS)->findOrFail($id);
    }

    public function create(array $data): ExcepcionDisponibilidad
    {
        $excepcion = ExcepcionDisponibilidad::create($data);

        return $excepcion->load(self::WITH_RELATIONS);
    }

    public function update(ExcepcionDisponibilidad $excepcion, array $data): ExcepcionDisponibilidad
    {
        $excepcion->update($data);

        return $excepcion->fresh(self::WITH_RELATIONS);
    }

    public function delete(ExcepcionDisponibilidad $excepcion): void
    {
        $excepcion->delete();
    }
}
