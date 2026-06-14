<?php

namespace App\Services;

use App\Models\PaqueteComprado;
use App\Models\PaqueteServicio;
use App\Models\Pago;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaqueteCompradoService
{
    public function __construct(private NotificacionService $notificacionService) {}

    private const WITH_RELATIONS = [
        'paqueteServicio.servicio.ubicacion',
        'paqueteServicio.servicio.profesionales.usuario',
        'pago',
        'cliente.usuario',
    ];

    public function comprar(int $idPaqueteServicio, int $idCliente): PaqueteComprado
    {
        $paqueteServicio = PaqueteServicio::with('servicio')->findOrFail($idPaqueteServicio);

        if (!$paqueteServicio->activo || ! $paqueteServicio->servicio?->activo) {
            throw new HttpException(409, 'El paquete de servicio no está activo.');
        }

        return DB::transaction(function () use ($paqueteServicio, $idCliente) {
            $totalSesiones = (int) $paqueteServicio->totalSesiones;

            $paqueteComprado = PaqueteComprado::create([
                'idPaqueteServicio' => $paqueteServicio->idPaqueteServicio,
                'idCliente'         => $idCliente,
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

    public function assertClienteOwner(PaqueteComprado $paqueteComprado, Usuario $usuario): void
    {
        $usuario->loadMissing('cliente');

        if ($usuario->cliente && (int) $paqueteComprado->idCliente === (int) $usuario->idUsuario) {
            return;
        }

        throw new HttpException(403, 'No tenes permisos para operar este paquete comprado.');
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
