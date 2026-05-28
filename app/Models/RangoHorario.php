<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Ciclo;

class RangoHorario extends Model
{
    protected $table = 'rango_horarios';
    protected $primaryKey = 'idRango';
    protected $fillable = [
        'diaSemana',
        'horaInicio',
        'horaFin',
        'idCiclo'
    ];

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'idCiclo');
    }
}
