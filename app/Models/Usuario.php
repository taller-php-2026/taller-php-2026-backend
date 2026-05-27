<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'idUsuario';
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'telefono',
        'activo'
    ];

    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'idUsuario');
    }

    public function administrador()
    {
        return $this->hasOne(Administrador::class, 'idUsuario');
    }

    public function profesional()
    {
        return $this->hasOne(Profesional::class, 'idUsuario');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'idUsuario');
    }
}
