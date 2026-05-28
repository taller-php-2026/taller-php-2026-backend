<?php

namespace App\Services;

use App\Models\Agenda;
use Illuminate\Database\Eloquent\Collection;

class AgendaService
{
    private const WITH_RELATIONS = [
        'ciclo',
        'reglasDisponibilidad',
        'excepcionesDisponibilidad',
    ];

    public function getAll(): Collection
    {
        return Agenda::with(self::WITH_RELATIONS)->get();
    }

    public function getById(int $id): Agenda
    {
        return Agenda::with(self::WITH_RELATIONS)->findOrFail($id);
    }

    public function create(array $data): Agenda
    {
        $agenda = Agenda::create($data);

        return $agenda->load(self::WITH_RELATIONS);
    }

    public function update(Agenda $agenda, array $data): Agenda
    {
        $agenda->update($data);

        return $agenda->fresh(self::WITH_RELATIONS);
    }

    public function delete(Agenda $agenda): void
    {
        $agenda->delete();
    }
}
