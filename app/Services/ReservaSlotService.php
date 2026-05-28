<?php

namespace App\Services;

use App\Models\Horario;
use App\Models\Profesional;
use App\Models\Reserva;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class ReservaSlotService
{
    public function __construct(private DisponibilidadService $disponibilidadService) {}

    public function reservar(
        int $idProfesional,
        int $idCliente,
        int $idServicio,
        string $fecha,
        string $horaInicio
    ): array {
        Profesional::findOrFail($idProfesional);

        $disponibilidad = $this->disponibilidadService->getDisponibilidad(
            $idProfesional,
            $fecha,
            $idServicio
        );

        $slot = collect($disponibilidad['slots_disponibles'])
            ->firstWhere('horaInicio', $horaInicio);

        if (!$slot) {
            throw new HttpResponseException(
                response()->json(
                    ['message' => 'El slot solicitado no está disponible para reservar.'],
                    409
                )
            );
        }

        $horaFin = $slot['horaFin'];

        return DB::transaction(function () use ($idProfesional, $idCliente, $idServicio, $fecha, $horaInicio, $horaFin) {
            $horario = Horario::create([
                'fecha'      => $fecha,
                'horaInicio' => $horaInicio,
                'horaFin'    => $horaFin,
            ]);

            $reserva = Reserva::create([
                'idCliente'    => $idCliente,
                'idProfesional' => $idProfesional,
                'idServicio'   => $idServicio,
                'idHorario'    => $horario->idHorario,
                'fechaReserva' => "{$fecha} {$horaInicio}:00",
                'estado'       => 'pendiente',
            ]);

            return [
                'reserva' => $reserva,
                'horario' => $horario,
            ];
        });
    }
}
