<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaqueteServicio extends Model
{
    public function servicio()
    {
        return $this->blongsTo(Servicio::class, 'idServicio');
    }
}
