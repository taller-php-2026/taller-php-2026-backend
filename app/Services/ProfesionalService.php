<?php

namespace App\Services;

use App\Models\Profesional;
use Illuminate\Database\Eloquent\Collection;

class ProfesionalService
{
    private const WITH_RELATIONS = ['usuario'];

    public function getAll(): Collection
    {
        return Profesional::with(self::WITH_RELATIONS)->get();
    }

    public function getById(int $id): Profesional
    {
        return Profesional::with(self::WITH_RELATIONS)->findOrFail($id);
    }

    public function create(array $data): Profesional
    {
        $profesional = Profesional::create($data);

        return $profesional->load(self::WITH_RELATIONS);
    }

    public function update(Profesional $profesional, array $data): Profesional
    {
        $profesional->update($data);

        return $profesional->fresh(self::WITH_RELATIONS);
    }

    public function delete(Profesional $profesional): void
    {
        $profesional->delete();
    }
}
