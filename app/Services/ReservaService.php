<?php

namespace App\Services;

use App\Models\Horario;
use App\Models\Pago;
use App\Models\Reserva;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReservaService
{
    public function __construct(private DisponibilidadService $disponibilidadService) {}
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

        return DB::transaction(function () use ($reserva, $fecha, $horaInicio, $horaFin) {
            $nuevoHorario = Horario::create([
                'fecha'      => $fecha,
                'horaInicio' => $horaInicio,
                'horaFin'    => $horaFin,
            ]);

            $reserva->update([
                'idHorario'    => $nuevoHorario->idHorario,
                'fechaReserva' => "{$fecha} {$horaInicio}:00",
            ]);

            return [
                'reserva' => $reserva->fresh(self::WITH_RELATIONS),
                'horario' => $nuevoHorario,
            ];
        });
    }

    public function cancelar(int $id): Reserva
    {
        $reserva = Reserva::with(self::WITH_RELATIONS)->findOrFail($id);

        match ($reserva->estado) {
            'cancelada'  => throw new HttpException(409, 'La reserva ya está cancelada.'),
            'completada' => throw new HttpException(409, 'No se puede cancelar una reserva completada.'),
            'enCurso'    => throw new HttpException(409, 'No se puede cancelar una reserva que está en curso.'),
            default      => null,
        };

        return DB::transaction(function () use ($reserva) {
            $reserva->estado = 'cancelada';
            $reserva->save();

            return $reserva->fresh(self::WITH_RELATIONS);
        });
    }
}
