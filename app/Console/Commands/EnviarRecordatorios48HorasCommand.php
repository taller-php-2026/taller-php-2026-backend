<?php

namespace App\Console\Commands;

use App\Services\ReservaService;
use Illuminate\Console\Command;

class EnviarRecordatorios48HorasCommand extends Command
{
    protected $signature = 'reservas:recordatorios-48h';

    protected $description = 'Envía recordatorios de reservas próximas';

    public function __construct(
        private ReservaService $reservaService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $cantidad = $this->reservaService->enviarRecordatorios48Horas();

        $this->info("Recordatorios enviados: {$cantidad}");

        return Command::SUCCESS;
    }
}