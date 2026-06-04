<?php

namespace App\Services;

use App\Events\ReservaActualizada;
use App\Models\Horario;
use App\Models\Pago;
use App\Models\PaqueteComprado;
use App\Models\Reserva;
use App\Models\Resena;
use App\Models\Profesional;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
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
        'servicio',
        'pago',
        'horario',
    ];

    public function getAll(): Collection
    {
        return Reserva::with(self::WITH_RELATIONS)->get();
    }

    public function getById(int $id): Reserva
    {
        return Reserva::with(self::WITH_RELATIONS)->findOrFail($id);
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

        if ($reserva->estado === 'cancelada') {
            throw new HttpException(422, 'No se puede pagar una reserva cancelada.');
        }

        $pago = $reserva->pago;

        if ($pago && $pago->estado === 'aprobado') {
            throw new HttpException(409, 'La reserva ya tiene un pago aprobado.');
        }

        return DB::transaction(function () use ($reserva, $pago, $data) {
            $datosPago = [
                'metodoPago'        => $data['metodoPago'],
                'estado'            => 'aprobado',
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

            $reserva->estado = 'confirmada';
            $reserva->save();

            return $reserva->fresh(self::WITH_RELATIONS);
        });
    }

    public function reprogramar(int $id, array $data): array
    {
        $reserva = Reserva::with(self::WITH_RELATIONS)->findOrFail($id);

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

            $reservaFresh = $result['reserva'];
            $servicio     = $reservaFresh->servicio->nombre            ?? 'No especificado';
            $profesional  = $reservaFresh->profesional->nombreNegocio  ?? 'No especificado';

            $this->notificacionService->notificar(
                $reservaFresh->idCliente,
                $reservaFresh->cliente->usuario->email,
                'Reserva reprogramada',
                "Tu reserva fue reprogramada.\n\nServicio: {$servicio}\nProfesional: {$profesional}\nNueva fecha: {$fecha}\nNueva hora: {$horaInicio}",
                'actualizacion'
            );

            return $result;
        });

        broadcast(new ReservaActualizada($result['reserva'], 'reprogramada'));

        return $result;
    }

    public function cancelar(int $id): array
    {
        $reserva = Reserva::with(self::WITH_RELATIONS)->findOrFail($id);

        match ($reserva->estado) {
            'cancelada'  => throw new HttpException(409, 'La reserva ya está cancelada.'),
            'completada' => throw new HttpException(409, 'No se puede cancelar una reserva completada.'),
            'enCurso'    => throw new HttpException(409, 'No se puede cancelar una reserva que está en curso.'),
            default      => null,
        };

        if ($reserva->idPaqueteComprado === null) {
            $result = DB::transaction(function () use ($reserva) {
                $reserva->estado = 'cancelada';
                $reserva->save();

                $servicio    = $reserva->servicio->nombre            ?? 'No especificado';
                $profesional = $reserva->profesional->nombreNegocio  ?? 'No especificado';
                $fecha       = substr($reserva->fechaReserva, 0, 10);
                $hora        = substr($reserva->fechaReserva, 11, 5);

                $this->notificacionService->notificar(
                    $reserva->idCliente,
                    $reserva->cliente->usuario->email,
                    'Reserva cancelada',
                    "Tu reserva fue cancelada.\n\nServicio: {$servicio}\nProfesional: {$profesional}\nFecha: {$fecha}\nHora: {$hora}",
                    'cancelacion'
                );

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
            $reserva->save();

            $servicio    = $reserva->servicio->nombre            ?? 'No especificado';
            $profesional = $reserva->profesional->nombreNegocio  ?? 'No especificado';
            $fecha       = substr($reserva->fechaReserva, 0, 10);
            $hora        = substr($reserva->fechaReserva, 11, 5);

            $this->notificacionService->notificar(
                $reserva->idCliente,
                $reserva->cliente->usuario->email,
                'Reserva cancelada',
                "Tu reserva fue cancelada.\n\nServicio: {$servicio}\nProfesional: {$profesional}\nFecha: {$fecha}\nHora: {$hora}",
                'cancelacion'
            );

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

    public function completar(int $id): Reserva
    {
        $reserva = Reserva::with(self::WITH_RELATIONS)->findOrFail($id);

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

    public function resena(int $id, array $data): array
    {
        $reserva = Reserva::with(['cliente', 'profesional', 'servicio', 'horario'])->findOrFail($id);

        if ($reserva->estado !== 'completada') {
            throw new HttpException(422, 'Solo se puede reseñar una reserva completada.');
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
