<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciclo extends Model
{
    public function agenda()
    {
        return $this->hasMany(Agenda::class, 'idCiclo');
    }
}
