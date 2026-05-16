<?php

use App\Http\Controllers\CarrosController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\InvitacionController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\EstadosController;
use App\Http\Controllers\PrecioviajeController;
use App\Http\Controllers\ReservarviajeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Públicas: lectura sin token ───────────────────────────────────────────────
Route::post('/login',       [RegistroController::class,    'LoginUsuario']);
Route::post('/registrar',   [RegistroController::class,    'Create']);
Route::post('/auth/google', [GoogleAuthController::class,  'handleGoogleAuth']);

// Invitación de conductores (públicas — el token es la seguridad)
Route::get('/validar-invitacion/{token}',       [InvitacionController::class, 'ValidarToken']);
Route::post('/registrar-conductor/{token}',     [InvitacionController::class, 'RegistrarConductor']);

Route::get('/listarcarro',  [CarrosController::class,       'GetAll']);
Route::get('/listarestados',[EstadosController::class,      'GetAll']);
Route::get('/listarprecios',[PrecioviajeController::class,  'GetAll']);
Route::get('/listarreserva',[ReservarviajeController::class,'GetAll']);

// ── Requieren token ───────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Ruta de usuario autenticado
    Route::get('/user', fn(Request $r) => $r->user());

    // Usuarios
    Route::get('/listarusuarios',            [RegistroController::class, 'GetAll']);
    Route::get('/verusuario/{user}',         [RegistroController::class, 'Show']);
    Route::put('/actualizarusuario/{user}',  [RegistroController::class, 'Update']);
    Route::post('/actualizarusuario/{user}', [RegistroController::class, 'Update']); // para FormData con foto
    Route::delete('/eliminarusuario/{user}', [RegistroController::class, 'Destroy']);

    // Carros
    Route::post('/crearcarro',                      [CarrosController::class, 'Create']);
    Route::put('/actualizarcarro/{carro}',          [CarrosController::class, 'Update']);
    Route::put('/actualizarestado/{carro}',         [CarrosController::class, 'UpdateEstado']);
    Route::delete('/eliminarcarro/{carro}',         [CarrosController::class, 'Destroy']);

    // Estados
    Route::post('/agregarestados',                  [EstadosController::class,     'Create']);
    Route::put('/actualizarestados/{estado}',       [EstadosController::class,     'Update']);
    Route::delete('/eliminarestados/{estado}',      [EstadosController::class,     'Destroy']);

    // Precios
    Route::post('/agregarprecio',                   [PrecioviajeController::class, 'Create']);
    Route::put('/actualizarprecio/{precio}',        [PrecioviajeController::class, 'Update']);
    Route::delete('/eliminarprecio/{precio}',       [PrecioviajeController::class, 'Destroy']);

    // Invitación conductores (protegida — solo admin)
    Route::post('/invitar-conductor', [InvitacionController::class, 'Invitar']);

    // Reservas
    Route::post('/crearreserva',                            [ReservarviajeController::class, 'Create']);
    Route::put('/actualizarreserva/{reservarviaje}',        [ReservarviajeController::class, 'Update']);
    Route::put('/confirmarreserva/{reservarviaje}',         [ReservarviajeController::class, 'Confirmar']);
    Route::delete('/eliminarreserva/{reservarviaje}',       [ReservarviajeController::class, 'Destroy']);
});

// ── Ruta de prueba ────────────────────────────────────────────────────────────
Route::get('/test', fn() => response()->json([
    'success'   => true,
    'mensaje'   => 'API de Mecaza funcionando correctamente',
    'timestamp' => now(),
    'version'   => '1.0.0',
]));
