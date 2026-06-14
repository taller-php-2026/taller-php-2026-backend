<?php
// Notificacion creada event.

namespace App\Events;

use App\Models\Notificacion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificacionCreada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        private readonly Notificacion $notificacion
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('notificaciones.' . $this->notificacion->idUsuario);
    }

    public function broadcastAs(): string
    {
        return 'NotificacionCreada';
    }

    public function broadcastWith(): array
    {
        return [
            'idNotificacion' => (int) $this->notificacion->idNotificacion,
            'idUsuario'      => (int) $this->notificacion->idUsuario,
            'titulo'         => $this->notificacion->titulo,
            'mensaje'        => $this->notificacion->mensaje,
            'tipo'           => $this->notificacion->tipo,
            'leida'          => (bool) $this->notificacion->leida,
            'idReserva'      => $this->notificacion->idReserva ? (int) $this->notificacion->idReserva : null,
            'fechaCreacion'  => $this->notificacion->fechaCreacion,
        ];
    }
}
