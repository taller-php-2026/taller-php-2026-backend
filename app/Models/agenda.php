<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Ciclo;
use App\Models\ReglaDisponibilidad;
use App\Models\ExcepcionDisponibilidad;

class Agenda extends Model
{
    protected $table = 'agendas';
    protected $primaryKey = 'idAgenda';
    protected $fillable = [
        'idCiclo'
    ];

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'idCiclo');
    }

    public function reglasDisponibilidad()
    {
        return $this->hasMany(ReglaDisponibilidad::class, 'idAgenda');
    }

    public function excepcionesDisponibilidad()
    {
        return $this->hasMany(ExcepcionDisponibilidad::class, 'idAgenda');
    }
}