<?php

use App\Http\Controllers\agregarcarrosController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\EstadosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas para el registro de usuarios
Route::post("/login", [RegistroController::class, "Login"]);

Route::post('/registro', [RegistroController::class, 'Registro']);
Route::get("/listarusuario", [RegistroController::class,"GetAll"]);
Route::put("/actualizarusuario/{usuario}", [RegistroController::class,"Update"]);
Route::delete("/eliminarusuario/{usuario}",[RegistroController::class,"Destroy"]);

Route::post("/agregarcarros", [agregarcarrosController::class, "Agregarcarros"]);
Route::get("/listarcarro", [agregarcarrosController::class,"GetAll"]);
Route::put("/actualizarcarro/{carro}", [agregarcarrosController::class,"Update"]);
Route::delete("/eliminarcarro/{carro}",[agregarcarrosController::class,"Destroy"]);

Route::put("/actualizarestadocarro/{estado}", [agregarcarrosController::class,"updateestado"]);

Route::post("/agregarestados",[EstadosController::class,"Agregarestado"]);
Route::put("/listarestados/", [EstadosController::class,"GetAllestado"]);
Route::get("/actualizarestados/{estado}", [EstadosController::class,"Updateestado"]);
Route::delete("/eliminarestados/{estado}",[EstadosController::class,"Destroyestado"]);


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
