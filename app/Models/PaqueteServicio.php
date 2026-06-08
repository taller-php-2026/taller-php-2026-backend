<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaqueteServicio extends Model
{
    protected $table = 'paquetes_servicios';
    protected $primaryKey = 'idPaqueteServicio';
    protected $fillable = [
        'idServicio',
        'totalSesiones',
        'precio',
        'activo',
        'imagenUrl',
        'imagenPublicId',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'idServicio', 'idServicio');
    }

    public function paquetesComprados()
    {
        return $this->hasMany(PaqueteComprado::class, 'idPaqueteServicio', 'idPaqueteServicio');
    }
}
