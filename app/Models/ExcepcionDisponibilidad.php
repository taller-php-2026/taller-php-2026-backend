<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Agenda;

class ExcepcionDisponibilidad extends Model
{
    protected $table = 'excepciones_disponibilidad';
    protected $primaryKey = 'idExcepcion';
    protected $fillable = [
        'idAgenda',
        'fecha',
        'horaInicio',
        'horaFin',
        'motivo'
    ];
    
    public function agenda()
    {
        return $this->belongsTo(Agenda::class, 'idAgenda');
    }
}
