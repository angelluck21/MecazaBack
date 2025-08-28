<?php

namespace App\Http\Controllers;

use App\Models\Reservarviaje;
use App\Models\Carros;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Mail\NotificacionReservaConductor;

class ReservarviajeController extends Controller
{
    /* public function Create(Request $request) {
         try {
             // Crear la reserva
             $reservar = new Reservarviaje();
             $reservar->nombre = $request->Nombre;
             $reservar->ubicacion = $request->Ubicacion;
             $reservar->tel = $request->Telefono;
             $reservar->asiento = $request->Asiento;
             $reservar->id_users = $request->user()->id_users;
             $reservar->tel = $request->user()->Telefono;
             $reservar->id_carros = $request->id_carros;

             $reservar->save();

             // Cargar las relaciones
             $reservar->load('usuario');

             // Obtener información del carro
             $carro = Carros::find($request->id_carros);

             if ($carro) {
                 // Obtener información del conductor
                 $conductor = User::where('Nombre', $carro->conductor)
                     ->orWhere('nombre', $carro->conductor)
                     ->orWhere('name', $carro->conductor)
                     ->first();

                 if ($conductor && $conductor->email) {
                     try {
                         // Preparar datos para el email
                         $emailData = [
                             'conductor' => $carro->conductor,
                             'pasajero' => $request->user()->Nombre ?? $request->user()->nombre ?? 'No especificado',
                             'telefono' => $request->user()->Telefono ?? 'No especificado',
                             'ubicacion' => $request->Ubicacion,
                             'asiento' => $request->Asiento,
                             'nombre' => $request->Nombre,
                             'tel' => $request->Telefono,
                             'placa' => $carro->placa,
                             'destino' => $carro->destino,
                             'fecha' => $carro->fecha,
                             'horasalida' => $carro->horasalida,
                             'fecha_reserva' => $reservar->created_at ? $reservar->created_at->format('d/m/Y H:i:s') : 'No especificada'
                         ];

                         // Enviar email al conductor
                         Mail::to($conductor->email)
                             ->send(new NotificacionReservaConductor($emailData));

                         Log::info('Email enviado exitosamente al conductor', [
                             'conductor_email' => $conductor->email,
                             'reserva_id' => $reservar->id
                         ]);
                     } catch (\Exception $e) {
                         Log::error('Error al enviar email al conductor', [
                             'error' => $e->getMessage(),
                             'conductor_email' => $conductor->email,
                             'reserva_id' => $reservar->id
                         ]);
                     }
                 } else {
                     Log::warning('No se pudo encontrar el conductor o su email', [
                         'conductor_nombre' => $carro->conductor ?? 'No especificado',
                         'reserva_id' => $reservar->id
                     ]);
                 }
             }

             return response()->json([
                 "message" => "viaje reservado exitosamente",
                 "data" => $reservar->load('usuario'),
             ], 201);

         } catch (\Exception $e) {
             Log::error('Error al crear reserva', [
                 'error' => $e->getMessage(),
                 'request_data' => $request->all()
             ]);

             return response()->json([
                 "message" => "Error al crear la reserva",
                 "error" => $e->getMessage()
             ], 500);
         }
     }
 */
           public function Create(Request $request) {
                $reservar = new Reservarviaje();
                $reservar-> nombre =$request->Nombre;
                $reservar-> tel =$request->Telefono;
                $reservar-> ubicacion = $request->Ubicacion;
                $reservar-> asiento = $request->Asiento;
                $reservar-> id_users = $request->user()->id_users;
                $reservar-> id_carros = $request->id_carros;

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
