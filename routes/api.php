<?php

use App\Http\Controllers\CarrosController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\InvitacionController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\EstadosController;
use App\Http\Controllers\PrecioviajeController;
use App\Http\Controllers\ReservarviajeController;
use App\Http\Controllers\MotivosCancelacionController;
use App\Http\Controllers\FacturasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Públicas: lectura sin token ───────────────────────────────────────────────
Route::post('/login',       [RegistroController::class,    'LoginUsuario']);
Route::post('/registrar',   [RegistroController::class,    'Create']);
Route::post('/auth/google', [GoogleAuthController::class,  'handleGoogleAuth']);

// Invitación de conductores (públicas — el token es la seguridad)
Route::get('/validar-invitacion/{token}',       [InvitacionController::class, 'ValidarToken']);
Route::post('/registrar-conductor/{token}',     [InvitacionController::class, 'RegistrarConductor']);

Route::get('/listarcarro',         [CarrosController::class,    'GetAll']);
Route::get('/listarestados',       [EstadosController::class,   'GetAll']);
Route::get('/listarprecios',       [PrecioviajeController::class,'GetAll']);
Route::get('/listarreserva',       [ReservarviajeController::class,'GetAll']);
Route::get('/conductor-perfil/{id_users}', [RegistroController::class, 'PerfilConductor']);

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
    Route::post('/iniciarviajenotify/{carro}',      [CarrosController::class, 'IniciarViaje']);
    Route::post('/terminarviaje/{carro}',           [CarrosController::class, 'TerminarViaje']);
    Route::put('/asignarviaje/{carro}',             [CarrosController::class, 'AsignarViaje']);
    Route::get('/mis-carros',                       [CarrosController::class, 'MisCarros']);
    Route::get('/listarcarros-admin',               [CarrosController::class, 'GetAllAdmin']);
    Route::get('/mis-reservas',                     [ReservarviajeController::class, 'MisReservas']);
    Route::get('/historial-conductor',              [CarrosController::class, 'HistorialConductor']);

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
    Route::put('/completarreserva/{reservarviaje}',         [ReservarviajeController::class, 'Completar']);
    Route::put('/calificarreserva/{reservarviaje}',         [ReservarviajeController::class, 'Calificar']);
    Route::delete('/eliminarreserva/{reservarviaje}',       [ReservarviajeController::class, 'Destroy']);

    // Motivos de cancelación
    Route::post('/guardarMotivoCancelacion',                [MotivosCancelacionController::class, 'Create']);
    Route::get('/motivosCancelacion/{id_reservarviajes}',   [MotivosCancelacionController::class, 'GetByReserva']);
    Route::get('/listarMotivos',                            [MotivosCancelacionController::class, 'GetAll']);

    // Facturas
    Route::post('/generarFactura/{id_reservarviajes}',      [FacturasController::class, 'GenerarFactura']);
    Route::get('/facturaReserva/{id_reservarviajes}',       [FacturasController::class, 'GetByReserva']);
    Route::get('/descargarFactura/{id_factura}',            [FacturasController::class, 'DescargarFactura']);
    Route::get('/misFacturas',                              [FacturasController::class, 'GetByUsuario']);
    Route::get('/listarFacturas',                           [FacturasController::class, 'GetAll']);
    Route::get('/descargarTodasFacturas',                   [FacturasController::class, 'DescargarTodas']);
});

// ── Ruta de prueba ────────────────────────────────────────────────────────────
Route::get('/test', fn() => response()->json([
    'success'   => true,
    'mensaje'   => 'API de Mecaza funcionando correctamente',
    'timestamp' => now(),
    'version'   => '1.0.0',
]));
