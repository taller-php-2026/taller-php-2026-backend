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
        'serviciosComunes.servicio',
    ];

    public function getAll(): Collection
    {
        return PaqueteServicio::with(self::WITH_RELATIONS)
            ->where('activo', true)
            ->whereHas('servicio', fn ($query) => $query->where('activo', true))
            ->get();
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
}
