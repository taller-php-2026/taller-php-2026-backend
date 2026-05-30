<?php

namespace App\Services;

use App\Models\PaqueteComprado;
use App\Models\PaqueteServicio;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaqueteCompradoService
{
    private const WITH_RELATIONS = [
        'paqueteServicio.servicio',
        'pago',
        'cliente.usuario',
    ];

    public function comprar(int $idPaqueteServicio, array $data): PaqueteComprado
    {
        $paqueteServicio = PaqueteServicio::findOrFail($idPaqueteServicio);

        if (!$paqueteServicio->activo) {
            throw new HttpException(409, 'El paquete de servicio no está activo.');
        }

        return DB::transaction(function () use ($paqueteServicio, $data) {
            $totalSesiones = (int) $paqueteServicio->totalSesiones;

            $paqueteComprado = PaqueteComprado::create([
                'idPaqueteServicio' => $paqueteServicio->idPaqueteServicio,
                'idCliente'         => $data['idCliente'],
                'idPago'            => null,
                'totalSesiones'     => $totalSesiones,
                'sesionesUsadas'    => 0,
                'sesionesRestantes' => $totalSesiones,
                'precioCompra'      => $paqueteServicio->precio,
                'estado'            => 'pendiente',
                'fechaCompra'       => now(),
            ]);

            return $paqueteComprado->fresh(self::WITH_RELATIONS);
        });
    }

    public function pagar(int $idPaqueteComprado, array $data): array
    {
        $paqueteComprado = PaqueteComprado::with(self::WITH_RELATIONS)->findOrFail($idPaqueteComprado);

        if ($paqueteComprado->estado !== 'pendiente') {
            throw new HttpException(409, 'Solo se puede pagar un paquete pendiente.');
        }

        return DB::transaction(function () use ($paqueteComprado, $data) {
            $pago = Pago::create([
                'monto'             => $paqueteComprado->precioCompra,
                'metodoPago'        => $data['metodoPago'],
                'estado'            => 'aprobado',
                'fechaPago'         => now(),
                'referenciaExterna' => $data['referenciaExterna'] ?? null,
            ]);

            $paqueteComprado->idPago = $pago->idPago;
            $paqueteComprado->estado = 'activo';
            $paqueteComprado->save();

            return [
                'paqueteComprado' => $paqueteComprado->fresh(self::WITH_RELATIONS),
                'pago'            => $pago,
            ];
        });
    }

    public function getByCliente(int $idCliente): Collection
    {
        return PaqueteComprado::with(self::WITH_RELATIONS)
            ->where('idCliente', $idCliente)
            ->get();
    }

    public function getById(int $idPaqueteComprado): PaqueteComprado
    {
        return PaqueteComprado::with(self::WITH_RELATIONS)->findOrFail($idPaqueteComprado);
    }
}
