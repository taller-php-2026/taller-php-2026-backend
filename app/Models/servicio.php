<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'idProfesional');
    }
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'idServicio');
    }
    public function ubicacion()
    {
        return $this->hasOne(Ubicacion::class, 'idServicio');
    }
    public function servicioComun()
    {
        return $this->hasOne(ServicioComun::class, 'idServicio');
    }
    public function paqueteServicio()
    {
        return $this->hasOne(PaqueteServicio::class, 'idServicio');
    }
}
 