<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';
    protected $primaryKey = 'idUbicacion';
    protected $fillable = [
        'idUbicacion',
        'idServicio',
        'direccion',
        'ciudad',
        'pais',
        'latitud',
        'longitud'
    ];
    
   public function servicio()
    {
        return $this->hasOne(Servicio::class, 'idUbicacion');
    }
}
