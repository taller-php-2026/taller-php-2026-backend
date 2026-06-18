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
        if ($idReserva !== null) {
            $notificacionExistente = Notificacion::where('idUsuario', $idUsuario)
                ->where('idReserva', $idReserva)
                ->where('tipo', $tipo)
                ->where('titulo', $titulo)
                ->first();

            if ($notificacionExistente) {
                return;
            }
        }

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
