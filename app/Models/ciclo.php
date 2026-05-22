<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciclo extends Model
{
    protected $table = 'ciclos';
    protected $primaryKey = 'idCiclos';
    protected $fillable = [
        'idCiclos',
        'nombre'
    ];

    public function agenda()
    {
        return $this->hasMany(Agenda::class, 'idCiclo');
    }
}
