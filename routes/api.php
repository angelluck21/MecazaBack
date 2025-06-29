<?php

use App\Http\Controllers\RegistroController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




// Rutas para el registro de usuarios
Route::prefix('Registro')->group(function () {
    Route::post('/Registro', [RegistroController::class, 'Registro']); // Crear registro
    Route::get('/registro', [RegistroController::class, 'index']); // Obtener todos los registros
    Route::get('/registro/{id}', [RegistroController::class, 'show']); // Obtener un registro específico
});

// Ruta de prueba para verificar que la API funciona
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'mensaje' => 'API de Mecaza funcionando correctamente',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
