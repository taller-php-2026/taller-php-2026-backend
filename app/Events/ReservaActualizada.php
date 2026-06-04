<?php

namespace App\Events;

use App\Models\Reserva;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservaActualizada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        private readonly Reserva $reserva,
        private readonly string $accion
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('agenda-profesional.' . $this->reserva->idProfesional);
    }

    public function broadcastAs(): string
    {
        return 'ReservaActualizada';
    }

    public function broadcastWith(): array
    {
        $reserva  = $this->reserva;
        $horario  = $reserva->relationLoaded('horario')  ? $reserva->horario  : null;
        $servicio = $reserva->relationLoaded('servicio') ? $reserva->servicio : null;
        $cliente  = $reserva->relationLoaded('cliente') && $reserva->cliente?->relationLoaded('usuario')
            ? $reserva->cliente->usuario
            : null;

        return [
            'accion'         => $this->accion,
            'idReserva'      => (int) $reserva->idReserva,
            'estado'         => $reserva->estado,
            'fechaReserva'   => $reserva->fechaReserva,
            'idProfesional'  => (int) $reserva->idProfesional,
            'idCliente'      => (int) $reserva->idCliente,
            'idServicio'     => (int) $reserva->idServicio,
            'servicioNombre' => $servicio?->nombre    ?? null,
            'clienteNombre'  => $cliente?->nombre     ?? null,
            'horarioInicio'  => $horario?->horaInicio ?? null,
            'horarioFin'     => $horario?->horaFin    ?? null,
        ];
    }
}
