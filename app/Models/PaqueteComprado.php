<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaqueteComprado extends Model
{
    protected $table = 'paquetes_comprados';
    protected $primaryKey = 'idPaqueteComprado';

    protected $fillable = [
        'idPaqueteServicio',
        'idCliente',
        'idPago',
        'totalSesiones',
        'sesionesUsadas',
        'sesionesRestantes',
        'precioCompra',
        'estado',
        'fechaCompra',
    ];

    protected $casts = [
        'fechaCompra' => 'datetime',
    ];

    public function paqueteServicio()
    {
        return $this->belongsTo(PaqueteServicio::class, 'idPaqueteServicio', 'idPaqueteServicio');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idCliente', 'idUsuario');
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'idPago', 'idPago');
    }
}
