<?php

namespace App\Services;

use App\Models\PaqueteServicio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PaqueteServicioService
{
    private const WITH_RELATIONS = [
        'servicio.ubicacion',
        'servicio.profesionales.usuario',
        'serviciosComunes.servicio.ubicacion',
    ];

    public function getAll(array $filtros = []): Collection
    {
        $paquetes = PaqueteServicio::with(self::WITH_RELATIONS)
            ->where('activo', true)
            ->whereHas('servicio', fn ($query) => $query->where('activo', true))
            ->get();

        if (isset($filtros['ratingMin'])) {
            $paquetes = $paquetes->filter(fn (PaqueteServicio $paquete) => $this->ratingPaquete($paquete) >= (float) $filtros['ratingMin'])->values();
        }

        $ordenarPor = $filtros['ordenarPor'] ?? 'recientes';
        $desc = ($filtros['orden'] ?? 'desc') === 'desc';

        $paquetes = match ($ordenarPor) {
            'precio' => $paquetes->sortBy(fn (PaqueteServicio $paquete) => (float) $paquete->precio, SORT_REGULAR, $desc),
            'rating' => $paquetes->sortBy(fn (PaqueteServicio $paquete) => $this->ratingPaquete($paquete), SORT_REGULAR, $desc),
            'nombre' => $paquetes->sortBy(fn (PaqueteServicio $paquete) => strtolower((string) $paquete->servicio?->nombre), SORT_REGULAR, $desc),
            default => $paquetes->sortBy(fn (PaqueteServicio $paquete) => $paquete->created_at, SORT_REGULAR, $desc),
        };

        return new Collection($paquetes->values()->all());
    }

    public function getById(int $id): PaqueteServicio
    {
        return PaqueteServicio::with(self::WITH_RELATIONS)->findOrFail($id);
    }

    public function create(array $data): PaqueteServicio
    {
        return DB::transaction(function () use ($data) {
            // 1. Crear el registro en la tabla padre 'servicios'
            $servicio = \App\Models\Servicio::create([
                'nombre'          => $data['nombre'],
                'descripcion'     => $data['descripcion'],
                'precio'          => $data['precio'],
                'duracionMinutos' => $data['duracionMinutos'],
                'activo'          => $data['activo'] ?? true,
                'modalidad'       => $data['modalidad'] ?? 'presencial',
                'idUbicacion'     => $data['idUbicacion'] ?? null,
                'idVideoSesion'   => $data['idVideoSesion'] ?? null,
            ]);

            // Asociar profesional al servicio (paquete) creado
            $servicio->profesionales()->attach($data['idProfesional']);

            // 2. Crear el registro hijo en 'paquetes_servicios'
            $paquete = PaqueteServicio::create([
                'idServicio'    => $servicio->idServicio,
                'totalSesiones' => $data['totalSesiones'],
                'precio'        => $data['precio'],
                'activo'        => $data['activo'] ?? true,
            ]);

            // 3. Asociar con los servicios comunes base indicados en 'servicios_ids'
            foreach ($data['servicios_ids'] as $servId) {
                // Obtener o crear el registro en la tabla hija 'servicios_comunes' si aún no existe
                $servComun = \App\Models\ServicioComun::firstOrCreate(['idServicio' => $servId]);
                $paquete->serviciosComunes()->attach($servComun->idServicioComun);
            }

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

    private function ratingPaquete(PaqueteServicio $paquete): float
    {
        return (float) ($paquete->servicio?->profesionales->max('ratingPromedio') ?? 0);
    }
}
