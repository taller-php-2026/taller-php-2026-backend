<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RangoHorario extends Model
{
    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'idCiclo');
    }
}
