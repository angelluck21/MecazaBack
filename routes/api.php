<?php

use App\Http\Controllers\CarrosController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\InvitacionController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\PrecioviajeController;
use App\Http\Controllers\ReservarviajeController;
use App\Http\Controllers\MotivosCancelacionController;
use App\Http\Controllers\FacturasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Públicas: sin token ───────────────────────────────────────────────────────
Route::post('/login',       [RegistroController::class,   'LoginUsuario']);
Route::post('/registrar',   [RegistroController::class,   'Create']);
Route::post('/auth/google', [GoogleAuthController::class, 'handleGoogleAuth']);

// Invitación de conductores (el token es la seguridad)
Route::get('/validar-invitacion/{token}',   [InvitacionController::class, 'ValidarToken']);
Route::post('/registrar-conductor/{token}', [InvitacionController::class, 'RegistrarConductor']);

Route::get('/listarcarro',               [CarrosController::class,        'GetAll']);
Route::get('/listarprecios',             [PrecioviajeController::class,   'GetAll']);
Route::get('/listarreserva',             [ReservarviajeController::class, 'GetAll']);
Route::get('/conductor-perfil/{id_users}', [RegistroController::class,   'PerfilConductor']);
Route::get('/usuario-perfil/{id_users}',  [RegistroController::class,   'PerfilUsuario']);

// ── Requieren token ───────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [RegistroController::class, 'Logout']);

    // Perfil del usuario autenticado
    Route::get('/user', fn(Request $r) => $r->user());
    Route::get('/verusuario/{user}',         [RegistroController::class, 'Show']);
    Route::put('/actualizarusuario/{user}',  [RegistroController::class, 'Update']);
    Route::post('/actualizarusuario/{user}', [RegistroController::class, 'Update']); // FormData con foto

    // Reservas (cualquier usuario autenticado puede reservar/cancelar/calificar)
    Route::post('/crearreserva',                          [ReservarviajeController::class, 'Create']);
    Route::put('/actualizarreserva/{reservarviaje}',      [ReservarviajeController::class, 'Update']);
    Route::put('/confirmarreserva/{reservarviaje}',       [ReservarviajeController::class, 'Confirmar']);
    Route::put('/completarreserva/{reservarviaje}',       [ReservarviajeController::class, 'Completar']);
    Route::put('/calificarreserva/{reservarviaje}',       [ReservarviajeController::class, 'Calificar']);
    Route::delete('/eliminarreserva/{reservarviaje}',     [ReservarviajeController::class, 'Destroy']);

    // Motivos de cancelación (usuarios y conductores)
    Route::post('/guardarMotivoCancelacion',              [MotivosCancelacionController::class, 'Create']);
    Route::get('/motivosCancelacion/{id_reservarviajes}', [MotivosCancelacionController::class, 'GetByReserva']);

    // Reservas propias del usuario
    Route::get('/mis-reservas-usuario',   [ReservarviajeController::class, 'MisReservasUsuario']);
    Route::get('/mis-reservas-historial', [ReservarviajeController::class, 'HistorialUsuario']);

    // Notificaciones in-app
    Route::get('/notificaciones',              [NotificacionController::class, 'MisNotificaciones']);
    Route::get('/notificaciones/contador',     [NotificacionController::class, 'ContadorNoLeidas']);
    Route::put('/notificaciones/leer-todas',   [NotificacionController::class, 'MarcarTodasLeidas']);
    Route::put('/notificaciones/{id}/leida',   [NotificacionController::class, 'MarcarLeida']);
    Route::delete('/notificaciones',           [NotificacionController::class, 'EliminarTodas']);

    // Exportar datos personales (GDPR)
    Route::get('/exportar-mis-datos', [RegistroController::class, 'ExportarMisDatos']);

    // Facturas propias
    Route::post('/generarFactura/{id_reservarviajes}',   [FacturasController::class, 'GenerarFactura']);
    Route::get('/facturaReserva/{id_reservarviajes}',    [FacturasController::class, 'GetByReserva']);
    Route::get('/descargarFactura/{id_factura}',         [FacturasController::class, 'DescargarFactura']);
    Route::get('/misFacturas',                           [FacturasController::class, 'GetByUsuario']);

    // ── Conductor + Admin ─────────────────────────────────────────────────────
    Route::middleware('role:conductor,admin')->group(function () {
        Route::post('/crearcarro',                  [CarrosController::class, 'Create']);
        Route::put('/actualizarcarro/{carro}',      [CarrosController::class, 'Update']);
        Route::put('/actualizarestado/{carro}',     [CarrosController::class, 'UpdateEstado']);
        Route::delete('/eliminarcarro/{carro}',     [CarrosController::class, 'Destroy']);
        Route::post('/iniciarviajenotify/{carro}',  [CarrosController::class, 'IniciarViaje']);
        Route::post('/terminarviaje/{carro}',       [CarrosController::class, 'TerminarViaje']);
        Route::put('/asignarviaje/{carro}',         [CarrosController::class, 'AsignarViaje']);
        Route::get('/mis-carros',                   [CarrosController::class, 'MisCarros']);
        Route::get('/mis-reservas',                 [ReservarviajeController::class, 'MisReservas']);
        Route::get('/historial-conductor',                          [CarrosController::class,        'HistorialConductor']);
        Route::put('/calificar-pasajero/{reservarviaje}',           [ReservarviajeController::class, 'CalificarPasajero']);
    });

    // ── Solo Admin ────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        // Usuarios
        Route::get('/listarusuarios',            [RegistroController::class, 'GetAll']);
        Route::delete('/eliminarusuario/{user}', [RegistroController::class, 'Destroy']);

        // Carros (vista admin)
        Route::get('/listarcarros-admin',        [CarrosController::class, 'GetAllAdmin']);

        // Precios
        Route::post('/agregarprecio',                 [PrecioviajeController::class, 'Create']);
        Route::put('/actualizarprecio/{precio}',      [PrecioviajeController::class, 'Update']);
        Route::delete('/eliminarprecio/{precio}',     [PrecioviajeController::class, 'Destroy']);

        // Invitaciones
        Route::post('/invitar-conductor',             [InvitacionController::class, 'Invitar']);

        // Motivos (vista completa)
        Route::get('/listarMotivos',                  [MotivosCancelacionController::class, 'GetAll']);

        // Facturas (vista completa)
        Route::get('/listarFacturas',                 [FacturasController::class, 'GetAll']);
        Route::get('/descargarTodasFacturas',         [FacturasController::class, 'DescargarTodas']);
    });
});

// ── Ruta de prueba ────────────────────────────────────────────────────────────
Route::get('/test', fn() => response()->json([
    'success'   => true,
    'mensaje'   => 'API de Mecaza funcionando correctamente',
    'timestamp' => now(),
    'version'   => '1.0.0',
]));
