<?php

namespace App\Services;

use App\Models\PaqueteComprado;
use App\Models\Reserva;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminMetricasService
{
    public function metricas(): array
    {
        $totalUsuarios        = DB::table('usuarios')->count();
        $totalClientes        = DB::table('clientes')->count();
        $totalProfesionales   = DB::table('profesionales')->count();
        $totalAdministradores = DB::table('administradores')->count();

        $servicioStats = DB::table('servicios')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN activo = true THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN modalidad = 'presencial' THEN 1 ELSE 0 END) as presenciales,
                SUM(CASE WHEN modalidad = 'virtual' THEN 1 ELSE 0 END) as virtuales,
                SUM(CASE WHEN modalidad = 'hibrida' THEN 1 ELSE 0 END) as hibridos
            ")
            ->first();

        $reservaStats = DB::table('reservas')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'pendiente'  THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 'confirmada' THEN 1 ELSE 0 END) as confirmadas,
                SUM(CASE WHEN estado = 'cancelada'  THEN 1 ELSE 0 END) as canceladas,
                SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as completadas,
                SUM(CASE WHEN estado = 'enCurso'    THEN 1 ELSE 0 END) as en_curso
            ")
            ->first();

        $pagoStats = DB::table('pagos')
            ->where('estado', 'aprobado')
            ->selectRaw("COUNT(*) as cantidad, COALESCE(SUM(monto), 0) as monto")
            ->first();

        $paqueteStats = DB::table('paquetes_comprados')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN estado = \'pendiente\'  THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = \'activo\'     THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN estado = \'agotado\'    THEN 1 ELSE 0 END) as agotados,
                SUM(CASE WHEN estado = \'cancelado\'  THEN 1 ELSE 0 END) as cancelados,
                COALESCE(SUM("totalSesiones"), 0)      as sesiones_vendidas,
                COALESCE(SUM("sesionesUsadas"), 0)     as sesiones_usadas,
                COALESCE(SUM("sesionesRestantes"), 0)  as sesiones_restantes
            ')
            ->first();

        $resenaStats = DB::table('resenas')
            ->selectRaw("COUNT(*) as total, COALESCE(AVG(calificacion), 0) as promedio")
            ->first();

        return [
            'usuarios' => [
                'total'           => (int) $totalUsuarios,
                'clientes'        => (int) $totalClientes,
                'profesionales'   => (int) $totalProfesionales,
                'administradores' => (int) $totalAdministradores,
            ],
            'servicios' => [
                'total'        => (int) $servicioStats->total,
                'activos'      => (int) $servicioStats->activos,
                'presenciales' => (int) $servicioStats->presenciales,
                'virtuales'    => (int) $servicioStats->virtuales,
                'hibridos'     => (int) $servicioStats->hibridos,
            ],
            'reservas' => [
                'total'       => (int) ($reservaStats->total       ?? 0),
                'pendientes'  => (int) ($reservaStats->pendientes  ?? 0),
                'confirmadas' => (int) ($reservaStats->confirmadas ?? 0),
                'canceladas'  => (int) ($reservaStats->canceladas  ?? 0),
                'completadas' => (int) ($reservaStats->completadas ?? 0),
                'enCurso'     => (int) ($reservaStats->en_curso    ?? 0),
            ],
            'pagos' => [
                'cantidadAprobados'  => (int)   ($pagoStats->cantidad ?? 0),
                'montoTotalAprobado' => (float) ($pagoStats->monto    ?? 0),
            ],
            'paquetes' => [
                'totalComprados'    => (int) ($paqueteStats->total              ?? 0),
                'pendientes'        => (int) ($paqueteStats->pendientes         ?? 0),
                'activos'           => (int) ($paqueteStats->activos            ?? 0),
                'agotados'          => (int) ($paqueteStats->agotados           ?? 0),
                'cancelados'        => (int) ($paqueteStats->cancelados         ?? 0),
                'sesionesVendidas'  => (int) ($paqueteStats->sesiones_vendidas  ?? 0),
                'sesionesUsadas'    => (int) ($paqueteStats->sesiones_usadas    ?? 0),
                'sesionesRestantes' => (int) ($paqueteStats->sesiones_restantes ?? 0),
            ],
            'resenas' => [
                'total'                 => (int) $resenaStats->total,
                'ratingPromedioGeneral' => round((float) $resenaStats->promedio, 2),
            ],
        ];
    }

    public function reservas(array $filtros): LengthAwarePaginator
    {
        $query = Reserva::with([
            'cliente.usuario',
            'profesional.usuario',
            'servicio',
            'pago',
            'horario',
            'paqueteComprado',
        ]);

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        if (!empty($filtros['idProfesional'])) {
            $query->where('idProfesional', $filtros['idProfesional']);
        }
        if (!empty($filtros['idServicio'])) {
            $query->where('idServicio', $filtros['idServicio']);
        }
        if (!empty($filtros['idCliente'])) {
            $query->where('idCliente', $filtros['idCliente']);
        }
        if (!empty($filtros['fechaDesde'])) {
            $query->whereDate('fechaReserva', '>=', $filtros['fechaDesde']);
        }
        if (!empty($filtros['fechaHasta'])) {
            $query->whereDate('fechaReserva', '<=', $filtros['fechaHasta']);
        }

        $perPage = min((int) ($filtros['perPage'] ?? 10), 50);

        return $query->orderBy('fechaReserva', 'desc')->paginate($perPage);
    }

    public function reservasPorProfesional(array $filtros): array
    {
        $query = DB::table('reservas')
            ->join('profesionales', 'reservas.idProfesional', '=', 'profesionales.idUsuario')
            ->select(
                'profesionales.idUsuario as idProfesional',
                'profesionales.nombreNegocio',
                DB::raw("COUNT(*) as total_reservas"),
                DB::raw("SUM(CASE WHEN reservas.estado = 'pendiente'  THEN 1 ELSE 0 END) as pendientes"),
                DB::raw("SUM(CASE WHEN reservas.estado = 'confirmada' THEN 1 ELSE 0 END) as confirmadas"),
                DB::raw("SUM(CASE WHEN reservas.estado = 'cancelada'  THEN 1 ELSE 0 END) as canceladas"),
                DB::raw("SUM(CASE WHEN reservas.estado = 'completada' THEN 1 ELSE 0 END) as completadas"),
                DB::raw("SUM(CASE WHEN reservas.estado = 'enCurso'    THEN 1 ELSE 0 END) as en_curso")
            )
            ->groupBy('profesionales.idUsuario', 'profesionales.nombreNegocio');

        if (!empty($filtros['estado'])) {
            $query->where('reservas.estado', $filtros['estado']);
        }
        if (!empty($filtros['idProfesional'])) {
            $query->where('reservas.idProfesional', $filtros['idProfesional']);
        }
        if (!empty($filtros['fechaDesde'])) {
            $query->whereDate('reservas.fechaReserva', '>=', $filtros['fechaDesde']);
        }
        if (!empty($filtros['fechaHasta'])) {
            $query->whereDate('reservas.fechaReserva', '<=', $filtros['fechaHasta']);
        }

        return $query->get()->map(fn($row) => [
            'idProfesional' => (int) ($row->idProfesional ?? 0),
            'nombreNegocio' => $row->nombreNegocio,
            'totalReservas' => (int) ($row->total_reservas ?? 0),
            'pendientes'    => (int) ($row->pendientes    ?? 0),
            'confirmadas'   => (int) ($row->confirmadas   ?? 0),
            'canceladas'    => (int) ($row->canceladas    ?? 0),
            'completadas'   => (int) ($row->completadas   ?? 0),
            'enCurso'       => (int) ($row->en_curso      ?? 0),
        ])->values()->all();
    }

    public function reservasPorServicio(array $filtros): array
    {
        $query = DB::table('reservas')
            ->join('servicios', 'reservas.idServicio', '=', 'servicios.idServicio')
            ->select(
                'servicios.idServicio',
                'servicios.nombre',
                'servicios.modalidad',
                DB::raw("COUNT(*) as total_reservas"),
                DB::raw("SUM(CASE WHEN reservas.estado = 'pendiente'  THEN 1 ELSE 0 END) as pendientes"),
                DB::raw("SUM(CASE WHEN reservas.estado = 'confirmada' THEN 1 ELSE 0 END) as confirmadas"),
                DB::raw("SUM(CASE WHEN reservas.estado = 'cancelada'  THEN 1 ELSE 0 END) as canceladas"),
                DB::raw("SUM(CASE WHEN reservas.estado = 'completada' THEN 1 ELSE 0 END) as completadas"),
                DB::raw("SUM(CASE WHEN reservas.estado = 'enCurso'    THEN 1 ELSE 0 END) as en_curso")
            )
            ->groupBy('servicios.idServicio', 'servicios.nombre', 'servicios.modalidad');

        if (!empty($filtros['estado'])) {
            $query->where('reservas.estado', $filtros['estado']);
        }
        if (!empty($filtros['idServicio'])) {
            $query->where('reservas.idServicio', $filtros['idServicio']);
        }
        if (!empty($filtros['modalidad'])) {
            $query->where('servicios.modalidad', $filtros['modalidad']);
        }
        if (!empty($filtros['fechaDesde'])) {
            $query->whereDate('reservas.fechaReserva', '>=', $filtros['fechaDesde']);
        }
        if (!empty($filtros['fechaHasta'])) {
            $query->whereDate('reservas.fechaReserva', '<=', $filtros['fechaHasta']);
        }

        return $query->get()->map(fn($row) => [
            'idServicio'    => (int) ($row->idServicio    ?? 0),
            'nombre'        => $row->nombre,
            'modalidad'     => $row->modalidad,
            'totalReservas' => (int) ($row->total_reservas ?? 0),
            'pendientes'    => (int) ($row->pendientes    ?? 0),
            'confirmadas'   => (int) ($row->confirmadas   ?? 0),
            'canceladas'    => (int) ($row->canceladas    ?? 0),
            'completadas'   => (int) ($row->completadas   ?? 0),
            'enCurso'       => (int) ($row->en_curso      ?? 0),
        ])->values()->all();
    }

    public function paquetes(array $filtros): LengthAwarePaginator
    {
        $query = PaqueteComprado::with([
            'cliente.usuario',
            'paqueteServicio.servicio',
            'pago',
        ]);

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        if (!empty($filtros['idCliente'])) {
            $query->where('idCliente', $filtros['idCliente']);
        }
        if (!empty($filtros['idPaqueteServicio'])) {
            $query->where('idPaqueteServicio', $filtros['idPaqueteServicio']);
        }
        if (!empty($filtros['fechaDesde'])) {
            $query->whereDate('fechaCompra', '>=', $filtros['fechaDesde']);
        }
        if (!empty($filtros['fechaHasta'])) {
            $query->whereDate('fechaCompra', '<=', $filtros['fechaHasta']);
        }

        $perPage = min((int) ($filtros['perPage'] ?? 10), 50);

        return $query->orderBy('fechaCompra', 'desc')->paginate($perPage);
    }

    public function resumenPaquetes(): array
    {
        $stats = DB::table('paquetes_comprados')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN estado = \'pendiente\'  THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = \'activo\'     THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN estado = \'agotado\'    THEN 1 ELSE 0 END) as agotados,
                SUM(CASE WHEN estado = \'cancelado\'  THEN 1 ELSE 0 END) as cancelados,
                COALESCE(SUM("totalSesiones"), 0)     as sesiones_vendidas,
                COALESCE(SUM("sesionesUsadas"), 0)    as sesiones_usadas,
                COALESCE(SUM("sesionesRestantes"), 0) as sesiones_restantes
            ')
            ->first();

        $montoAprobado = DB::table('paquetes_comprados')
            ->join('pagos', 'paquetes_comprados.idPago', '=', 'pagos.idPago')
            ->where('pagos.estado', 'aprobado')
            ->sum('pagos.monto');

        return [
            'totalComprados'     => (int)   ($stats->total              ?? 0),
            'pendientes'         => (int)   ($stats->pendientes         ?? 0),
            'activos'            => (int)   ($stats->activos            ?? 0),
            'agotados'           => (int)   ($stats->agotados           ?? 0),
            'cancelados'         => (int)   ($stats->cancelados         ?? 0),
            'sesionesVendidas'   => (int)   ($stats->sesiones_vendidas  ?? 0),
            'sesionesUsadas'     => (int)   ($stats->sesiones_usadas    ?? 0),
            'sesionesRestantes'  => (int)   ($stats->sesiones_restantes ?? 0),
            'montoTotalAprobado' => (float) $montoAprobado,
        ];
    }

    public function paquetesPorServicio(): array
    {
        $rows = DB::table('paquetes_comprados')
            ->join('paquetes_servicios', 'paquetes_comprados.idPaqueteServicio', '=', 'paquetes_servicios.idPaqueteServicio')
            ->join('servicios', 'paquetes_servicios.idServicio', '=', 'servicios.idServicio')
            ->leftJoin('pagos', function ($join) {
                $join->on('paquetes_comprados.idPago', '=', 'pagos.idPago')
                     ->where('pagos.estado', '=', 'aprobado');
            })
            ->select(
                'servicios.idServicio',
                'servicios.nombre',
                'servicios.modalidad',
                DB::raw("COUNT(*) as total_paquetes_comprados"),
                DB::raw("SUM(CASE WHEN paquetes_comprados.estado = 'pendiente'  THEN 1 ELSE 0 END) as paquetes_pendientes"),
                DB::raw("SUM(CASE WHEN paquetes_comprados.estado = 'activo'     THEN 1 ELSE 0 END) as paquetes_activos"),
                DB::raw("SUM(CASE WHEN paquetes_comprados.estado = 'agotado'    THEN 1 ELSE 0 END) as paquetes_agotados"),
                DB::raw('COALESCE(SUM(paquetes_comprados."totalSesiones"), 0)     as sesiones_vendidas'),
                DB::raw('COALESCE(SUM(paquetes_comprados."sesionesUsadas"), 0)    as sesiones_usadas'),
                DB::raw('COALESCE(SUM(paquetes_comprados."sesionesRestantes"), 0) as sesiones_restantes'),
                DB::raw('COALESCE(SUM(pagos.monto), 0) as monto_total')
            )
            ->groupBy('servicios.idServicio', 'servicios.nombre', 'servicios.modalidad')
            ->get();

        return $rows->map(fn($row) => [
            'idServicio'             => (int)   ($row->idServicio             ?? 0),
            'nombre'                 => $row->nombre,
            'modalidad'              => $row->modalidad,
            'totalPaquetesComprados' => (int)   ($row->total_paquetes_comprados ?? 0),
            'paquetesPendientes'     => (int)   ($row->paquetes_pendientes     ?? 0),
            'paquetesActivos'        => (int)   ($row->paquetes_activos        ?? 0),
            'paquetesAgotados'       => (int)   ($row->paquetes_agotados       ?? 0),
            'sesionesVendidas'       => (int)   ($row->sesiones_vendidas       ?? 0),
            'sesionesUsadas'         => (int)   ($row->sesiones_usadas         ?? 0),
            'sesionesRestantes'      => (int)   ($row->sesiones_restantes      ?? 0),
            'montoTotal'             => (float) ($row->monto_total             ?? 0),
        ])->values()->all();
    }
}
