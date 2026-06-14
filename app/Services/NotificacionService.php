<?php
// Servicio de notificaciones.

namespace App\Services;

use App\Events\NotificacionCreada;
use App\Jobs\EnviarEmailNotificacion;
use App\Models\Notificacion;

class NotificacionService
{
    public function notificar(
        int $idUsuario,
        string $email,
        string $titulo,
        string $mensaje,
        string $tipo,
        ?int $idReserva = null
    ): void {
        $notificacion = Notificacion::create([
            'idUsuario'     => $idUsuario,
            'titulo'        => $titulo,
            'mensaje'       => $mensaje,
            'tipo'          => $tipo,
            'leida'         => false,
            'enviadaMail'   => false,
            'fechaCreacion' => now(),
            'idReserva'     => $idReserva,
        ]);

        EnviarEmailNotificacion::dispatch(
            $email,
            $titulo,
            $mensaje,
            $notificacion->idNotificacion
        )->afterCommit();

        broadcast(new NotificacionCreada($notificacion));
    }
}
