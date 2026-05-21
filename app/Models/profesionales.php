<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class profesionales extends Model
{
    public function usuario()
    {
        return $this->hasOne(usuarios::class, 'idUsuario');
    }
}
