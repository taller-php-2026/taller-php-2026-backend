<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reservas';
    protected $primaryKey = 'idReserva';
    protected $fillable = [
        'idCliente',
        'idProfesional',
        'idServicio',
        'idHorario',
        'idPago',
        'idPaqueteComprado',
        'fechaReserva',
        'estado',
        'comentarios',
        'recordatorio48hEnviado',
        'idVideoSesion',
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

    public function paqueteComprado()
    {
        return $this->belongsTo(PaqueteComprado::class, 'idPaqueteComprado', 'idPaqueteComprado');
    }

    public function videoSesion()
    {
        return $this->belongsTo(VideoSesion::class, 'idVideoSesion', 'idVideoSesion');
    }

    // Obtener reseña asociada.
    public function resena()
    {
        return $this->hasOne(Resena::class, 'idReserva', 'idReserva');
    }
}

