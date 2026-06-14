<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    protected $primaryKey = 'idNotificacion';
    
    protected $fillable = [
        'idUsuario',
        'titulo',
        'mensaje',
        'tipo',
        'leida',
        'enviadaMail',
        'fechaCreacion',
        'fechaLectura',
        'idReserva',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }
}
