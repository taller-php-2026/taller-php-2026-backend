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
    public function __construct(private NotificacionService $notificacionService) {}

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
            // Aceptar estados 'aprobado' o 'pendiente' (para Mercado Pago)
            $estadoPago = $data['estado'] ?? 'aprobado';
            if (!in_array($estadoPago, ['aprobado', 'pendiente'])) {
                $estadoPago = 'aprobado';
            }

            $pago = Pago::create([
                'monto'             => $paqueteComprado->precioCompra,
                'metodoPago'        => $data['metodoPago'],
                'estado'            => $estadoPago,
                'fechaPago'         => now(),
                'referenciaExterna' => $data['referenciaExterna'] ?? null,
            ]);

            $paqueteComprado->idPago = $pago->idPago;
            
            // Solo activar si estado es aprobado
            if ($estadoPago === 'aprobado') {
                $paqueteComprado->estado = 'activo';
                
                $fresh = $paqueteComprado->fresh(self::WITH_RELATIONS);
                $servicio = $fresh->paqueteServicio->servicio->nombre ?? 'No especificado';

                $this->notificacionService->notificar(
                    $fresh->idCliente,
                    $fresh->cliente->usuario->email,
                    'Paquete activado',
                    "Tu paquete fue activado correctamente.\n\nServicio: {$servicio}\nSesiones totales: {$fresh->totalSesiones}\nSesiones restantes: {$fresh->sesionesRestantes}\nPrecio pagado: \${$fresh->precioCompra}",
                    'confirmacion'
                );
            }
            
            $paqueteComprado->save();

            $fresh = $paqueteComprado->fresh(self::WITH_RELATIONS);

            return [
                'paqueteComprado' => $fresh,
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
