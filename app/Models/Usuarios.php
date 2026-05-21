<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuarios extends Model
{
    public function cliente()
    {
        return $this->hasOne(clientes::class, 'idUsuario');
    }

    public function administrador()
    {
        return $this->hasOne(administradores::class, 'idUsuario');
    }

    public function profesional()
    {
        return $this->hasOne(profesionales::class, 'idUsuario');
    }

    public function notificaciones()
    {
        return $this->hasMany(notificaciones::class, 'idUsuario');
    }
}
