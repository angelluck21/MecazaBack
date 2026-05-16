<?php

use App\Http\Controllers\CarrosController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\EstadosController;
use App\Http\Controllers\PrecioviajeController;
use App\Http\Controllers\ReservarviajeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::post('/login',     [RegistroController::class, 'LoginUsuario']);
Route::post('/registrar', [RegistroController::class, 'Create']);

// ── Usuarios ──────────────────────────────────────────────────────────────────
Route::get('/listarusuarios',           [RegistroController::class, 'GetAll']);
Route::get('/verusuario/{user}',        [RegistroController::class, 'Show']);
Route::put('/actualizarusuario/{user}', [RegistroController::class, 'Update']);
Route::delete('/eliminarusuario/{user}',[RegistroController::class, 'Destroy']);

// ── Carros ────────────────────────────────────────────────────────────────────
Route::get('/listarcarro',                      [CarrosController::class, 'GetAll']);
Route::post('/crearcarro',                      [CarrosController::class, 'Create']);
Route::put('/actualizarcarro/{carro}',          [CarrosController::class, 'Update']);
Route::put('/actualizarestado/{carro}',         [CarrosController::class, 'UpdateEstado']);
Route::delete('/eliminarcarro/{carro}',         [CarrosController::class, 'Destroy']);

// ── Estados ───────────────────────────────────────────────────────────────────
Route::get('/listarestados',                    [EstadosController::class, 'GetAll']);
Route::post('/agregarestados',                  [EstadosController::class, 'Create']);
Route::put('/actualizarestados/{estado}',       [EstadosController::class, 'Update']);
Route::delete('/eliminarestados/{estado}',      [EstadosController::class, 'Destroy']);

// ── Precios ───────────────────────────────────────────────────────────────────
Route::get('/listarprecios',                    [PrecioviajeController::class, 'GetAll']);
Route::post('/agregarprecio',                   [PrecioviajeController::class, 'Create']);
Route::put('/actualizarprecio/{precio}',        [PrecioviajeController::class, 'Update']);
Route::delete('/eliminarprecio/{precio}',       [PrecioviajeController::class, 'Destroy']);

// ── Reservas (requieren token) ────────────────────────────────────────────────
Route::get('/listarreserva',                            [ReservarviajeController::class, 'GetAll']);
Route::delete('/eliminarreserva/{reservarviaje}',       [ReservarviajeController::class, 'Destroy']);
Route::put('/actualizarreserva/{reservarviaje}',        [ReservarviajeController::class, 'Update']);
Route::put('/confirmarreserva/{reservarviaje}',         [ReservarviajeController::class, 'Confirmar']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/crearreserva', [ReservarviajeController::class, 'Create']);
    Route::get('/user',          fn(Request $r) => $r->user());
});

// ── Ruta de prueba ────────────────────────────────────────────────────────────
Route::get('/test', fn() => response()->json([
    'success'   => true,
    'mensaje'   => 'API de Mecaza funcionando correctamente',
    'timestamp' => now(),
    'version'   => '1.0.0',
]));
