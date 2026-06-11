<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class ProfesionalMetricasController extends Controller
{
    private const ESTADO_PENDIENTE = 'pendiente';
    private const ESTADO_CONFIRMADA = 'confirmada';
    private const ESTADO_CANCELADA = 'cancelada';
    private const ESTADO_COMPLETADA = 'completada';

    private const ESTADOS_INGRESO_ESTIMADO = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_CONFIRMADA,
        'enCurso',
    ];

    private const ESTADOS_INGRESO_CONFIRMADO = [
        self::ESTADO_CONFIRMADA,
        self::ESTADO_COMPLETADA,
        'enCurso',
        'finalizada',
    ];

    public function metricas(Request $request, $id)
    {
        $this->ensureCanViewProfessional($request, (int) $id);

        return $this->metricasPorProfesional((int) $id);
    }

    public function misMetricas(Request $request)
    {
        $user = $request->user();
        $user?->loadMissing('profesional');

        if (! $user || ! $user->profesional) {
            throw new HttpResponseException(response()->json([
                'message' => 'Solo los profesionales pueden consultar sus métricas.',
            ], 403));
        }

        return $this->metricasPorProfesional((int) $user->idUsuario);
    }

    private function metricasPorProfesional(int $idProfesional)
    {
        $hoy = Carbon::today();
        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();

        $reservas = Reserva::with(['servicio', 'pago', 'horario'])
            ->where('idProfesional', $idProfesional)
            ->get();

        $reservasHoy = $reservas->filter(
            fn (Reserva $reserva) => $this->fechaReserva($reserva)?->isSameDay($hoy)
        );

        $reservasProximas = $reservas->filter(
            fn (Reserva $reserva) => ($fecha = $this->fechaReserva($reserva))
                && $fecha->greaterThanOrEqualTo($hoy->copy()->startOfDay())
        );

        $reservasMesActual = $reservas->filter(
            fn (Reserva $reserva) => ($fecha = $this->fechaReserva($reserva))
                && $fecha->betweenIncluded($inicioMes, $finMes)
        );

        $hoyMetricas = $this->metricasReservas($reservasHoy, incluirIngresos: true);
        $proximosMetricas = $this->metricasReservas($reservasProximas);
        $mesActualMetricas = $this->metricasReservas($reservasMesActual, incluirIngresos: true);
        $totalesMetricas = $this->metricasTotales($idProfesional, $reservas);

        return response()->json([
            'message' => 'Métricas del profesional obtenidas correctamente',
            'data' => [
                // Compatibilidad con el dashboard actual: estos campos reflejan el bloque "hoy".
                'turnosTotales' => $hoyMetricas['turnosTotales'],
                'turnosConfirmados' => $hoyMetricas['turnosConfirmados'],
                'turnosPendientes' => $hoyMetricas['turnosPendientes'],
                'ingresosEstimados' => $hoyMetricas['ingresosEstimados'],
                'hoy' => $hoyMetricas,
                'proximos' => $proximosMetricas,
                'mesActual' => $mesActualMetricas,
                'totales' => $totalesMetricas,
            ],
        ]);
    }

    private function fechaReserva(Reserva $reserva): ?Carbon
    {
        if ($reserva->horario?->fecha) {
            return Carbon::parse($reserva->horario->fecha);
        }

        return $reserva->fechaReserva ? Carbon::parse($reserva->fechaReserva) : null;
    }

    private function metricasReservas($reservas, bool $incluirIngresos = false): array
    {
        $metricas = [
            'turnosTotales' => $reservas->count(),
            'turnosConfirmados' => $reservas->where('estado', self::ESTADO_CONFIRMADA)->count(),
            'turnosPendientes' => $reservas->where('estado', self::ESTADO_PENDIENTE)->count(),
            'turnosCancelados' => $reservas->where('estado', self::ESTADO_CANCELADA)->count(),
        ];

        if ($incluirIngresos) {
            $metricas['ingresosEstimados'] = round($reservas
                ->filter(fn (Reserva $reserva) => in_array($reserva->estado, self::ESTADOS_INGRESO_ESTIMADO, true))
                ->sum(fn (Reserva $reserva) => (float) ($reserva->servicio?->precio ?? 0)), 2);

            $metricas['ingresosConfirmados'] = round($reservas
                ->filter(fn (Reserva $reserva) => $this->cuentaComoIngresoConfirmado($reserva))
                ->sum(fn (Reserva $reserva) => (float) ($reserva->pago?->monto ?? 0)), 2);
        }

        return $metricas;
    }

    private function metricasTotales(int $idProfesional, $reservas): array
    {
        $serviciosQuery = Servicio::whereHas(
            'profesionales',
            fn ($query) => $query->where('profesionales.idUsuario', $idProfesional)
        );

        return [
            'serviciosActivos' => (clone $serviciosQuery)->where('activo', true)->count(),
            'serviciosTotales' => $serviciosQuery->count(),
            'reservasTotales' => $reservas->count(),
            'reservasCanceladas' => $reservas->where('estado', self::ESTADO_CANCELADA)->count(),
            'reservasCompletadas' => $reservas->where('estado', self::ESTADO_COMPLETADA)->count(),
        ];
    }

    private function cuentaComoIngresoConfirmado(Reserva $reserva): bool
    {
        return $reserva->pago?->estado === 'aprobado'
            && in_array($reserva->estado, self::ESTADOS_INGRESO_CONFIRMADO, true);
    }

    private function ensureCanViewProfessional(Request $request, int $idProfesional): void
    {
        $user = $request->user();
        $user?->loadMissing(['profesional', 'administrador']);

        if ($user && $user->administrador) {
            return;
        }

        if ($user && $user->profesional && (int) $user->idUsuario === $idProfesional) {
            return;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'No tenés permisos para consultar estas métricas.',
        ], 403));
    }
}
