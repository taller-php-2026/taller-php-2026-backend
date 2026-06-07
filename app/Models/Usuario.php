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

    public function getRolesAttribute(): array
    {
        $roles = [];

        if ($this->relationLoaded('administrador') ? (bool) $this->administrador : $this->administrador()->exists()) {
            $roles[] = 'administrador';
        }
        if ($this->relationLoaded('profesional') ? (bool) $this->profesional : $this->profesional()->exists()) {
            $roles[] = 'profesional';
        }
        if ($this->relationLoaded('cliente') ? (bool) $this->cliente : $this->cliente()->exists()) {
            $roles[] = 'cliente';
        }

        return $roles;
    }

    public function getTipoPrincipalAttribute(): ?string
    {
        foreach (['administrador', 'profesional', 'cliente'] as $prioridad) {
            if (in_array($prioridad, $this->getRolesAttribute())) {
                return $prioridad;
            }
        }

        return null;
    }
}
