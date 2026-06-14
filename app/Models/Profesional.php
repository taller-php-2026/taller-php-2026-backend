<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profesional extends Model
{
    protected $table = 'profesionales';
    protected $primaryKey = 'idUsuario';
    protected $fillable = [
        'idUsuario',
        'nombreNegocio',
        'descripcion',
        'ratingPromedio',
        'color'
    ];
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }
    
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'idProfesional');
    }

    public function agendas()
    {
        return $this->hasMany(Agenda::class, 'idProfesional');
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class, 'idProfesional');
    }

}
