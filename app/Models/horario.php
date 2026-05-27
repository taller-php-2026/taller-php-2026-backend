<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = 'horarios';
    protected $primaryKey = 'idHorario';
    protected $fillable = [
        'fecha',
        'horaInicio',
        'horaFin'
    ];

    public function agenda()
    {
        return $this->belongsTo(Agenda::class, 'idAgenda');
    }

    public function reserva()
    {
        return $this->hasOne(Reserva::class, 'idHorario');  
    }

}
