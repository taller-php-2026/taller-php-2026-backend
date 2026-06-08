<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrador extends Model
{
    protected $table = 'administradores';
    protected $primaryKey = 'idUsuario';
    
    protected $fillable = [
        'idUsuario',
        'nivelAcceso'
    ];
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }
}
