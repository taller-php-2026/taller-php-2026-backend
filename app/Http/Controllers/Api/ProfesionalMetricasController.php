<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProfesionalMetricasController extends Controller
{
    /**
     * Obtener metricas de reservas del dia de hoy para un profesional.
     */
    public function metricas(Request $request, $id)
    {
        // Obtener la fecha de hoy en formato Y-m-d.
        $hoy = Carbon::today()->toDateString();

        // Obtener todas las reservas de hoy para este profesional.
        $reservasHoy = Reserva::where('idProfesional', $id)
            ->whereDate('fechaReserva', $hoy)
            ->get();

        $turnosTotales = $reservasHoy->count();
        $turnosConfirmados = $reservasHoy->where('estado', 'confirmada')->count();
        $turnosPendientes = $reservasHoy->where('estado', 'pendiente')->count();

        // Calcular los ingresos estimados sumando los precios de los servicios asociados.
        // Se calculan sobre turnos confirmados y completados para estimar ingresos reales/esperados.
        $ingresosEstimados = 0;
        foreach ($reservasHoy as $reserva) {
            if (in_array($reserva->estado, ['confirmada', 'completada', 'enCurso', 'pendiente'])) {
                // Obtener el precio del servicio.
                $servicio = Servicio::find($reserva->idServicio);
                if ($servicio) {
                    $ingresosEstimados += (float) $servicio->precio;
                }
            }
        }

        return response()->json([
            'message' => 'Métricas del profesional obtenidas correctamente',
            'data' => [
                'turnosTotales' => $turnosTotales,
                'turnosConfirmados' => $turnosConfirmados,
                'turnosPendientes' => $turnosPendientes,
                'ingresosEstimados' => $ingresosEstimados,
            ]
        ]);
    }
}
