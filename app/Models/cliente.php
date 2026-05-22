<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'idUsuario'
    ];
    
    public function usuario()
    {
        return $this->belongsTo(usuario::class, 'idUsuario');
    }

    public function resena()
    {
        return $this->hasMany(resena::class, 'idCliente');
    }

    public function reserva()
    {
        return $this->hasMany(reserva::class, 'idCliente');
    }
}
