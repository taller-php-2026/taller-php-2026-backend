<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicioComun extends Model
{
    protected $table = 'servicios';

    protected $fillable = [
        'idProfesional',
        'nombre',
        'descripcion',
        'precio',
        'duracionMinutos',
        'activo',
        'modalidad'
    ];
    
    public function servicio()
    {
    return $this->belongsTo(Servicio::class, 'idServicio');
    }
}
