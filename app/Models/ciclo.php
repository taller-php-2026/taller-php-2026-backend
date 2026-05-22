<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciclo extends Model
{
    protected $table = 'ciclos';

    protected $fillable = [
        'id',
        'nombre'
    ];

    public function agenda()
    {
        return $this->hasMany(Agenda::class, 'idCiclo');
    }
}
