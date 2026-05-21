<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    public function pago()
    {
        return $this->hasOne(Pago::class, 'idPago');
    }
}
