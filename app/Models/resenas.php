<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    protected $table = 'resenas';

    protected $fillable = [
        'idReserva',
        'idCliente',
        'idProfesional',
        'puntuacion',
        'comentario',
        'fechaCreacion'
    ];

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'id_cliente');
    }
    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'idProfesional');
    }
}
