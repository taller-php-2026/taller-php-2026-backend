<?php

namespace App\Services;

use App\Models\Servicio;
use Illuminate\Database\Eloquent\Collection;

class ServicioService
{
    public function getAll(): Collection
    {
        return Servicio::all();
    }

    public function getById(int $id): Servicio
    {
        return Servicio::with(['servicioComun'])->findOrFail($id);
    }

    public function create(array $data): Servicio
    {
        return Servicio::create($data);
    }

    public function update(Servicio $servicio, array $data): Servicio
    {
        $servicio->update($data);

        return $servicio->fresh();
    }

    public function delete(Servicio $servicio): void
    {
        $servicio->delete();
    }
}
