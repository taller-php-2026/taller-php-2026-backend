<?php

namespace App\Services;

use App\Models\Pago;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PagoService
{
    public function getAll(): Collection
    {
        return Pago::all();
    }

    public function getById(int $id): Pago
    {
        return Pago::findOrFail($id);
    }

    public function create(array $data): Pago
    {
        return DB::transaction(function () use ($data) {
            return Pago::create($data);
        });
    }

    public function update(Pago $pago, array $data): Pago
    {
        return DB::transaction(function () use ($pago, $data) {
            $pago->update($data);

            return $pago->fresh();
        });
    }

    public function delete(Pago $pago): void
    {
        DB::transaction(function () use ($pago) {
            $pago->delete();
        });
    }
}
