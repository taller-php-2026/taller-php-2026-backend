<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoSesion extends Model{
  protected $table = 'video_sesiones';
  protected $primaryKey = 'idVideoSesion';

  protected $fillable = [
    'idVideoSesion',
    'idServicio',
    'proveedor',
    'urlAcceso',
    'nombreSala',
    'estado',
    'inicio',
    'fin'
  ];
    public function servicio()
    {
      return $this->belongsTo(Servicio::class, 'idServicio');
    }
}
