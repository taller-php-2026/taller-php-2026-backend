<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoSesion extends Model
{
  public function servicio()
    {
    return $this->belongsTo(Servicio::class, 'idServicio');
    }
}
