<?php

namespace App\Services;

use App\Models\ExcepcionDisponibilidad;
use App\Models\Profesional;
use App\Models\ReglaDisponibilidad;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

    public function getDisponibilidad(int $idProfesional, string $fecha): array
    {
        $profesional = Profesional::with('usuario')->findOrFail($idProfesional);

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

        $reservasActivas = Reserva::with('horario')
            ->where('idProfesional', $idProfesional)
            ->whereIn('estado', self::ESTADOS_ACTIVOS)
            ->whereHas('horario', fn ($q) => $q->where('fecha', $fecha))
            ->get();

        return [
            'profesional'     => $profesional,
            'fecha'           => $fecha,
            'dia_semana'      => $diaSemana,
            'reglas'          => $reglas,
            'excepciones'     => $excepciones,
            'reservas_activas' => $reservasActivas,
        ];
    }
}
