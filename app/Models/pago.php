<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'idPago',
        'idReserva',
        'monto',
        'metodo',
        'estado',
        'referenciaExterna',
        'fechaPago'
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'idReserva');
    }
}
