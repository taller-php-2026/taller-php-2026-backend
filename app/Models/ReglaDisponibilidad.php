<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Agenda;
use App\Models\Profesional;

class ReglaDisponibilidad extends Model
{
    protected $table = 'reglas_disponibilidad';
    protected $primaryKey = 'idRegla';
    protected $fillable = [
        'dia_semana',
        'horaInicio',
        'horaFin',
        'pausaMinutos',
        'bufferMinutos',
        'activa',
        'idAgenda',
        'idProfesional'
    ];

    public function agenda()
    {
        return $this->belongsTo(Agenda::class, 'idAgenda');
    }

    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'idProfesional', 'idUsuario');
    }
}
