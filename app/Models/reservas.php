<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class reservas extends Model
{
    public function pago()
    {
        return $this->hasOne(pagos::class, 'idPago');
    }
}
