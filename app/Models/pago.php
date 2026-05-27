<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Reserva;

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
        return $this->hasOne(Reserva::class, 'idPago', 'idPago');
    }
}
