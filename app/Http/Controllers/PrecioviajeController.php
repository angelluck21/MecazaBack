<?php

namespace App\Http\Controllers;

use App\Models\Precioviajes;
use Illuminate\Http\Request;

class PrecioviajeController extends Controller
{
    public function Create(Request $request)
    {
        Precioviajes::create([
            'zara-mede'  => $request->ZaraMede,
            'zara-cauca' => $request->ZaraCauca,
            'cauca-mede' => $request->CaucaMede,
        ]);

        return response()->json([
            'message' => 'Precios guardados exitosamente',
        ], 201);
    }

    public function GetAll()
    {
        return response()->json([
            'data'    => Precioviajes::all(),
            'message' => 'Consulta de precios exitosa',
        ], 200);
    }

    public function Update(Request $request, Precioviajes $precio)
    {
        $precio->update([
            'zara-mede'  => $request->ZaraMede,
            'zara-cauca' => $request->ZaraCauca,
            'cauca-mede' => $request->CaucaMede,
        ]);

        return response()->json([
            'message' => 'Precios actualizados exitosamente',
        ], 200);
    }

    public function Destroy(Precioviajes $precio)
    {
        $precio->delete();

        return response()->json([
            'message' => 'Precio eliminado exitosamente',
        ], 200);
    }
}
