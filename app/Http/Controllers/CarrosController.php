<?php

namespace App\Http\Controllers;
use App\Models\Carros;
use App\Models\Estadoscarro;
use Illuminate\Http\Request;

class CarrosController extends Controller
{
    public function Create(Request $request) {
        $carro = new Carros();
        $carro-> conductor = $request->Conductor;
        $carro-> telefono =$request->Telefono;
        $carro-> placa = $request->Placa;
        $carro-> asientos = $request->Asientos;
        $carro-> destino = $request->Destino;
        $carro-> horasalida =$request->Horasalida;
        $carro-> fecha = $request->Fecha;
        $carro->id_estados = $request->Estado;
        $carro->id_users = $request->Userid;

        $carro ->save();

        return response()->json([
            "message" => "carro agregado",
            "data" => $carro->load('estado'),
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
