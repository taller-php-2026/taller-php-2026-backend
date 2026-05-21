<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profesional extends Model
{
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }
    
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'idProfesional');
    }


}
