<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class clientes extends Model
{
    public function usuario()
    {
        return $this->hasOne(usuarios::class, 'idUsuario');
    }

    public function resenas()
    {
        return $this->hasMany(resenas::class, 'idCliente');
    }
}
