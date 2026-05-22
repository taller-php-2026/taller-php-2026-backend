<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcepcionDisponibilidad extends Model
{
    protected $table = 'excepciones_disponibilidad';

    protected $fillable = [
        'idAgenda',
        'fecha',
        'horaInicio',
        'horaFin',
        'motivo',
        'disponible'
    ];
    
    public function agenda()
    {
        return $this->belongsTo(Agenda::class, 'idAgenda');
    }
}
