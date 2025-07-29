<?php

namespace App\Http\Controllers;
use App\Models\Carros;
use App\Models\Estadoscarro;
use Illuminate\Http\Request;

class agregarcarrosController extends Controller
{

    public function Agregarcarros(Request $request) {
        Carros::create([
            "conductor" => $request->Conductor,
            "telefono" => $request->Telefono,
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

    public function GetAll(carros $request){
        return response()->json([
            "data" => $request->get(),
            "message" => "Consulta de carros exitosa"
        ],200);
    }

    public function Update(Request $request, Carros $agregarcarros){
        $agregarcarros->update([
            "conductor" => $request->Conductor,
            "imagencarro" => $request->Imagencarro,
            "placa" => $request->Placa,
            "asientos" => $request->Asientos,
            "destino" => $request->Destino,
            "horasalida" => $request->Horasalida,
            "fecha" => $request->Fecha,
        ]);

        return response()->json([
            "message" => "Actualizado exitosamente"
        ],200);
    }

    public function updateestado(Request $request, Estadoscarro $estadocarros){
        $estadocarros->update([
            "estado" => $request->estado
        ]);
        return response()->json([
            "message" => "Actualizado exitosamente"
        ],200);


    }

    public function Destroy(Carros $agregarcarros) {
        $agregarcarros->delete();

        return response()->json([
            "message" => "Jugador eliminado Exitosamente!"
        ], 200);
    }
}
