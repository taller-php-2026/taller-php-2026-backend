<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
  
    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'idProfesional');
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'idAgenda');
    }

    public function reglasDisponibilidad()
    {
        return $this->hasMany(ReglaDisponibilidad::class, 'idAgenda');
    }

    public function excepcionesDisponibilidad()
    {
        return $this->hasMany(ExcepcionDisponibilidad::class, 'idAgenda');
    }

     public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'idCiclo');
    }
}