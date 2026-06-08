<?php

namespace App\Services;

use App\Models\Servicio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function buscar(array $filtros): LengthAwarePaginator
    {
        $query = Servicio::query()
            ->select('servicios.*')
            ->leftJoin('profesionales_servicios', 'servicios.idServicio', '=', 'profesionales_servicios.idServicio')
            ->leftJoin('profesionales', 'profesionales_servicios.idProfesional', '=', 'profesionales.idUsuario');

        if (!empty($filtros['texto'])) {
            $texto = $filtros['texto'];
            $query->where(function ($q) use ($texto) {
                $q->where('servicios.nombre', 'ILIKE', "%{$texto}%")
                    ->orWhere('servicios.descripcion', 'ILIKE', "%{$texto}%")
                    ->orWhere('profesionales.nombreNegocio', 'ILIKE', "%{$texto}%");
            });
        }

        if (!empty($filtros['modalidad'])) {
            $query->where('servicios.modalidad', $filtros['modalidad']);
        }

        if (isset($filtros['precioMin'])) {
            $query->where('servicios.precio', '>=', $filtros['precioMin']);
        }

        if (isset($filtros['precioMax'])) {
            $query->where('servicios.precio', '<=', $filtros['precioMax']);
        }

        if (isset($filtros['ratingMin'])) {
            $query->where('profesionales.ratingPromedio', '>=', $filtros['ratingMin']);
        }

        if (isset($filtros['activo'])) {
            $query->where('servicios.activo', filter_var($filtros['activo'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filtros['idProfesional'])) {
            $query->where('profesionales_servicios.idProfesional', $filtros['idProfesional']);
        }

        $ordenarPor = $filtros['ordenarPor'] ?? 'recientes';
        $orden      = $filtros['orden'] ?? 'desc';

        match ($ordenarPor) {
            'precio'    => $query->orderBy('servicios.precio', $orden),
            'rating'    => $query->orderBy('profesionales.ratingPromedio', $orden),
            'nombre'    => $query->orderBy('servicios.nombre', $orden),
            default     => $query->orderBy('servicios.created_at', $orden),
        };

        $perPage = min((int) ($filtros['perPage'] ?? 10), 50);

        $paginator = $query->paginate($perPage);

        $paginator->getCollection()->transform(function (Servicio $servicio) {
            $profesional = $servicio->profesionales()->first();

            return [
                'idServicio'       => $servicio->idServicio,
                'nombre'           => $servicio->nombre,
                'descripcion'      => $servicio->descripcion,
                'precio'           => $servicio->precio,
                'duracionMinutos'  => $servicio->duracionMinutos,
                'modalidad'        => $servicio->modalidad,
                'activo'           => (bool) $servicio->activo,
                'profesional'      => $profesional ? [
                    'idUsuario'       => $profesional->idUsuario,
                    'nombreNegocio'   => $profesional->nombreNegocio,
                    'ratingPromedio'  => $profesional->ratingPromedio,
                ] : null,
            ];
        });

        return $paginator;
    }

    public function getProfesionalesByServicioId(int $id): Collection
    {
        return Servicio::findOrFail($id)->profesionales()->with('usuario')->get();
    }
}
