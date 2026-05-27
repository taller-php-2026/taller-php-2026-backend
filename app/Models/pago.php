<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';
    protected $primaryKey = 'idPago';
    protected $fillable = [
        'monto',
        'metodoPago',
        'estado',
        'referenciaExterna',
        'fechaPago'
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'idReserva');
    }
}
