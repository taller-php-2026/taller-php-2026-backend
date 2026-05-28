<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'usuarios';
    protected $primaryKey = 'idUsuario';
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'telefono',
        'activo'
    ];

    protected $hidden = ['password'];

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
