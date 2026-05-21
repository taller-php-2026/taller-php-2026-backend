<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class notificaciones extends Model
{
    public function usuarios()
    {
        return $this->hasOne(usuarios::class, 'idUsuario');
    }
}
