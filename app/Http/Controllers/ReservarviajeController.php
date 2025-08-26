<?php

namespace App\Http\Controllers;

use App\Models\Reservarviaje;
use Illuminate\Http\Request;

class ReservarviajeController extends Controller
{
  /*  public function Create(Request $request) {
        $reservar = new Reservarviaje();
        $reservar-> comentario =$request->Nombre;
        $reservar-> ubicacion = $request->Ubicacion;
        $reservar-> asiento = $request->Asiento;
        $reservar-> id_users = $request->user()->id_users;
        $reservar-> id_carros = $request->id_carros;

        $reservar ->save();

        return response()->json([
            "message" => "viaje reservado exitosamente",
            "data" => $reservar->load('usuario'),
        ], 201);
    } */

    public function Create(Request $request)
    {
        try {
            // Crear la reserva (tu código original)
            $reservar = new Reservarviaje();
            $reservar->comentario = $request->Nombre;
            $reservar->ubicacion = $request->Ubicacion;
            $reservar->asiento = $request->Asiento;
            $reservar->id_users = $request->user()->id_users;
            $reservar->id_carros = $request->id_carros;
            $reservar->save();

            // ENVIAR EMAIL AL CONDUCTOR - NUEVO CÓDIGO
            $this->enviarEmailConductor($reservar, $request);

            return response()->json([
                "message" => "viaje reservado exitosamente",
                "data" => $reservar->load(relations: 'usuario')
            ], status: 201);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al reservar el viaje: " . $e->getMessage()
            ], status: 500);
        }
    }

    // NUEVA FUNCIÓN PARA ENVIAR EMAIL
    private function enviarEmailConductor($reservar, $request)
    {
        try {
            // Obtener información del carro y conductor
            $carro = Carros::find($request->id_carros);

            if ($carro) {
                // Datos para el email
                $emailData = [
                    'conductorNombre' => $carro->conductor ?? 'Conductor',
                    'conductorEmail' => $carro->email ?? 'conductor@mecaza.com',
                    'pasajeroNombre' => $request->Nombre,
                    'asiento' => $request->Asiento,
                    'ubicacion' => $request->Ubicacion,
                    'placa' => $carro->placa ?? 'N/A',
                    'destino' => $carro->destino ?? 'N/A',
                    'fecha' => $carro->fecha ?? 'N/A',
                    'hora' => $carro->horasalida ?? 'N/A',
                    'fechaReserva' => now()->format('d/m/Y H:i:s')
                ];

                // Enviar email usando Mailtrap
                Mail::to($carro->email)->send(new NuevaReservaConductor($emailData));

                \Log::info('Email enviado al conductor: ' . $carro->email);
            }

        } catch (\Exception $e) {
            \Log::error('Error al enviar email al conductor: ' . $e->getMessage());
            // No fallar la reserva si el email falla
        }
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
