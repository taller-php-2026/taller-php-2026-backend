<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoSesion extends Model
{
    protected $table = 'video_sesiones';
    protected $primaryKey = 'idVideoSesion';

    protected $fillable = [
        'proveedor',
        'url',
        'nombreSala',
        'fechaHoraInicio',
        'fechaHoraFin',
        'estado',
    ];

    public function reserva()
    {
        return $this->hasOne(Reserva::class, 'idVideoSesion', 'idVideoSesion');
    }
}
