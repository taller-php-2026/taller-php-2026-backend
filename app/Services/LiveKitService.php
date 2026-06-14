<?php

namespace App\Services;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;
use App\Models\Reserva;
use App\Models\VideoSesion;

class LiveKitService
{
    public function generarToken(string $roomName, string $participantIdentity, ?string $participantName = null): string
    {
        $options = (new AccessTokenOptions())
            ->setIdentity($participantIdentity);

        if ($participantName !== null && method_exists($options, 'setName')) {
            $options->setName($participantName);
        }

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
        $reserva = Reserva::with(['servicio', 'videoSesion', 'cliente.usuario', 'profesional.usuario'])->findOrFail($idReserva);

        if (! in_array($reserva->servicio->modalidad, ['virtual', 'hibrida'], true)) {
            abort(422, 'La reserva no corresponde a un servicio virtual.');
        }

        if ($reserva->estado === 'cancelada') {
            abort(422, 'No se puede generar videollamada para una reserva cancelada.');
        }

        if (in_array($reserva->estado, ['pendiente', 'completada', 'finalizada', 'no_asistida'], true)) {
            abort(422, 'El estado de la reserva no permite generar una videollamada.');
        }

        $esCliente = $idUsuario === (int) $reserva->idCliente;
        $esProfesional = $idUsuario === (int) $reserva->idProfesional;

        if (! $esCliente && ! $esProfesional) {
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

        $rol = $esCliente ? 'cliente' : 'profesional';
        $identity = $rol . '-' . $idUsuario;
        $nombre = $esCliente
            ? ($reserva->cliente?->usuario?->nombre ?? 'Cliente')
            : ($reserva->profesional?->usuario?->nombre ?? $reserva->profesional?->nombreNegocio ?? 'Profesional');

        $token = $this->generarToken($videoSesion->nombreSala, $identity, $nombre);

        return [
            'url'        => config('services.livekit.url'),
            'token'      => $token,
            'room'       => $videoSesion->nombreSala,
            'identity'   => $identity,
            'nombre'     => $nombre,
            'reserva'    => [
                'idReserva'   => $reserva->idReserva,
                'estado'      => $reserva->estado,
                'fechaReserva' => $reserva->fechaReserva,
                'servicio'    => [
                    'nombre'          => $reserva->servicio?->nombre,
                    'modalidad'       => $reserva->servicio?->modalidad,
                    'duracionMinutos' => $reserva->servicio?->duracionMinutos,
                    'imagenUrl'       => $reserva->servicio?->imagenUrl,
                ],
                'profesional' => [
                    'nombreNegocio'   => $reserva->profesional?->nombreNegocio,
                    'imagenPerfilUrl' => $reserva->profesional?->usuario?->imagenPerfilUrl,
                    'usuario'       => [
                        'nombre'          => $reserva->profesional?->usuario?->nombre,
                        'imagenPerfilUrl' => $reserva->profesional?->usuario?->imagenPerfilUrl,
                    ],
                ],
                'cliente'     => [
                    'usuario' => [
                        'nombre'          => $reserva->cliente?->usuario?->nombre,
                        'imagenPerfilUrl' => $reserva->cliente?->usuario?->imagenPerfilUrl,
                    ],
                ],
            ],
            'roomName'   => $videoSesion->nombreSala,
            'livekitUrl' => config('services.livekit.url'),
        ];
    }
}
