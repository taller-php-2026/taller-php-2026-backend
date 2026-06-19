<?php

namespace App\Services;

use App\Events\ReservaActualizada;
use App\Models\Horario;
use App\Models\Pago;
use App\Models\PaqueteComprado;
use App\Models\Reserva;
use App\Models\Resena;
use App\Models\Profesional;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReservaService
{
    public function __construct(
        private DisponibilidadService $disponibilidadService,
        private NotificacionService $notificacionService
    ) {}
    private const ESTADOS_ACTIVOS = ['pendiente', 'confirmada', 'enCurso'];

    private const WITH_RELATIONS = [
        'cliente.usuario',
        'profesional.usuario',
        'servicio.ubicacion',
        'pago',
        'horario',
        'paqueteComprado.paqueteServicio.servicio.ubicacion',
    ];

    public function getAll(): Collection
    {
        return Reserva::with(self::WITH_RELATIONS)->get();
    }

    public function getById(int $id): Reserva
    {
        return Reserva::with(self::WITH_RELATIONS)->findOrFail($id);
    }

    public function authorizeReservaAction(Reserva $reserva, Usuario $usuario, string $action): void
    {
        $usuario->loadMissing(['administrador', 'profesional', 'cliente']);

        if ($usuario->administrador) {
            if ($action === 'mercadopago') {
                throw new HttpException(403, 'Solo el cliente dueno de la reserva puede iniciar el pago con Mercado Pago.');
            }

            return;
        }

        $isClienteOwner = $usuario->cliente && (int) $reserva->idCliente === (int) $usuario->idUsuario;
        $isProfesionalOwner = $usuario->profesional && (int) $reserva->idProfesional === (int) $usuario->idUsuario;

        $allowed = match ($action) {
            'view' => $isClienteOwner || $isProfesionalOwner,
            'pay', 'mercadopago', 'review' => $isClienteOwner,
            'reprogram', 'complete' => $isProfesionalOwner,
            'update', 'delete' => false,
            default => false,
        };

        if (! $allowed) {
            throw new HttpException(403, $this->authorizationMessage($action));
        }
    }

    public function assertPayable(Reserva $reserva): void
    {
        match ($reserva->estado) {
            'cancelada' => throw new HttpException(422, 'No se puede pagar una reserva cancelada.'),
            'completada', 'finalizada' => throw new HttpException(422, 'No se puede pagar una reserva finalizada.'),
            'enCurso' => throw new HttpException(422, 'No se puede pagar una reserva que esta en curso.'),
            'no_asistida' => throw new HttpException(422, 'No se puede pagar una reserva marcada como no asistida.'),
            default => null,
        };
    }

    public function assertReviewable(Reserva $reserva): void
    {
        if (! in_array($reserva->estado, ['completada', 'finalizada'], true)) {
            throw new HttpException(422, 'Solo se puede resenar una reserva completada o finalizada.');
        }
    }

    private function authorizationMessage(string $action): string
    {
        return match ($action) {
            'view' => 'No tenes permisos para consultar esta reserva.',
            'pay' => 'No tenes permisos para pagar esta reserva.',
            'mercadopago' => 'No tenes permisos para iniciar el pago de esta reserva.',
            'review' => 'No tenes permisos para resenar esta reserva.',
            'reprogram' => 'No tenes permisos para reprogramar esta reserva.',
            'complete' => 'No tenes permisos para completar esta reserva.',
            'update' => 'Solo administradores pueden actualizar reservas manualmente.',
            'delete' => 'Solo administradores pueden eliminar reservas.',
            default => 'No tenes permisos para operar esta reserva.',
        };
    }

    public function getReservasByCliente(int $idCliente): Collection
    {
        return Reserva::with([
            'servicio.ubicacion',
            'profesional.usuario',
            'horario',
            'pago',
            'paqueteComprado.paqueteServicio.servicio.ubicacion',
        ])
            ->where('idCliente', $idCliente)
            ->orderBy('fechaReserva', 'desc')
            ->get();
    }

    public function create(array $data): Reserva
    {
        return DB::transaction(function () use ($data) {
            $horarioOcupado = Reserva::where('idHorario', $data['idHorario'])
                ->whereIn('estado', self::ESTADOS_ACTIVOS)
                ->exists();

            if ($horarioOcupado) {
                throw new HttpException(409, 'El horario seleccionado ya tiene una reserva activa.');
            }

            return Reserva::create(array_merge($data, ['estado' => 'pendiente']));
        });
    }

    public function update(Reserva $reserva, array $data): Reserva
    {
        return DB::transaction(function () use ($reserva, $data) {
            $reserva->update($data);

            return $reserva->fresh(self::WITH_RELATIONS);
        });
    }

    public function delete(Reserva $reserva): void
    {
        DB::transaction(function () use ($reserva) {
            $reserva->delete();
        });
    }

    public function pagar(int $id, array $data): Reserva
    {
        $reserva = Reserva::with(self::WITH_RELATIONS)->findOrFail($id);

        $this->assertPayable($reserva);

        $pago = $reserva->pago;

        if ($pago && $pago->estado === 'aprobado') {
            throw new HttpException(409, 'La reserva ya tiene un pago aprobado.');
        }

        return DB::transaction(function () use ($reserva, $pago, $data) {
            // Si el método es mercadopago y viene un token como referenciaExterna, procesamos con la API real de MP
            if ($data['metodoPago'] === 'mercadopago' && !empty($data['referenciaExterna'])) {
                // Invocamos al servicio de Mercado Pago para realizar el cobro real con el token
                $email = optional($reserva->cliente?->usuario)->email ?? 'comprador_prueba@test.com';
                app(MercadoPagoService::class)->cobrarConToken($reserva->idReserva, $data['referenciaExterna'], $email);
                
                return $reserva->fresh(self::WITH_RELATIONS);
            }

            // Aceptar estados 'aprobado' o 'pendiente' (para Mercado Pago)
            $estadoPago = $data['estado'] ?? 'aprobado';
            if (!in_array($estadoPago, ['aprobado', 'pendiente'])) {
                $estadoPago = 'aprobado';
            }

            $datosPago = [
                'metodoPago'        => $data['metodoPago'],
                'estado'            => $estadoPago,
                'fechaPago'         => now(),
                'referenciaExterna' => $data['referenciaExterna'] ?? null,
            ];

            if ($pago) {
                $pago->update($datosPago);
            } else {
                $pago = Pago::create(array_merge($datosPago, [
                    'monto' => $reserva->servicio->precio,
                ]));

                $reserva->idPago = $pago->idPago;
            }

            // Solo confirmar si estado es aprobado
            if ($estadoPago === 'aprobado') {
                $reserva->estado = 'confirmada';
            }
            $reserva->save();

            $reservaFresh = $reserva->fresh(self::WITH_RELATIONS);
            $servicio      = $reservaFresh->servicio->nombre            ?? 'No especificado';
            $profesional   = $reservaFresh->profesional->nombreNegocio  ?? 'No especificado';
            $clienteNombre = $reservaFresh->cliente->usuario->nombre    ?? 'Un cliente';
            $fecha         = substr($reservaFresh->fechaReserva, 0, 10);
            $hora          = substr($reservaFresh->fechaReserva, 11, 5);

            if ($estadoPago === 'aprobado') {
                // Notificar cliente
                $this->notificacionService->notificar(
                    $reservaFresh->idCliente,
                    $reservaFresh->cliente->usuario->email,
                    'Pago confirmado',
                    "Tu reserva fue confirmada.\n\nServicio: {$servicio}\nProfesional: {$profesional}\nFecha: {$fecha}\nHora: {$hora}",
                    'confirmacion',
                    $reservaFresh->idReserva
                );

                // Notificar profesional
                if ($reservaFresh->profesional && $reservaFresh->profesional->usuario) {
                    $this->notificacionService->notificar(
                        $reservaFresh->idProfesional,
                        $reservaFresh->profesional->usuario->email,
                        'Pago confirmado',
                        "Se ha confirmado el pago de la reserva.\n\nServicio: {$servicio}\nCliente: {$clienteNombre}\nFecha: {$fecha}\nHora: {$hora}",
                        'confirmacion',
                        $reservaFresh->idReserva
                    );
                }
            } else if ($estadoPago === 'pendiente') {
                // Notificar cliente
                $this->notificacionService->notificar(
                    $reservaFresh->idCliente,
                    $reservaFresh->cliente->usuario->email,
                    'Pago pendiente',
                    "Tu reserva está pendiente de confirmación de pago.\n\nServicio: {$servicio}\nProfesional: {$profesional}\nFecha: {$fecha}\nHora: {$hora}",
                    'confirmacion',
                    $reservaFresh->idReserva
                );

                // Notificar profesional
                if ($reservaFresh->profesional && $reservaFresh->profesional->usuario) {
                    $this->notificacionService->notificar(
                        $reservaFresh->idProfesional,
                        $reservaFresh->profesional->usuario->email,
                        'Reserva pendiente de pago',
                        "Se ha registrado una reserva con pago pendiente.\n\nServicio: {$servicio}\nCliente: {$clienteNombre}\nFecha: {$fecha}\nHora: {$hora}",
                        'confirmacion',
                        $reservaFresh->idReserva
                    );
                }
            }

            return $reservaFresh;
        });
    }

    public function reprogramar(int $id, array $data): array
    {
        $reserva = Reserva::with(self::WITH_RELATIONS)->findOrFail($id);

        if (in_array($reserva->estado, ['finalizada', 'no_asistida'], true)) {
            throw new HttpException(409, 'No se puede reprogramar una reserva finalizada o marcada como no asistida.');
        }

        match ($reserva->estado) {
            'cancelada'  => throw new HttpException(409, 'No se puede reprogramar una reserva cancelada.'),
            'completada' => throw new HttpException(409, 'No se puede reprogramar una reserva completada.'),
            'enCurso'    => throw new HttpException(409, 'No se puede reprogramar una reserva que está en curso.'),
            default      => null,
        };

        $fecha      = $data['fecha'];
        $horaInicio = $data['horaInicio'];

        $disponibilidad = $this->disponibilidadService->getDisponibilidad(
            $reserva->idProfesional,
            $fecha,
            $reserva->idServicio,
            $reserva->idReserva
        );

        $slot = collect($disponibilidad['slots_disponibles'])->firstWhere('horaInicio', $horaInicio);

        if (!$slot) {
            throw new HttpResponseException(
                response()->json(
                    ['message' => 'El slot solicitado no está disponible para reprogramar.'],
                    409
                )
            );
        }

        $horaFin = $slot['horaFin'];

        $result = DB::transaction(function () use ($reserva, $fecha, $horaInicio, $horaFin) {
            $nuevoHorario = Horario::create([
                'fecha'      => $fecha,
                'horaInicio' => $horaInicio,
                'horaFin'    => $horaFin,
            ]);

            $reserva->update([
                'idHorario'    => $nuevoHorario->idHorario,
                'fechaReserva' => "{$fecha} {$horaInicio}:00",
            ]);

            $result = [
                'reserva' => $reserva->fresh(self::WITH_RELATIONS),
                'horario' => $nuevoHorario,
            ];

            if ($reserva->idPaqueteComprado !== null) {
                $result['paqueteComprado'] = PaqueteComprado::find($reserva->idPaqueteComprado);
            }

            $reservaFresh  = $result['reserva'];
            $servicio      = $reservaFresh->servicio->nombre            ?? 'No especificado';
            $profesional   = $reservaFresh->profesional->nombreNegocio  ?? 'No especificado';
            $clienteNombre = $reservaFresh->cliente->usuario->nombre    ?? 'Un cliente';

            // Notificar cliente
            $this->notificacionService->notificar(
                $reservaFresh->idCliente,
                $reservaFresh->cliente->usuario->email,
                'Reserva reprogramada',
                "Tu reserva fue reprogramada.\n\nServicio: {$servicio}\nProfesional: {$profesional}\nNueva fecha: {$fecha}\nNueva hora: {$horaInicio}",
                'actualizacion',
                $reservaFresh->idReserva
            );

            // Notificar profesional
            if ($reservaFresh->profesional && $reservaFresh->profesional->usuario) {
                $this->notificacionService->notificar(
                    $reservaFresh->idProfesional,
                    $reservaFresh->profesional->usuario->email,
                    'Reserva reprogramada',
                    "Una reserva fue reprogramada por el cliente.\n\nServicio: {$servicio}\nCliente: {$clienteNombre}\nNueva fecha: {$fecha}\nNueva hora: {$horaInicio}",
                    'actualizacion',
                    $reservaFresh->idReserva
                );
            }

            return $result;
        });

        broadcast(new ReservaActualizada($result['reserva'], 'reprogramada'));

        return $result;
    }

    public function cancelar(int $id, Usuario $usuario): array
    {
        $reserva = Reserva::with(self::WITH_RELATIONS)->findOrFail($id);

        $this->validarPermisoCancelacion($reserva, $usuario);

        match ($reserva->estado) {
            'pendiente', 'confirmada' => null,
            'cancelada'               => throw new HttpException(422, 'La reserva ya está cancelada.'),
            'enCurso'                 => throw new HttpException(422, 'No se puede cancelar una reserva que está en curso.'),
            'completada', 'finalizada' => throw new HttpException(422, 'No se puede cancelar una reserva finalizada.'),
            'no_asistida'             => throw new HttpException(422, 'No se puede cancelar una reserva marcada como no asistida.'),
            default                   => throw new HttpException(422, 'El estado de la reserva no permite cancelación.'),
        };

        if ($reserva->pago?->estado === 'aprobado') {
            Log::info(
                'Reserva cancelada. Pago aprobado asociado; requiere revisión administrativa para reembolso si corresponde.',
                [
                    'idReserva' => $reserva->idReserva,
                    'idPago'    => $reserva->pago->idPago,
                    'idUsuario' => $usuario->idUsuario,
                ]
            );
        }

        if ($reserva->idPaqueteComprado === null) {
            $result = DB::transaction(function () use ($reserva) {
                $reserva->estado = 'cancelada';
                $reserva->comentarios = 'Cancelada por el usuario';
                $reserva->save();

                $servicio      = $reserva->servicio->nombre            ?? 'No especificado';
                $profesional   = $reserva->profesional->nombreNegocio  ?? 'No especificado';
                $clienteNombre = $reserva->cliente->usuario->nombre    ?? 'Un cliente';
                $fecha         = substr($reserva->fechaReserva, 0, 10);
                $hora          = substr($reserva->fechaReserva, 11, 5);

                // Notificar cliente
                $this->notificacionService->notificar(
                    $reserva->idCliente,
                    $reserva->cliente->usuario->email,
                    'Reserva cancelada',
                    "Tu reserva fue cancelada.\n\nServicio: {$servicio}\nProfesional: {$profesional}\nFecha: {$fecha}\nHora: {$hora}",
                    'cancelacion',
                    $reserva->idReserva
                );

                // Notificar profesional
                if ($reserva->profesional && $reserva->profesional->usuario) {
                    $this->notificacionService->notificar(
                        $reserva->idProfesional,
                        $reserva->profesional->usuario->email,
                        'Reserva cancelada',
                        "Una reserva fue cancelada.\n\nServicio: {$servicio}\nCliente: {$clienteNombre}\nFecha: {$fecha}\nHora: {$hora}",
                        'cancelacion',
                        $reserva->idReserva
                    );
                }

                return [
                    'reserva' => $reserva->fresh(self::WITH_RELATIONS),
                ];
            });

            broadcast(new ReservaActualizada($result['reserva'], 'cancelada'));

            return $result;
        }

        $result = DB::transaction(function () use ($reserva) {
            $paquete = PaqueteComprado::lockForUpdate()->findOrFail($reserva->idPaqueteComprado);

            $reserva->estado = 'cancelada';
            $reserva->comentarios = 'Cancelada por el usuario';
            $reserva->save();

            $servicio      = $reserva->servicio->nombre            ?? 'No especificado';
            $profesional   = $reserva->profesional->nombreNegocio  ?? 'No especificado';
            $clienteNombre = $reserva->cliente->usuario->nombre    ?? 'Un cliente';
            $fecha         = substr($reserva->fechaReserva, 0, 10);
            $hora          = substr($reserva->fechaReserva, 11, 5);

            // Notificar cliente
            $this->notificacionService->notificar(
                $reserva->idCliente,
                $reserva->cliente->usuario->email,
                'Reserva cancelada',
                "Tu reserva fue cancelada.\n\nServicio: {$servicio}\nProfesional: {$profesional}\nFecha: {$fecha}\nHora: {$hora}",
                'cancelacion',
                $reserva->idReserva
            );

            // Notificar profesional
            if ($reserva->profesional && $reserva->profesional->usuario) {
                $this->notificacionService->notificar(
                    $reserva->idProfesional,
                    $reserva->profesional->usuario->email,
                    'Reserva cancelada',
                    "Una reserva fue cancelada.\n\nServicio: {$servicio}\nCliente: {$clienteNombre}\nFecha: {$fecha}\nHora: {$hora}",
                    'cancelacion',
                    $reserva->idReserva
                );
            }

            $paquete->sesionesUsadas    = max(0, $paquete->sesionesUsadas - 1);
            $paquete->sesionesRestantes = min($paquete->totalSesiones, $paquete->sesionesRestantes + 1);

            if ($paquete->estado === 'agotado') {
                $paquete->estado = 'activo';
            }

            $paquete->save();

            return [
                'reserva'         => $reserva->fresh(self::WITH_RELATIONS),
                'paqueteComprado' => $paquete->fresh(),
            ];
        });

        broadcast(new ReservaActualizada($result['reserva'], 'cancelada'));

        return $result;
    }

    private function validarPermisoCancelacion(Reserva $reserva, Usuario $usuario): void
    {
        $usuario->loadMissing('administrador', 'profesional', 'cliente');

        if ($usuario->administrador) {
            return;
        }

        if ($usuario->cliente && (int) $reserva->idCliente === (int) $usuario->idUsuario) {
            return;
        }

        if ($usuario->profesional && (int) $reserva->idProfesional === (int) $usuario->idUsuario) {
            return;
        }

        throw new HttpException(403, 'No tenés permiso para cancelar esta reserva.');
    }

    public function completar(int $id): Reserva
    {
        $reserva = Reserva::with(self::WITH_RELATIONS)->findOrFail($id);

        if (! in_array($reserva->estado, ['confirmada', 'enCurso'], true)) {
            throw new HttpException(422, 'El estado de la reserva no permite completarla.');
        }

        match ($reserva->estado) {
            'pendiente'  => throw new HttpException(409, 'No se puede completar una reserva que está pendiente.'),
            'cancelada'  => throw new HttpException(409, 'No se puede completar una reserva cancelada.'),
            'completada' => throw new HttpException(409, 'La reserva ya está completada.'),
            default      => null,
        };

        $reserva = DB::transaction(function () use ($reserva) {
            $reserva->estado = 'completada';
            $reserva->save();

            return $reserva->fresh(self::WITH_RELATIONS);
        });

        broadcast(new ReservaActualizada($reserva, 'completada'));

        return $reserva;
    }

    public function cancelarVencidas(): array
    {
        $vencidas = Reserva::with(self::WITH_RELATIONS)
            ->where('estado', 'pendiente')
            ->where('created_at', '<=', now()->subMinutes(15))
            ->get();

        if ($vencidas->isEmpty()) {
            return [
                'cantidadCanceladas' => 0,
                'reservas'           => [],
            ];
        }

        return DB::transaction(function () use ($vencidas) {
            foreach ($vencidas as $reserva) {
                $reserva->estado = 'cancelada';
                $reserva->save();
            }

            return [
                'cantidadCanceladas' => $vencidas->count(),
                'reservas'           => $vencidas->map(fn ($r) => $r->fresh(self::WITH_RELATIONS))->values(),
            ];
        });
    }
    public function enviarRecordatorios48Horas(): int
    {
        $reservas = Reserva::with(self::WITH_RELATIONS)
            ->where('estado', 'confirmada')
            ->where('recordatorio48hEnviado', false)
            ->whereBetween(
                'fechaReserva',
                [now()->addHours(47), now()->addHours(49)]
            )
            ->get();

        foreach ($reservas as $reserva) {
            $servicio    = $reserva->servicio->nombre           ?? 'No especificado';
            $profesional = $reserva->profesional->nombreNegocio ?? 'No especificado';
            $fecha       = substr($reserva->fechaReserva, 0, 10);
            $hora        = substr($reserva->fechaReserva, 11, 5);

            $this->notificacionService->notificar(
                $reserva->idCliente,
                $reserva->cliente->usuario->email,
                'Recordatorio de reserva',
                "Tu reserva será dentro de 48 horas.\n\nServicio: {$servicio}\nProfesional: {$profesional}\nFecha: {$fecha}\nHora: {$hora}",
                'recordatorio',
                $reserva->idReserva
            );

            $reserva->recordatorio48hEnviado = true;
            $reserva->save();
        }

        return $reservas->count();
    }
    public function resena(int $id, array $data): array
    {
        $reserva = Reserva::with(['cliente', 'profesional', 'servicio.ubicacion', 'horario'])->findOrFail($id);

        if (! in_array($reserva->estado, ['completada', 'finalizada'], true)) {
            throw new HttpException(422, 'Solo se puede resenar una reserva completada o finalizada.');
        }

        if (Resena::where('idReserva', $id)->exists()) {
            throw new HttpException(409, 'Esta reserva ya tiene una reseña.');
        }

        return DB::transaction(function () use ($reserva, $data) {
            $resena = Resena::create([
                'calificacion'  => $data['calificacion'],
                'comentario'    => $data['comentario'] ?? null,
                'fecha'         => now()->toDateString(),
                'idProfesional' => $reserva->idProfesional,
                'idCliente'     => $reserva->idCliente,
                'idReserva'     => $reserva->idReserva,
            ]);

            $nuevoRating = Resena::where('idProfesional', $reserva->idProfesional)->avg('calificacion');

            $profesional = Profesional::findOrFail($reserva->idProfesional);
            $profesional->ratingPromedio = round($nuevoRating, 2);
            $profesional->save();

            $resena->load(['reserva.horario', 'reserva.servicio']);

            return [
                'resena'      => $resena,
                'profesional' => $profesional,
            ];
        });
    }
}
