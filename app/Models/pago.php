<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'idReserva');
    }
}
