<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    public function Usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }

    public function resena()
    {
        return $this->hasMany(Resena::class, 'idCliente');
    }
}
