<?php

namespace App\Http\Controllers;
use App\Models\agregarcarros;
use Illuminate\Http\Request;

class agregarcarrosController extends Controller
{
    public function Agregarcarros(Request $request) {
        agregarcarros::create([
            "conductor" => $request->Conductor,
            "imagencarro" => $request->Imagencarro,
            "placa" => $request->Placa,
            "asientos" => $request->Asientos,
            "destino" => $request->Destino,
            "horasalida" => $request->Horasalida,
            "fecha" => $request->Fecha,
        ]);

        return response()->json([
            "message" => "Carro guardado exitosamente"
        ], 201);

    }

}
