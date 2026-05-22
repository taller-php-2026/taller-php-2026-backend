<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RangoHorario extends Model
{
    protected $table = 'rangos_horario';

    protected $fillable = [
        'idCiclo',
        'diaSemana',
        'horaInicio',
        'horaFin'
    ];
    
    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'idCiclo');
    }
}
