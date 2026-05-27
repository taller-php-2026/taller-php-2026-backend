<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;
use App\Models\Resena;
use App\Models\Reserva;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'idUsuario';
    
    protected $fillable = [
        'idUsuario'
    ];
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }

    public function resena()
    {
        return $this->hasMany(Resena::class, 'idCliente');
    }

    public function reserva()
    {
        return $this->hasMany(Reserva::class, 'idCliente');
    }
}
