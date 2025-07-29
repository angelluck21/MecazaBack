<?php

namespace App\Http\Controllers;
use App\Models\Carros;
use App\Models\Estadoscarro;
use App\Models\User;
use Illuminate\Http\Request;

class AgregarcarrosController extends Controller
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

    public function AgregarAsientos(Request $request, $id_carros)
    {
        $carro = Carros::findOrFail($id_carros);
        $carro->id_asientos = $request->Asientosid; // puede ser null si deseas desasociarlo
        $carro->save();

        return response()->json([
            'message' => 'Asiento agregado correctamente al asiento.',
            'carro' => $carro
        ]);
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
            "telefono" => $request->Telefono,
            "placa" => $request->Placa,
            "asientos" => $request->Asientos,
            "destino" => $request->Destino,
            "horasalida" => $request->Horasalida,
            "fecha" => $request->Fecha,
            "id_estados" => $request->Estado,
            "id_user" => $request->Userid,
        ]);

        return response()->json([
            "message" => "Actualizado exitosamente"
        ],200);
    }

    public function UpdateEstado(Request $request, $id_carros)
    {
        $carro = Carros::findOrFail($id_carros);
        $carro->id_estados = $request->Estadoid;
        $carro->save();

        return response()->json([
            'mensaje' => 'Estado del carro actualizado correctamente.',
            'data' => $carro->load('estado'),
        ]);
    }

    public function Destroy(Carros $agregarcarros) {
        $agregarcarros->delete();

        return response()->json([
            "message" => "Carro eliminado Exitosamente!"
        ], 200);
    }
}
