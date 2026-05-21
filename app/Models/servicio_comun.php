<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicioComun extends Model
{
    public function servicio()
    {
    return $this->belongsTo(Servicio::class, 'idServicio');
    }
}
