<?php

namespace App\Services;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;
use App\Models\Reserva;
use App\Models\VideoSesion;

class LiveKitService
{
    public function generarToken(string $roomName, string $participantIdentity): string
    {
        $options = (new AccessTokenOptions())
            ->setIdentity($participantIdentity);

        $grant = (new VideoGrant())
            ->setRoomJoin()
            ->setRoomName($roomName);

        $token = (new AccessToken(
            config('services.livekit.api_key'),
            config('services.livekit.api_secret'),
            $options
        ))->setGrant($grant);

        return $token->toJwt();
    }

    public function generarTokenParaReserva(int $idReserva, int $idUsuario): array
    {
        $reserva = Reserva::with(['servicio', 'videoSesion'])->findOrFail($idReserva);

        if ($reserva->servicio->modalidad !== 'virtual') {
            abort(422, 'La reserva no corresponde a un servicio virtual.');
        }

        if ($reserva->estado === 'cancelada') {
            abort(409, 'No se puede generar videollamada para una reserva cancelada.');
        }

        if ($reserva->estado === 'completada') {
            abort(409, 'No se puede generar videollamada para una reserva completada.');
        }

        if ($idUsuario !== (int) $reserva->idCliente && $idUsuario !== (int) $reserva->idProfesional) {
            abort(403, 'El usuario no pertenece a esta reserva.');
        }

        $videoSesion = $reserva->videoSesion;

        if (!$videoSesion) {
            $nombreSala  = 'reserva-' . $idReserva;
            $videoSesion = VideoSesion::create([
                'proveedor'       => 'livekit',
                'url'             => config('services.livekit.url'),
                'nombreSala'      => $nombreSala,
                'fechaHoraInicio' => now(),
                'estado'          => 'programada',
            ]);

            $reserva->idVideoSesion = $videoSesion->idVideoSesion;
            $reserva->save();
        }

        $token = $this->generarToken($videoSesion->nombreSala, 'usuario-' . $idUsuario);

        return [
            'roomName'   => $videoSesion->nombreSala,
            'token'      => $token,
            'livekitUrl' => config('services.livekit.url'),
        ];
    }
}
