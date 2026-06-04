<?php

namespace App\Services;

use App\Events\ReservaActualizada;
use App\Models\Horario;
use App\Models\PaqueteComprado;
use App\Models\Profesional;
use App\Models\Reserva;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class ReservaSlotService
{
    public function __construct(
        private DisponibilidadService $disponibilidadService,
        private NotificacionService $notificacionService
    ) {}

    public function reservar(
        int $idProfesional,
        int $idCliente,
        int $idServicio,
        string $fecha,
        string $horaInicio,
        ?int $idPaqueteComprado = null
    ): array {
        Profesional::findOrFail($idProfesional);

        $disponibilidad = $this->disponibilidadService->getDisponibilidad(
            $idProfesional,
            $fecha,
            $idServicio
        );

        $slot = collect($disponibilidad['slots_disponibles'])
            ->firstWhere('horaInicio', $horaInicio);

        if (!$slot) {
            throw new HttpResponseException(
                response()->json(
                    ['message' => 'El slot solicitado no está disponible para reservar.'],
                    409
                )
            );
        }

        $horaFin = $slot['horaFin'];

        if ($idPaqueteComprado !== null) {
            $result = $this->reservarConPaquete(
                $idProfesional, $idCliente, $idServicio,
                $fecha, $horaInicio, $horaFin, $idPaqueteComprado
            );
        } else {
            $result = $this->reservarNormal(
                $idProfesional, $idCliente, $idServicio,
                $fecha, $horaInicio, $horaFin
            );
        }

        if (! $result['reserva']->relationLoaded('horario')) {
            $result['reserva']->load('horario');
        }

        broadcast(new ReservaActualizada($result['reserva'], 'creada'));

        return $result;
    }

    private function reservarNormal(
        int $idProfesional,
        int $idCliente,
        int $idServicio,
        string $fecha,
        string $horaInicio,
        string $horaFin
    ): array {
        return DB::transaction(function () use (
            $idProfesional, $idCliente, $idServicio, $fecha, $horaInicio, $horaFin
        ) {
            $horario = Horario::create([
                'fecha'      => $fecha,
                'horaInicio' => $horaInicio,
                'horaFin'    => $horaFin,
            ]);

            $reserva = Reserva::create([
                'idCliente'     => $idCliente,
                'idProfesional' => $idProfesional,
                'idServicio'    => $idServicio,
                'idHorario'     => $horario->idHorario,
                'fechaReserva'  => "{$fecha} {$horaInicio}:00",
                'estado'        => 'pendiente',
            ]);

            $reserva->load(['servicio', 'cliente.usuario', 'profesional']);

            $servicio    = $reserva->servicio->nombre        ?? 'No especificado';
            $profesional = $reserva->profesional->nombreNegocio ?? 'No especificado';

            $this->notificacionService->notificar(
                $idCliente,
                $reserva->cliente->usuario->email,
                'Reserva creada correctamente',
                "Tu reserva fue creada correctamente.\n\nServicio: {$servicio}\nProfesional: {$profesional}\nFecha: {$fecha}\nHora: {$horaInicio}\nEstado: pendiente",
                'confirmacion'
            );

            return [
                'reserva' => $reserva,
                'horario' => $horario,
            ];
        });
    }

    private function reservarConPaquete(
        int $idProfesional,
        int $idCliente,
        int $idServicio,
        string $fecha,
        string $horaInicio,
        string $horaFin,
        int $idPaqueteComprado
    ): array {
        return DB::transaction(function () use (
            $idProfesional, $idCliente, $idServicio,
            $fecha, $horaInicio, $horaFin, $idPaqueteComprado
        ) {
            $paquete = PaqueteComprado::lockForUpdate()->findOrFail($idPaqueteComprado);

            if ((int) $paquete->idCliente !== $idCliente) {
                throw new HttpResponseException(
                    response()->json(
                        ['message' => 'El paquete no pertenece al cliente indicado.'],
                        403
                    )
                );
            }

            if ($paquete->estado !== 'activo') {
                throw new HttpResponseException(
                    response()->json(
                        ['message' => 'El paquete comprado no está activo.'],
                        409
                    )
                );
            }

            if ($paquete->sesionesRestantes <= 0) {
                throw new HttpResponseException(
                    response()->json(
                        ['message' => 'El paquete no tiene sesiones disponibles.'],
                        409
                    )
                );
            }

            $paquete->load('paqueteServicio');

            if ((int) $paquete->paqueteServicio->idServicio !== $idServicio) {
                throw new HttpResponseException(
                    response()->json(
                        ['message' => 'El paquete seleccionado no corresponde al servicio solicitado.'],
                        422
                    )
                );
            }

            $horario = Horario::create([
                'fecha'      => $fecha,
                'horaInicio' => $horaInicio,
                'horaFin'    => $horaFin,
            ]);

            $reserva = Reserva::create([
                'idCliente'         => $idCliente,
                'idProfesional'     => $idProfesional,
                'idServicio'        => $idServicio,
                'idHorario'         => $horario->idHorario,
                'idPaqueteComprado' => $idPaqueteComprado,
                'fechaReserva'      => "{$fecha} {$horaInicio}:00",
                'estado'            => 'confirmada',
            ]);

            $paquete->sesionesUsadas    += 1;
            $paquete->sesionesRestantes -= 1;

            if ($paquete->sesionesRestantes === 0) {
                $paquete->estado = 'agotado';
            }

            $paquete->save();

            $reserva->load(['servicio', 'cliente.usuario', 'profesional']);

            $servicio    = $reserva->servicio->nombre            ?? 'No especificado';
            $profesional = $reserva->profesional->nombreNegocio  ?? 'No especificado';

            $this->notificacionService->notificar(
                $idCliente,
                $reserva->cliente->usuario->email,
                'Reserva creada correctamente',
                "Tu reserva fue creada correctamente.\n\nServicio: {$servicio}\nProfesional: {$profesional}\nFecha: {$fecha}\nHora: {$horaInicio}\nEstado: confirmada",
                'confirmacion'
            );

            return [
                'reserva'         => $reserva,
                'horario'         => $horario,
                'paqueteComprado' => $paquete->fresh(),
            ];
        });
    }
}

