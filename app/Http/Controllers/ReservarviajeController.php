<?php

namespace App\Http\Controllers;

use App\Models\Reservarviaje;
use App\Models\Carros;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificacionReservaConductor;
use App\Mail\CorreoReservaConfirmada;

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
                            'telefono'     => $request->user()->tel  ?? 'No especificado',
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

            // Webhook a N8N para WhatsApp al usuario
            $webhookReserva = env('N8N_WEBHOOK_RESERVA');
            if ($webhookReserva && $carro) {
                try {
                    Http::timeout(5)->post($webhookReserva, [
                        'usuario_nombre' => $request->user()->name ?? 'Pasajero',
                        'usuario_tel'    => $request->user()->tel ?? '',
                        'ubicacion'      => $request->Ubicacion,
                        'asiento'        => $request->Asiento,
                        'conductor'      => $carro->conductor ?? '',
                        'destino'        => $carro->destino ?? '',
                        'fecha'          => $carro->fecha ?? '',
                        'hora'           => $carro->horasalida ?? '',
                        'placa'          => $carro->placa ?? '',
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al llamar webhook N8N reserva', ['error' => $e->getMessage()]);
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

        // Cargar usuario y carro relacionados
        $usuario = User::find($reservarviaje->id_users);
        $carro   = Carros::find($reservarviaje->id_carros);

        if ($usuario && $carro) {
            $datosNotificacion = [
                'pnr'            => $reservarviaje->id_reservarviajes,
                'origen'         => $usuario->name,
                'estado'         => $request->estado,
                'conductor'      => $carro->conductor,
                'destino'        => $carro->destino,
                'fecha'          => $carro->fecha,
                'hora'           => $carro->horasalida,
                'asiento'        => $reservarviaje->asiento,
                'placa'          => $carro->placa,
                'usuario_nombre' => $usuario->name,
                'usuario_email'  => $usuario->email,
                'usuario_tel'    => $usuario->tel ?? '',
            ];

            // Email al usuario
            try {
                Mail::to($usuario->email)->send(new CorreoReservaConfirmada($datosNotificacion));
            } catch (\Exception $e) {
                Log::error('Error al enviar email de confirmación al usuario', [
                    'error'         => $e->getMessage(),
                    'usuario_email' => $usuario->email,
                ]);
            }

            // Webhook a N8N para WhatsApp + notificaciones adicionales
            $webhookUrl = env('N8N_WEBHOOK_URL');
            if ($webhookUrl) {
                try {
                    Http::timeout(5)->post($webhookUrl, $datosNotificacion);
                } catch (\Exception $e) {
                    Log::error('Error al llamar webhook N8N', ['error' => $e->getMessage()]);
                }
            }
        }

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
