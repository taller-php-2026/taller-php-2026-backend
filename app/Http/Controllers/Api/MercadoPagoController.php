<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MercadoPagoService;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    public function __construct(
        private MercadoPagoService $mercadoPagoService,
        private ReservaService $reservaService,
    ) {}

    /**
     * POST /api/reservas/{id}/mercadopago
     * Crear preferencia de pago para una reserva
     */
    public function crearPreferenciaReserva(Request $request, int $id)
    {
        $reserva = $this->reservaService->getById((int) $id);
        $this->reservaService->authorizeReservaAction($reserva, $request->user(), 'mercadopago');
        $this->reservaService->assertPayable($reserva);

        $resultado = $this->mercadoPagoService->createPreferenciaReserva((int) $id);

        return response()->json([
            'message' => 'Preferencia de pago creada correctamente',
            'data' => $resultado,
        ], 201);
    }

    /**
     * POST /api/paquetes-comprados/{id}/mercadopago
     * Crear preferencia de pago para un paquete comprado
     */
    public function crearPreferenciaPaquete(int $id)
    {
        $resultado = $this->mercadoPagoService->createPreferenciaPaquete((int) $id);

        return response()->json([
            'message' => 'Preferencia de pago creada correctamente',
            'data' => $resultado,
        ], 201);
    }

    /**
     * POST /api/mercadopago/webhook
     * Recibir webhooks de Mercado Pago
     * 
     * Mercado Pago enviará:
     * {
     *   "type": "payment",
     *   "data": {
     *     "id": <payment_id>
     *   }
     * }
     */
    public function webhook(Request $request)
    {
        $data = $request->all();

        Log::info('Webhook Mercado Pago recibido', $data);

        $resultado = $this->mercadoPagoService->procesarWebhook($data);

        // Mercado Pago requiere respuesta 200 OK
        return response()->json($resultado, 200);
    }

    /**
     * GET /api/mercadopago/pago/{payment_id}
     * Consultar estado de un pago
     */
    public function consultarPago(int $paymentId)
    {
        $resultado = $this->mercadoPagoService->consultarPago($paymentId);

        return response()->json([
            'data' => $resultado,
        ]);
    }
}
