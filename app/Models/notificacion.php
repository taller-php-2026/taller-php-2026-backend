<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'idUsuario',
        'titulo',
        'mensaje',
        'tipo',
        'leida',
        'enviadaPorEmail',
        'fechaCreacion',
        'fechaLectura'
    ];
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }
}
