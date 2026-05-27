<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicioComun extends Model
{
    protected $table = 'servicios_comunes';
    protected $primaryKey = 'idServicioComun';
    protected $fillable = [
        'idServicio'
    ];

    public function servicio()
    {
    return $this->belongsTo(Servicio::class, 'idServicio');
    }
}
