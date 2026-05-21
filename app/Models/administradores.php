<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class administradores extends Model
{
    public function usuarios()
    {
        return $this->hasOne(usuarios::class, 'idUsuario');
    }
}
