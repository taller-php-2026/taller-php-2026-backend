<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaqueteServicio extends Model
{
    protected $table = 'paquetes_servicio';

    protected $fillable = [
        'idServicio',
        'totalSesiones',
        'precio',
        'activo'
    ];
    
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'idServicio');
    }
}
