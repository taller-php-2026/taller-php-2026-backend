<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciclo extends Model
{
    protected $table = 'ciclos';
    protected $primaryKey = 'idCiclo';
    protected $fillable = [
        'nombre'
    ];

    public function agenda()
    {
        return $this->hasMany(Agenda::class, 'idCiclo');
    }

    public function rangoHorarios()
    {
        return $this->hasMany(RangoHorario::class, 'idCiclo');
    }
}
