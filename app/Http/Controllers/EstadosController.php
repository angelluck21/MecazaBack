<?php

namespace App\Http\Controllers;

use App\Models\Estadoscarro;
use Illuminate\Http\Request;

class EstadosController extends Controller
{
    public function Create(Request $request)
    {
        $request->validate(['Estados' => 'required|string|max:100']);

        Estadoscarro::create(['estados' => $request->Estados]);

        return response()->json([
            'message' => 'Estado guardado exitosamente',
        ], 201);
    }

    public function GetAll()
    {
        return response()->json([
            'data'    => Estadoscarro::all(),
            'message' => 'Consulta de estados exitosa',
        ], 200);
    }

    public function Update(Request $request, Estadoscarro $estado)
    {
        $request->validate(['Estados' => 'required|string|max:100']);

        $estado->update(['estados' => $request->Estados]);

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
        ], 200);
    }

    public function Destroy(Estadoscarro $estado)
    {
        $estado->delete();

        return response()->json([
            'message' => 'Estado eliminado exitosamente',
        ], 200);
    }
}
