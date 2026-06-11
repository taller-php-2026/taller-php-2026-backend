<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Reserva;
use App\Models\PaqueteComprado;

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

    public function paqueteComprado()
    {
        return $this->hasOne(PaqueteComprado::class, 'idPago', 'idPago');
    }
}
