<?php

namespace App\Http\Controllers;

use App\Models\Reservarviaje;
use Illuminate\Http\Request;

class ReservarviajeController extends Controller
{
    public function Create(Request $request) {
        $reservar = new Reservarviaje();
        $reservar-> regate = $request->Regate;
        $reservar-> comentario =$request->Nombre;
        $reservar-> ubicacion = $request->Ubicacion;
        $reservar-> asiento = $request->Asiento;
        $reservar-> id_users = $request->user()->id_users;

        $reservar ->save();

        return response()->json([
            "message" => "viaje reservado exitosamente",
            "data" => $reservar->load('usuario'),
        ], 201);
    }

    public function GetAll(Reservarviaje $reservarviaje){
        return response()->json([
            "data" => $reservarviaje->with('usuario')->get(),
            "message" => "Consulta de reserva exitosa"
        ],200);
    }

    public function Update(Request $request, Reservarviaje $reservarviaje){
        $reservarviaje->update([
            "regate" => $request->Regate,
            "comentario" => $request->Nombre,
            "ubicacion" => $request->Ubicacion,
            "asiento" => $request->Asiento,
            "id_users" => $request->Usuario,
        ]);

        return response()->json([
            "message" => "Reserva actualizada exitosamente"
        ],200);
    }

    public function Destroy(Reservarviaje $reservarviaje) {
        $reservarviaje->delete();

        return response()->json([
            "message" => "Reserva eliminada Exitosamente!"
        ], 200);
    }
}
