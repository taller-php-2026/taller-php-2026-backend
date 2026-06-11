<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table = 'servicios';
    protected $primaryKey = 'idServicio';
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'duracionMinutos',
        'activo',
        'modalidad',
        'idUbicacion',
        'idVideoSesion',
        'imagenUrl',
        'imagenPublicId',
    ];

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
        return $this->belongsTo(Ubicacion::class, 'idUbicacion');
    }
    public function servicioComun()
    {
        return $this->hasOne(ServicioComun::class, 'idServicio');
    }
    public function paqueteServicio()
    {
        return $this->hasOne(PaqueteServicio::class, 'idServicio');
    }
    public function videoSesion()
    {
        return $this->belongsTo(VideoSesion::class, 'idVideoSesion');
    }

    public function profesionales()
    {
        return $this->belongsToMany(
            Profesional::class,
            'profesionales_servicios',
            'idServicio',
            'idProfesional'
        );
    }

}
 