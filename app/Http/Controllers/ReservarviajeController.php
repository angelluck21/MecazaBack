<?php

namespace App\Http\Controllers;

use App\Models\Reservarviaje;
use App\Models\Carros;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificacionReservaConductor;

class ReservarviajeController extends Controller
{
    public function Create(Request $request)
    {
        $request->validate([
            'Nombre'    => 'required|string|max:255',
            'Ubicacion' => 'required|string|max:500',
            'Asiento'   => 'required|integer|min:1|max:4',
            'id_carros' => 'required|integer|exists:carros,id_carros',
        ]);

        try {
            $reservar            = new Reservarviaje();
            $reservar->nombre    = $request->Nombre;
            $reservar->ubicacion = $request->Ubicacion;
            $reservar->tel       = $request->user()->tel ?? $request->Telefono;
            $reservar->asiento   = $request->Asiento;
            $reservar->id_users  = $request->user()->id_users;
            $reservar->id_carros = $request->id_carros;
            $reservar->save();

            // Notificar al conductor por email
            $carro = Carros::find($request->id_carros);
            if ($carro) {
                $conductor = User::where('name', $carro->conductor)->first();
                if ($conductor?->email) {
                    try {
                        Mail::to($conductor->email)->send(new NotificacionReservaConductor([
                            'conductor'    => $carro->conductor,
                            'pasajero'     => $request->user()->name ?? 'No especificado',
                            'telefono'     => $request->user()->tel ?? 'No especificado',
                            'ubicacion'    => $request->Ubicacion,
                            'asiento'      => $request->Asiento,
                            'nombre'       => $request->Nombre,
                            'tel'          => $request->Telefono,
                            'placa'        => $carro->placa,
                            'destino'      => $carro->destino,
                            'fecha'        => $carro->fecha,
                            'horasalida'   => $carro->horasalida,
                            'fecha_reserva'=> $reservar->created_at?->format('d/m/Y H:i:s') ?? 'No especificada',
                        ]));
                    } catch (\Exception $e) {
                        Log::error('Error al enviar email al conductor', [
                            'error'           => $e->getMessage(),
                            'conductor_email' => $conductor->email,
                        ]);
                    }
                }
            }

            return response()->json([
                'message' => 'Viaje reservado exitosamente',
                'data'    => $reservar->load('usuario'),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al crear reserva', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al crear la reserva',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function GetAll()
    {
        return response()->json([
            'data'    => Reservarviaje::with(['usuario', 'carro'])->get(),
            'message' => 'Consulta de reservas exitosa',
        ], 200);
    }

    public function Update(Request $request, Reservarviaje $reservarviaje)
    {
        $reservarviaje->update([
            'ubicacion' => $request->Ubicacion,
            'asiento'   => $request->Asiento,
        ]);

        return response()->json([
            'message' => 'Reserva actualizada exitosamente',
        ], 200);
    }

    public function Confirmar(Request $request, Reservarviaje $reservarviaje)
    {
        $request->validate([
            'estado' => 'required|string|in:Confirmada,rechazada,cancelada',
        ]);

        $reservarviaje->estado = $request->estado;
        $reservarviaje->save();

        return response()->json([
            'message' => 'Reserva actualizada exitosamente',
            'data'    => $reservarviaje,
        ], 200);
    }

    public function Destroy(Reservarviaje $reservarviaje)
    {
        $reservarviaje->delete();

        return response()->json([
            'message' => 'Reserva eliminada exitosamente',
        ], 200);
    }
}
