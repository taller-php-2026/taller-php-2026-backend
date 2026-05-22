<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';

    protected $fillable = [
        'idServicio',
        'direccion',
        'ciudad',
        'latitud',
        'longitud'
    ];
    
   public function servicio()
    {
    return $this->belongsTo(Servicio::class, 'idServicio');
    }
}
