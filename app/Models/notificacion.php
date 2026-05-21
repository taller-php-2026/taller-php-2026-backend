<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }
}
