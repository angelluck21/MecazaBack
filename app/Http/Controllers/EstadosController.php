<?php

namespace App\Http\Controllers;

use App\Models\Estadoscarro;
use Illuminate\Http\Request;

class EstadosController extends Controller
{
    public function Create(Request $request) {
        Estadoscarro::create([
            "estados" => $request->Estados,
        ]);

        return response()->json([
            "message" => "Estado guardado exitosamente"
        ], 201);
    }

    public function Update(Request $request, Estadoscarro $updateestado){
        $updateestado->update([
            "estados" => $request->Estados,
        ]);
        return response()->json([
            "message" => "Actualizado exitosamente"
        ],200);
    }

    public function GetAll(Estadoscarro $request){
        return response()->json([
            "data" => $request->get(),
            "message" => "Consulta de carros exitosa"
        ],200);
    }

    public function Destroy(Estadoscarro $estadoscarro) {
        $estadoscarro->delete();

        return response()->json([
            "message" => "Estado eliminado Exitosamente!"
        ], 200);
    }
}
