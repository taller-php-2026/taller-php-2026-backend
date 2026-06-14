<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ProfesionalController;
use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\ServicioComunController;
use App\Http\Controllers\Api\UbicacionController;
use App\Http\Controllers\Api\ReglaDisponibilidadController;
use App\Http\Controllers\Api\ResenaController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\VideoSesionController;
use App\Http\Controllers\Api\AgendaController;
use App\Http\Controllers\Api\HorarioController;
use App\Http\Controllers\Api\ExcepcionDisponibilidadController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\DisponibilidadController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\PaqueteServicioController;
use App\Http\Controllers\Api\PaqueteCompradoController;
use App\Http\Controllers\Api\CicloController;
use App\Http\Controllers\Api\RangoHorarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ReservaSlotController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\MercadoPagoController;
use App\Http\Controllers\Api\ProfesionalMetricasController;

Route::apiResource('clientes', ClienteController::class);
Route::get('profesionales/{id}/disponibilidad', [DisponibilidadController::class, 'porProfesional']);
Route::post('profesionales/{id}/reservar-slot', [ReservaSlotController::class, 'reservar'])->middleware('auth:sanctum');

// Mercado Pago — webhook público (MP lo llama desde sus servidores)
Route::post('mercadopago/webhook', [MercadoPagoController::class, 'webhook']);
Route::get('mercadopago/pago/{paymentId}', [MercadoPagoController::class, 'consultarPago']);
Route::get('servicios/buscar', [ServicioController::class, 'buscar']);
Route::get('servicios/{id}/profesionales', [ServicioController::class, 'profesionales']);
Route::get('servicios/{id}', [ServicioController::class, 'show']);
Route::apiResource('servicio-comun', ServicioComunController::class);
Route::apiResource('ubicaciones', UbicacionController::class);
Route::apiResource('resenas', ResenaController::class);
Route::apiResource('video-sesiones', VideoSesionController::class);
Route::apiResource('horarios', HorarioController::class);
Route::apiResource('notificaciones', NotificacionController::class);
Route::apiResource('pagos', PagoController::class);
Route::post('paquete-servicios/{id}/comprar', [PaqueteCompradoController::class, 'comprar']);
Route::post('paquetes-comprados/{id}/pagar', [PaqueteCompradoController::class, 'pagar']);
Route::get('clientes/{id}/paquetes', [PaqueteCompradoController::class, 'porCliente']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);
Route::middleware('auth:sanctum')->group(function () {
    Route::patch('profesionales/{id}/perfil', [ProfesionalController::class, 'updatePerfil']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me/perfil', [UsuarioController::class, 'actualizarMiPerfil']);
    Route::post('/me/imagen', [UsuarioController::class, 'subirMiImagen']);
    Route::get('/me/reservas', [ReservaController::class, 'misReservas']);
    Route::get('/me/profesional/metricas', [ProfesionalMetricasController::class, 'misMetricas']);
    Route::get('/me/profesional/reservas', [ReservaController::class, 'misReservasProfesional']);
    Route::get('/me/profesional/servicios', [ServicioController::class, 'misServiciosProfesional']);
    Route::get('/me/profesional/agendas', [AgendaController::class, 'misAgendasProfesional']);
    Route::get('/me/profesional/excepciones', [ExcepcionDisponibilidadController::class, 'misExcepcionesProfesional']);
    Route::post('/auth/completar-perfil', [AuthController::class, 'completarPerfil']);
    Route::post('reservas/{id}/video-token', [ReservaController::class, 'videoToken']);
    Route::post('reservas/{id}/cancelar', [ReservaController::class, 'cancelar']);
    Route::apiResource('reservas', ReservaController::class);
    Route::post('reservas/cancelar-vencidas', [ReservaController::class, 'cancelarVencidas']);
    Route::post('reservas/{id}/pagar', [ReservaController::class, 'pagar']);
    Route::post('reservas/{id}/reprogramar', [ReservaController::class, 'reprogramar']);
    Route::post('reservas/{id}/completar', [ReservaController::class, 'completar']);
    Route::post('reservas/{id}/resena', [ReservaController::class, 'resena']);

    // Mercado Pago — requieren usuario autenticado
    Route::post('reservas/{id}/mercadopago',           [MercadoPagoController::class, 'crearPreferenciaReserva']);
    Route::post('paquetes-comprados/{id}/mercadopago', [MercadoPagoController::class, 'crearPreferenciaPaquete']);

    Route::get('profesionales/{id}/metricas', [ProfesionalMetricasController::class, 'metricas']);
    Route::get('profesionales', [ProfesionalController::class, 'index']);
    Route::get('profesionales/{id}', [ProfesionalController::class, 'show']);
    Route::post('profesionales', [ProfesionalController::class, 'store']);
    Route::put('profesionales/{id}', [ProfesionalController::class, 'update']);
    Route::patch('profesionales/{id}', [ProfesionalController::class, 'update']);
    Route::delete('profesionales/{id}', [ProfesionalController::class, 'destroy']);

    Route::get('usuarios', [UsuarioController::class, 'index']);
    Route::get('usuarios/{id}', [UsuarioController::class, 'show']);
    Route::post('usuarios', [UsuarioController::class, 'store']);
    Route::put('usuarios/{id}', [UsuarioController::class, 'update']);
    Route::patch('usuarios/{id}', [UsuarioController::class, 'update']);
    Route::delete('usuarios/{id}', [UsuarioController::class, 'destroy']);
    Route::post('usuarios/{id}/imagen', [UsuarioController::class, 'subirImagen']);

    Route::get('servicios', [ServicioController::class, 'index']);
    Route::post('servicios', [ServicioController::class, 'store']);
    Route::put('servicios/{id}', [ServicioController::class, 'update']);
    Route::patch('servicios/{id}', [ServicioController::class, 'update']);
    Route::delete('servicios/{id}', [ServicioController::class, 'destroy']);
    Route::post('servicios/{id}/imagen', [ServicioController::class, 'subirImagen']);

    Route::post('paquete-servicios', [PaqueteServicioController::class, 'store']);
    Route::put('paquete-servicios/{id}', [PaqueteServicioController::class, 'update']);
    Route::patch('paquete-servicios/{id}', [PaqueteServicioController::class, 'update']);
    Route::delete('paquete-servicios/{id}', [PaqueteServicioController::class, 'destroy']);
    Route::post('paquete-servicios/{id}/imagen', [PaqueteServicioController::class, 'subirImagen']);

    Route::apiResource('ciclos', CicloController::class);
    Route::apiResource('rangos-horarios', RangoHorarioController::class);
    Route::apiResource('agendas', AgendaController::class);
    Route::apiResource('reglas-disponibilidad', ReglaDisponibilidadController::class);
    Route::apiResource('excepciones-disponibilidad', ExcepcionDisponibilidadController::class);

    Route::prefix('admin')->group(function () {
        Route::get('logs', [ActivityLogController::class, 'index']);
        Route::get('metricas',               [AdminController::class, 'metricas']);
        Route::get('reservas/profesionales', [AdminController::class, 'reservasPorProfesional']);
        Route::get('reservas/servicios',     [AdminController::class, 'reservasPorServicio']);
        Route::get('reservas',               [AdminController::class, 'reservas']);
        Route::get('paquetes/resumen',       [AdminController::class, 'resumenPaquetes']);
        Route::get('paquetes/servicios',     [AdminController::class, 'paquetesPorServicio']);
        Route::get('paquetes',               [AdminController::class, 'paquetes']);
    });
});
