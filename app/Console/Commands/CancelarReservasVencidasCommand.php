<?php

namespace App\Console\Commands;

use App\Services\ReservaService;
use Illuminate\Console\Command;

class CancelarReservasVencidasCommand extends Command
{
    protected $signature = 'reservas:cancelar-vencidas';

    protected $description = 'Cancela reservas pendientes que superaron el tiempo de gracia de pago';

    public function __construct(private ReservaService $reservaService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $result = $this->reservaService->cancelarVencidas();

        $cantidad = $result['cantidadCanceladas'];

        if ($cantidad === 0) {
            $this->info('No hay reservas vencidas para cancelar.');
        } else {
            $this->info("Reservas canceladas: {$cantidad}");

            foreach ($result['reservas'] as $reserva) {
                $this->line("  - idReserva: {$reserva->idReserva}");
            }
        }

        return Command::SUCCESS;
    }
}
