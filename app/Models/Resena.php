<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    protected $table = 'resenas';
    protected $primaryKey = 'idResena';
    protected $fillable = [
        'calificacion',
        'comentario',
        'fecha',
        'idProfesional',
        'idCliente',
        'idReserva',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idCliente', 'idUsuario');
    }

    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'idProfesional', 'idUsuario');
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'idReserva', 'idReserva');
    }
}