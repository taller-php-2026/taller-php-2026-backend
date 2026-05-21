<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrador extends Model
{
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }
}
