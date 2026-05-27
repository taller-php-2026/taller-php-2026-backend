<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Reserva;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReservaService
{
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
}
