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
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\PaqueteServicioController;
use App\Http\Controllers\Api\CicloController;
use App\Http\Controllers\Api\RangoHorarioController;    

Route::apiResource('clientes', ClienteController::class);
Route::apiResource('profesionales', ProfesionalController::class);
Route::apiResource('reservas', ReservaController::class);
Route::post('reservas/{id}/pagar', [ReservaController::class, 'pagar']);
Route::apiResource('servicios', ServicioController::class);
Route::apiResource('servicio-comun', ServicioComunController::class);
Route::apiResource('ubicaciones', UbicacionController::class);
Route::apiResource('reglas-disponibilidad', ReglaDisponibilidadController::class);
Route::apiResource('resenas', ResenaController::class);
Route::apiResource('usuarios', UsuarioController::class);
Route::apiResource('video-sesiones', VideoSesionController::class);
Route::apiResource('agendas', AgendaController::class);
Route::apiResource('horarios', HorarioController::class);
Route::apiResource('excepciones-disponibilidad', ExcepcionDisponibilidadController::class);
Route::apiResource('notificaciones', NotificacionController::class);
Route::apiResource('pagos', PagoController::class);
Route::apiResource('paquete-servicios', PaqueteServicioController::class);
Route::apiResource('ciclos', CicloController::class);
Route::apiResource('rangos-horarios', RangoHorarioController::class);