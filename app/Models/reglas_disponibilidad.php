<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReglaDisponibilidad extends Model
{
    protected $table = 'reglas_disponibilidad';

    protected $fillable = [
        'idAgenda',
        'diaSemana',
        'horaInicio',
        'horaFin',
        'pausaMinutos',
        'duracionMinutos',
        'activo'
    ];

    public function agenda()
    {
        return $this->belongsTo(Agenda::class, 'idAgenda');
    }
}
