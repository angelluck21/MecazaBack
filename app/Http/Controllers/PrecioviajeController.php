<?php

namespace App\Http\Controllers;

use App\Models\Precioviajes;
use Illuminate\Http\Request;

class PrecioviajeController extends Controller
{
    public function Create(Request $request)
    {
        $request->validate([
            'Origen'  => 'required|string|max:255',
            'Destino' => 'required|string|max:255',
            'Precio'  => 'required|numeric|min:0',
        ]);

        Precioviajes::create([
            'origen'  => $request->Origen,
            'destino' => $request->Destino,
            'precio'  => $request->Precio,
        ]);

        return response()->json([
            'message' => 'Precio guardado exitosamente',
        ], 201);
    }

    public function GetAll(Request $request)
    {
        if ($request->filled('page')) {
            return response()->json(
                Precioviajes::orderBy('created_at', 'desc')->paginate(15)
            );
        }

        return response()->json([
            'data'    => Precioviajes::orderBy('created_at', 'desc')->get(),
            'message' => 'Consulta de precios exitosa',
        ], 200);
    }

    public function Update(Request $request, Precioviajes $precio)
    {
        $request->validate([
            'Origen'  => 'sometimes|string|max:255',
            'Destino' => 'sometimes|string|max:255',
            'Precio'  => 'sometimes|numeric|min:0',
        ]);

        $precio->update([
            'origen'  => $request->Origen  ?? $precio->origen,
            'destino' => $request->Destino ?? $precio->destino,
            'precio'  => $request->Precio  ?? $precio->precio,
        ]);

        return response()->json([
            'message' => 'Precio actualizado exitosamente',
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
