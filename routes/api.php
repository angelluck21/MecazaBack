<?php

use App\Http\Controllers\agregarcarrosController;
use App\Http\Controllers\RegistroController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas para el registro de usuarios
Route::post('/registro', [RegistroController::class, 'Registro']); // Crear registro
Route::post("/login", [RegistroController::class, "Login"]);

Route::post("/agregarcarros", [agregarcarrosController::class, "Agregarcarros"]);

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
