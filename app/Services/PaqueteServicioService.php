<?php

namespace App\Services;

use App\Models\PaqueteServicio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PaqueteServicioService
{
    private const WITH_RELATIONS = ['servicio'];

    public function getAll(): Collection
    {
        return PaqueteServicio::with(self::WITH_RELATIONS)->get();
    }

    public function getById(int $id): PaqueteServicio
    {
        return PaqueteServicio::with(self::WITH_RELATIONS)->findOrFail($id);
    }

    public function create(array $data): PaqueteServicio
    {
        return DB::transaction(function () use ($data) {
            $paquete = PaqueteServicio::create($data);

            return $paquete->fresh(self::WITH_RELATIONS);
        });
    }

    public function update(PaqueteServicio $paquete, array $data): PaqueteServicio
    {
        return DB::transaction(function () use ($paquete, $data) {
            $paquete->update($data);

            return $paquete->fresh(self::WITH_RELATIONS);
        });
    }

    public function delete(PaqueteServicio $paquete): void
    {
        DB::transaction(function () use ($paquete) {
            $paquete->delete();
        });
    }
}