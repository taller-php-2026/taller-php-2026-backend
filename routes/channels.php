<?php

use App\Models\Administrador;
use App\Models\Usuario;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Broadcast::channel('agenda-profesional.{idProfesional}', function (Usuario $user, int $idProfesional) {
    if ((int) $user->idUsuario === $idProfesional) {
        return true;
    }
    return Administrador::where('idUsuario', $user->idUsuario)->exists();
});
