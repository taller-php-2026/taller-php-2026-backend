<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'id_cliente');
    }
}
