<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reservas';
    protected $primaryKey = 'idReserva';
    protected $fillable = [
        'idReserva',
        'idCliente',
        'idServicio',
        'idHorario',
        'fechaReserva',
        'estado',
        'comentarios'
    ];

    public function pago()
    {
    return $this->belongsTo(Pago::class, 'idPago', 'idPago');
    }

    public function profesional()
    {
         return $this->belongsTo(Profesional::class, 'idProfesional', 'idUsuario');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idCliente');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'idServicio');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'idHorario', 'idHorario');
    }
}
