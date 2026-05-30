<?php

namespace App\Services;

use App\Jobs\EnviarEmailNotificacion;
use App\Models\Notificacion;

class NotificacionService
{
    public function notificar(
        int $idUsuario,
        string $email,
        string $titulo,
        string $mensaje,
        string $tipo
    ): void {
        $notificacion = Notificacion::create([
            'idUsuario'     => $idUsuario,
            'titulo'        => $titulo,
            'mensaje'       => $mensaje,
            'tipo'          => $tipo,
            'leida'         => false,
            'enviadaMail'   => false,
            'fechaCreacion' => now(),
        ]);

        EnviarEmailNotificacion::dispatch(
            $email,
            $titulo,
            $mensaje,
            $notificacion->idNotificacion
        )->afterCommit();
    }
}
