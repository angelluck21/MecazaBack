<?php

namespace App\Http\Controllers;

use App\Models\Estadoscarro;
use Illuminate\Http\Request;

class EstadosController extends Controller
{
    public function Agregarestado(Request $request) {
        Estadoscarro::create([
            "estados" => $request->Estados,
        ]);

        return response()->json([
            "message" => "Carro guardado exitosamente"
        ], 201);
    }

    public function Updateestado(Request $request, Estadoscarro $updateestado){
        $updateestado->update([
            "estados" => $request->Estados,
        ]);
        return response()->json([
            "message" => "Actualizado exitosamente"
        ],200);
    }

    public function GetAllestado(Estadoscarro $request){
        return response()->json([
            "data" => $request->get(),
            "message" => "Consulta de carros exitosa"
        ],200);
    }

    public function Destroyestado(Estadoscarro $estadoscarro) {
        $estadoscarro->delete();

        return response()->json([
            "message" => "Jugador eliminado Exitosamente!"
        ], 200);
    }
}
