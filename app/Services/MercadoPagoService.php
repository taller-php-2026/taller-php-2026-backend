<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Reserva;
use App\Models\PaqueteComprado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use App\Services\NotificacionService;

class MercadoPagoService
{
    private PreferenceClient $preferenceClient;
    private PaymentClient $paymentClient;

    public function __construct()
    {
        \MercadoPago\MercadoPagoConfig::setAccessToken(
            config('mercadopago.access_token')
        );

        $this->preferenceClient = new PreferenceClient();
        $this->paymentClient = new PaymentClient();
    }

    /* =========================================================
     *  COBRO CON TOKEN (BRICKS)
     * ========================================================= */

    public function cobrarConToken(int $idReserva, string $token, string $emailPayer): array
    {
        $reserva = Reserva::with(['servicio'])->findOrFail($idReserva);

        try {
            $payment = $this->paymentClient->create([
                'token' => $token,
                'description' => $reserva->servicio->nombre ?? 'Servicio',
                'installments' => 1,
                'payment_method_id' => 'master', // O inferir del token en producción
                'payer' => [
                    'email' => $emailPayer,
                ],
                'transaction_amount' => (float) $reserva->servicio->precio,
                'external_reference' => "reserva_{$idReserva}",
            ]);

            // Procesar el pago localmente de inmediato con los datos de MP
            return $this->procesarReserva($payment);
        } catch (MPApiException $e) {
            Log::error('MercadoPago cobrarConToken error', [
                'message' => $e->getMessage(),
                'response' => method_exists($e, 'getApiResponse')
                    ? $e->getApiResponse()?->getContent()
                    : null,
            ]);
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(500, 'Error procesando cobro con Mercado Pago: ' . $e->getMessage());
        }
    }

    /* =========================================================
     *  RESERVA
     * ========================================================= */

    public function createPreferenciaReserva(int $idReserva, ?string $urlRetorno = null): array
    {
        $reserva = Reserva::with(['cliente.usuario', 'servicio'])
            ->findOrFail($idReserva);

        if ($reserva->estado === 'cancelada') {
            abort(422, 'No se puede pagar una reserva cancelada.');
        }

        if ($reserva->pago && $reserva->pago->estado === 'aprobado') {
            abort(409, 'La reserva ya está pagada.');
        }

        $frontendUrl     = rtrim(config('mercadopago.frontend_url'), '/');
        $apiUrl          = rtrim(config('app.api_url'), '/');
        $notificationUrl = $apiUrl . '/api/mercadopago/webhook';

        $backUrls = [
            'success' => $frontendUrl . '/reservas/' . $reserva->idReserva . '/confirmacion?status=approved',
            'failure' => $frontendUrl . '/reservas/' . $reserva->idReserva . '/confirmacion?status=failure',
            'pending' => $frontendUrl . '/reservas/' . $reserva->idReserva . '/confirmacion?status=pending',
        ];

        if (empty($backUrls['success'])) {
            abort(500, 'FRONTEND_URL no está configurado. No se puede crear la preferencia de pago.');
        }

        $payload = [
            'items' => [[
                'id'          => "reserva_{$idReserva}",
                'title'       => $reserva->servicio->nombre ?? 'Servicio',
                'quantity'    => 1,
                'currency_id' => 'UYU',
                'unit_price'  => (float) $reserva->servicio->precio,
            ]],
            'payer' => [
                'name'  => $reserva->cliente->usuario->nombre ?? 'Cliente',
                'email' => $reserva->cliente->usuario->email ?? '',
            ],
            'back_urls'          => $backUrls,
            'auto_return'        => 'approved',
            'external_reference' => "reserva_{$idReserva}",
            'notification_url'   => $notificationUrl,
        ];

        Log::info('MercadoPago preference payload', [
            'idReserva'        => $reserva->idReserva,
            'back_urls'        => $backUrls,
            'notification_url' => $notificationUrl,
            'frontend_url'     => $frontendUrl,
            'api_url'          => $apiUrl,
            'item_price'       => $payload['items'][0]['unit_price'],
            'item_title'       => $payload['items'][0]['title'],
            'payer_email'      => $payload['payer']['email'],
            'access_token_set' => ! empty(config('mercadopago.access_token')),
        ]);

        try {
            $preference = $this->preferenceClient->create($payload);

            return [
                'checkout_url'  => $preference->init_point,
                'preference_id' => $preference->id,
            ];
        } catch (MPApiException $e) {
            $apiResponse = method_exists($e, 'getApiResponse')
                ? $e->getApiResponse()?->getContent()
                : null;

            Log::error('MercadoPago error reserva', [
                'idReserva'    => $idReserva,
                'exception'    => get_class($e),
                'message'      => $e->getMessage(),
                'code'         => $e->getCode(),
                'api_response' => $apiResponse,
                'payload'      => $payload,
            ]);

            if (config('app.debug')) {
                abort(response()->json([
                    'message'      => 'Error creando preferencia de pago',
                    'error'        => $e->getMessage(),
                    'api_response' => $apiResponse,
                    'payload'      => $payload,
                ], 500));
            }

            abort(500, 'Error creando preferencia de pago');
        } catch (\Throwable $e) {
            Log::error('MercadoPago excepción inesperada reserva', [
                'idReserva' => $idReserva,
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);

            if (config('app.debug')) {
                abort(response()->json([
                    'message' => 'Error creando preferencia de pago',
                    'error'   => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ], 500));
            }

            abort(500, 'Error creando preferencia de pago');
        }
    }

    /* =========================================================
     *  PAQUETE
     * ========================================================= */

    public function createPreferenciaPaquete(int $idPaqueteComprado, ?string $urlRetorno = null): array
    {
        $paquete = PaqueteComprado::with(['paqueteServicio.servicio', 'cliente.usuario'])
            ->findOrFail($idPaqueteComprado);

        if ($paquete->estado !== 'pendiente') {
            abort(409, 'Solo paquetes pendientes pueden pagarse.');
        }

        if ($paquete->pago && $paquete->pago->estado === 'aprobado') {
            abort(409, 'Este paquete ya fue pagado.');
        }

        $frontendUrl     = rtrim(config('mercadopago.frontend_url'), '/');
        $apiUrl          = rtrim(config('app.api_url'), '/');
        $notificationUrl = $apiUrl . '/api/mercadopago/webhook';

        $backUrls = [
            'success' => $frontendUrl . '/paquetes-comprados/' . $idPaqueteComprado . '/confirmacion?status=approved',
            'failure' => $frontendUrl . '/paquetes-comprados/' . $idPaqueteComprado . '/confirmacion?status=failure',
            'pending' => $frontendUrl . '/paquetes-comprados/' . $idPaqueteComprado . '/confirmacion?status=pending',
        ];

        if (empty($backUrls['success'])) {
            abort(500, 'FRONTEND_URL no está configurado. No se puede crear la preferencia de pago.');
        }

        $payload = [
            'items' => [[
                'id'          => "paquete_{$idPaqueteComprado}",
                'title'       => ($paquete->paqueteServicio->servicio->nombre ?? 'Paquete') .
                    " - {$paquete->totalSesiones} sesiones",
                'quantity'    => 1,
                'currency_id' => 'UYU',
                'unit_price'  => (float) $paquete->precioCompra,
            ]],
            'payer' => [
                'name'  => $paquete->cliente->usuario->nombre ?? 'Cliente',
                'email' => $paquete->cliente->usuario->email ?? '',
            ],
            'back_urls'          => $backUrls,
            'auto_return'        => 'approved',
            'external_reference' => "paquete_{$idPaqueteComprado}",
            'notification_url'   => $notificationUrl,
        ];

        Log::info('MercadoPago preference payload paquete', [
            'idPaqueteComprado' => $idPaqueteComprado,
            'back_urls'         => $backUrls,
            'notification_url'  => $notificationUrl,
            'frontend_url'      => $frontendUrl,
            'api_url'           => $apiUrl,
            'item_price'        => $payload['items'][0]['unit_price'],
        ]);

        try {
            $preference = $this->preferenceClient->create($payload);

            return [
                'checkout_url'  => $preference->init_point,
                'preference_id' => $preference->id,
            ];
        } catch (MPApiException $e) {
            $apiResponse = method_exists($e, 'getApiResponse')
                ? $e->getApiResponse()?->getContent()
                : null;

            Log::error('MercadoPago error paquete', [
                'idPaqueteComprado' => $idPaqueteComprado,
                'exception'         => get_class($e),
                'message'           => $e->getMessage(),
                'code'              => $e->getCode(),
                'api_response'      => $apiResponse,
                'payload'           => $payload,
            ]);

            if (config('app.debug')) {
                abort(response()->json([
                    'message'      => 'Error creando preferencia de pago',
                    'error'        => $e->getMessage(),
                    'api_response' => $apiResponse,
                    'payload'      => $payload,
                ], 500));
            }

            abort(500, 'Error creando preferencia de pago');
        } catch (\Throwable $e) {
            Log::error('MercadoPago excepción inesperada paquete', [
                'idPaqueteComprado' => $idPaqueteComprado,
                'exception'         => get_class($e),
                'message'           => $e->getMessage(),
                'file'              => $e->getFile(),
                'line'              => $e->getLine(),
            ]);

            if (config('app.debug')) {
                abort(response()->json([
                    'message' => 'Error creando preferencia de pago',
                    'error'   => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ], 500));
            }

            abort(500, 'Error creando preferencia de pago');
        }
    }

    /* =========================================================
     *  WEBHOOK
     * ========================================================= */

    public function procesarWebhook(array $data): array
    {
        Log::info('WEBHOOK ENTRÓ', $data);
        $type = $data['type'] ?? null;
        $paymentId = $data['data']['id'] ?? null;

        if ($type !== 'payment' || !$paymentId) {
            Log::warning('Webhook inválido', $data);
            return ['success' => true];
        }

        try {
            $payment = $this->paymentClient->get($paymentId);
            Log::info('ESTADO DEL PAGO', [
                'id' => $payment->id,
                'status' => $payment->status,
                'status_detail' => $payment->status_detail,
                'external_reference' => $payment->external_reference,
            ]);
            $ref = $payment->external_reference ?? null;

            if (!$ref) {
                return ['success' => true];
            }

            return match (true) {
                str_starts_with($ref, 'reserva_') => $this->procesarReserva($payment),
                str_starts_with($ref, 'paquete_') => $this->procesarPaquete($payment),
                default => ['success' => true],
            };

        } catch (MPApiException $e) {
            Log::error('Webhook error MP', [
                'message' => $e->getMessage(),
            ]);

            return ['success' => false];
        }
    }

    /* =========================================================
     *  RESERVA WEBHOOK
     * ========================================================= */

    private function procesarReserva(object $payment): array
    {
        Log::info('PROCESANDO RESERVA WEBHOOK', [
            'payment' => $payment,
        ]);
        return DB::transaction(function () use ($payment) {

            $id = (int) str_replace('reserva_', '', $payment->external_reference);

            $reserva = Reserva::findOrFail($id);

            $estado = match ($payment->status) {
                'approved' => 'aprobado',
                'pending' => 'pendiente',
                'rejected' => 'rechazado',
                'cancelled' => 'cancelado',
                default => 'pendiente',
            };
            $pago = Pago::updateOrCreate(
                ['idPago' => $reserva->idPago],
                [
                    'monto' => $payment->transaction_amount,
                    'metodoPago' => 'mercadopago',
                    'estado' => $estado,
                    'referenciaExterna' => $payment->id,
                    'fechaPago' => now(),
                ]
            );

            $reserva->idPago = $pago->idPago;

            if ($estado === 'aprobado') {
                $reserva->estado = 'confirmada';
            }

            $reserva->save();

            if ($estado === 'aprobado') {

    $email = optional($reserva->cliente->usuario)->email;

            app(NotificacionService::class)->notificar(
                $reserva->idCliente,
                $email,
                'Pago confirmado',
                'Tu reserva fue confirmada.',
                'confirmacion'
            );
        }

            return ['success' => true];
        });
    }

    /* =========================================================
     *  PAQUETE WEBHOOK
     * ========================================================= */

    private function procesarPaquete(object $payment): array
    {
        return DB::transaction(function () use ($payment) {

            $id = (int) str_replace('paquete_', '', $payment->external_reference);

            $paquete = PaqueteComprado::findOrFail($id);

            $estado = match ($payment->status) {
                'approved' => 'aprobado',
                'pending' => 'pendiente',
                'rejected' => 'rechazado',
                'cancelled' => 'cancelado',
                default => 'pendiente',
            };

            $pago = Pago::updateOrCreate(
                ['idPago' => $paquete->idPago],
                [
                    'monto' => $payment->transaction_amount,
                    'metodoPago' => 'mercadopago',
                    'estado' => $estado,
                    'referenciaExterna' => $payment->id,
                    'fechaPago' => now(),
                ]
            );

            $paquete->idPago = $pago->idPago;

            if ($estado === 'aprobado') {
                $paquete->estado = 'activo';
            }

            $paquete->save();

            return ['success' => true];
        });
    }
    public function consultarPago(string $paymentId): array
    {
        try {
            $payment = $this->paymentClient->get((int) $paymentId);
            Log::info('ESTADO ACTUAL DEL PAGO', [
                'id' => $payment->id,
                'status' => $payment->status,
                'status_detail' => $payment->status_detail,
                'external_reference' => $payment->external_reference,
            ]);

            $ref = $payment->external_reference ?? null;
            if ($ref) {
                if (str_starts_with($ref, 'reserva_')) {
                    $this->procesarReserva($payment);
                } elseif (str_starts_with($ref, 'paquete_')) {
                    $this->procesarPaquete($payment);
                }
            }

            return [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'amount' => $payment->transaction_amount,
                'external_reference' => $payment->external_reference,
                'created_at' => $payment->date_created,
            ];
        } catch (MPApiException $e) {
            Log::error('MercadoPago consultarPago error', [
                'message' => $e->getMessage(),
                'payment_id' => $paymentId,
            ]);

            abort(500, 'Error consultando pago');
        }
    }
}