<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class resenas extends Model
{
    public function cliente()
    {
        return $this->hasOne(clientes::class, 'id_cliente');
    }
}
