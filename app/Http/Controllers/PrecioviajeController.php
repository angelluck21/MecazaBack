<?php

namespace App\Http\Controllers;

use App\Models\Precioviajes;
use Illuminate\Http\Request;

class PrecioviajeController extends Controller
{
    public function Create(Request $request) {
        Precioviajes::create([
            "zara-mede" => $request->ZaraMede,
            "zara-cauca" => $request->ZaraCauca,
            "cauca-mede" => $request->CaucaMede,
        ]);

        return response()->json([
            "message" => "Precios guardado exitosamente"
        ], 201);
    }

    public function GetAll(Precioviajes $request){
        return response()->json([
            "data" => $request->get(),
            "message" => "Consulta de precios exitosa"
        ],200);
    }

    public function Update(Request $request, Precioviajes $precios){

        $precios->update([
            "zara-mede" => $request->ZaraMede,
            "zara-cauca" => $request->ZaraCauca,
            "cauca-mede" => $request->CaucaMede,
        ]);

        return response()->json([
            "message" => "Actualizado zzzzzzzzzexitosamente"
        ],200);
    }

    public function Destroy(Precioviajes $precios) {
        $precios->delete();

        return response()->json([
            "message" => "precio eliminado Exitosamente!"
        ], 200);
    }
}
