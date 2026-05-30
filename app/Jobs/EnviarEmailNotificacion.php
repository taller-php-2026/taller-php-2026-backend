<?php

namespace App\Jobs;

use App\Mail\NotificacionMail;
use App\Models\Notificacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EnviarEmailNotificacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $email,
        private string $titulo,
        private string $mensaje,
        private int $idNotificacion
    ) {}

    public function handle(): void
    {
        Mail::to($this->email)->send(new NotificacionMail($this->titulo, $this->mensaje));

        Notificacion::where('idNotificacion', $this->idNotificacion)
            ->update(['enviadaMail' => true]);
    }
}
