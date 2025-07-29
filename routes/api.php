<?php

use App\Http\Controllers\AgregarcarrosController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\EstadosController;
use App\Http\Controllers\PrecioviajeController;
use App\Http\Controllers\ReservarviajeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/registro', [RegistroController::class, 'Create']);
Route::get("/listarusuario", [RegistroController::class,"GetAll"]);
Route::get("/listarusuariotodo", [RegistroController::class,"traerUsuario"]);
Route::put("/actualizarusuario/{user}", [RegistroController::class,"Update"]);
Route::delete("/eliminarusuario/{user}",[RegistroController::class,"Destroy"]);
Route::post("/login", [RegistroController::class, "LoginUsuario"]);      //////

Route::get("/listarcarro", [AgregarcarrosController::class,"GetAll"]);
Route::put("/actualizarcarro/{agregarcarros}", [AgregarcarrosController::class,"Update"]);
Route::delete("/eliminarcarro/{agregarcarros}",[AgregarcarrosController::class,"Destroy"]);
Route::put("/actualizarestadocarro/{id_carros}", [AgregarcarrosController::class,"UpdateEstado"]); /////
Route::post("/asignarasietoscarros", [AgregarcarrosController::class, "AgregarAsientos"]); //////

Route::post("/agregarestados",[EstadosController::class,"Create"]);
Route::get("/listarestados", [EstadosController::class,"GetAll"]);
Route::put("/actualizarestados/{updateestado}", [EstadosController::class,"Update"]);
Route::delete("/eliminarestados/{estadoscarro}",[EstadosController::class,"Destroy"]);

Route::post("/agregarprecio",[PrecioviajeController::class,"Create"]);
Route::get("/listarprecio", [PrecioviajeController::class,"GetAll"]);
Route::put("/actualizarprecio/{precios}", [PrecioviajeController::class,"Update"]);
Route::delete("/eliminarprecio/{precios}",[PrecioviajeController::class,"Destroy"]);

Route::post("/agregarreserva", [ReservarviajeController::class,"Create"]);
Route::get("/listarreserva", [ReservarviajeController::class,"GetAll"]);
Route::put("/actualizarreserva/{reservarviaje}", [ReservarviajeController::class,"Update"]);
Route::delete("/eliminarreserva/{reservarviaje}",[ReservarviajeController::class,"Destroy"]);

// Ruta de prueba para verificar que la API funciona
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'mensaje' => 'API de Mecaza funcionando correctamente',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});
Route::post("/agregarreserva",[ReservarviajeController::class,"Create"])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
