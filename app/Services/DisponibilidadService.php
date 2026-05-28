<?php

namespace App\Services;

use App\Models\ExcepcionDisponibilidad;
use App\Models\Profesional;
use App\Models\ReglaDisponibilidad;
use App\Models\Reserva;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DisponibilidadService
{
    private const DIAS_ES = [
        'Sunday'    => 'Domingo',
        'Monday'    => 'Lunes',
        'Tuesday'   => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday'  => 'Jueves',
        'Friday'    => 'Viernes',
        'Saturday'  => 'Sábado',
    ];

    private const ESTADOS_ACTIVOS = ['pendiente', 'confirmada', 'enCurso'];

    public function getDisponibilidad(int $idProfesional, string $fecha, int $idServicio, ?int $reservaIgnoradaId = null): array
    {
        $profesional = Profesional::with('usuario')->findOrFail($idProfesional);
        $servicio    = Servicio::findOrFail($idServicio);

        $fechaCarbon = Carbon::parse($fecha);
        $diaSemana   = self::DIAS_ES[$fechaCarbon->format('l')];

        $reglas = ReglaDisponibilidad::with('agenda')
            ->where('idProfesional', $idProfesional)
            ->where('dia_semana', $diaSemana)
            ->where('activa', true)
            ->get();

        $agendaIds = $reglas->pluck('idAgenda')->filter()->unique()->values();

        $excepciones = ExcepcionDisponibilidad::whereIn('idAgenda', $agendaIds)
            ->where('fecha', $fecha)
            ->get();

        $queryReservas = Reserva::with('horario')
            ->where('idProfesional', $idProfesional)
            ->whereIn('estado', self::ESTADOS_ACTIVOS)
            ->whereHas('horario', fn ($q) => $q->where('fecha', $fecha));

        if ($reservaIgnoradaId !== null) {
            $queryReservas->where('idReserva', '!=', $reservaIgnoradaId);
        }

        $reservasActivas = $queryReservas->get();

        $duracion = (int) $servicio->duracionMinutos;

        return [
            'profesional'          => $profesional,
            'servicio'             => $servicio,
            'fecha'                => $fecha,
            'dia_semana'           => $diaSemana,
            'reglas'               => $reglas,
            'excepciones'          => $excepciones,
            'reservas_activas'     => $reservasActivas,
            'slots_disponibles'    => $this->generarSlotsDisponibles($reglas, $excepciones, $reservasActivas, $fecha, $duracion, $idServicio),
            'bloqueos_reservas'    => $this->construirBloqueosReservas($reservasActivas, $fecha),
            'bloqueos_excepciones' => $this->construirBloqueosExcepciones($excepciones),
        ];
    }

    private function generarSlotsDisponibles(
        Collection $reglas,
        Collection $excepciones,
        Collection $reservasActivas,
        string $fecha,
        int $duracion,
        int $idServicio
    ): array {
        $slots = [];

        foreach ($reglas as $regla) {
            $buffer = (int) ($regla->bufferMinutos ?? 0);
            $avance = $duracion + $buffer;

            $cursor = Carbon::parse("{$fecha} {$regla->horaInicio}");
            $fin    = Carbon::parse("{$fecha} {$regla->horaFin}");

            while ($cursor->copy()->addMinutes($duracion)->lte($fin)) {
                $slotInicio = $cursor->copy();
                $slotFin    = $cursor->copy()->addMinutes($duracion);

                if (!$this->solapaConReservas($slotInicio, $slotFin, $reservasActivas, $fecha)
                    && !$this->solapaConExcepciones($slotInicio, $slotFin, $excepciones, $fecha)) {
                    $slots[] = [
                        'horaInicio'      => $slotInicio->format('H:i'),
                        'horaFin'         => $slotFin->format('H:i'),
                        'idRegla'         => $regla->idRegla,
                        'idServicio'      => $idServicio,
                        'duracionMinutos' => $duracion,
                    ];
                }

                $cursor->addMinutes($avance);
            }
        }

        return $slots;
    }

    private function solapaConReservas(Carbon $inicio, Carbon $fin, Collection $reservasActivas, string $fecha): bool
    {
        foreach ($reservasActivas as $reserva) {
            $horario = $reserva->horario;
            if (!$horario) {
                continue;
            }
            $resInicio = Carbon::parse("{$fecha} {$horario->horaInicio}");
            $resFin    = Carbon::parse("{$fecha} {$horario->horaFin}");

            if ($inicio->lt($resFin) && $fin->gt($resInicio)) {
                return true;
            }
        }
        return false;
    }

    private function solapaConExcepciones(Carbon $inicio, Carbon $fin, Collection $excepciones, string $fecha): bool
    {
        foreach ($excepciones as $excepcion) {
            if (is_null($excepcion->horaInicio) || is_null($excepcion->horaFin)) {
                return true; // día completo bloqueado
            }
            $excInicio = Carbon::parse("{$fecha} {$excepcion->horaInicio}");
            $excFin    = Carbon::parse("{$fecha} {$excepcion->horaFin}");

            if ($inicio->lt($excFin) && $fin->gt($excInicio)) {
                return true;
            }
        }
        return false;
    }

    private function construirBloqueosReservas(Collection $reservasActivas, string $fecha): array
    {
        $bloqueos = [];

        foreach ($reservasActivas as $reserva) {
            $horario = $reserva->horario;
            $bloqueos[] = [
                'idReserva'  => $reserva->idReserva,
                'estado'     => $reserva->estado,
                'horaInicio' => $horario ? $horario->horaInicio : null,
                'horaFin'    => $horario ? $horario->horaFin : null,
                'idHorario'  => $horario ? $horario->idHorario : null,
            ];
        }

        return $bloqueos;
    }

    private function construirBloqueosExcepciones(Collection $excepciones): array
    {
        $bloqueos = [];

        foreach ($excepciones as $excepcion) {
            $diaCompleto = is_null($excepcion->horaInicio) || is_null($excepcion->horaFin);
            $bloqueos[] = [
                'idExcepcion' => $excepcion->idExcepcion,
                'fecha'       => $excepcion->fecha,
                'horaInicio'  => $excepcion->horaInicio,
                'horaFin'     => $excepcion->horaFin,
                'motivo'      => $excepcion->motivo ?? null,
                'idAgenda'    => $excepcion->idAgenda,
                'diaCompleto' => $diaCompleto,
            ];
        }

        return $bloqueos;
    }
}
