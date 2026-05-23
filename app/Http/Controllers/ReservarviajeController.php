<?php

namespace App\Http\Controllers;

use App\Models\Reservarviaje;
use App\Models\Carros;
use App\Models\User;
use App\Models\Faturaviaje;
use App\Models\Precioviajes;
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
            // Bloquear si el carro ya está en viaje
            $carroSolicitado = Carros::find($request->id_carros);
            if ($carroSolicitado && intval($carroSolicitado->id_estados) === 2) {
                return response()->json([
                    'message' => 'Este viaje ya está en curso y no acepta nuevas reservas.',
                ], 422);
            }

            $reservar               = new Reservarviaje();
            $reservar->nombre       = $request->Nombre;
            $reservar->ubicacion    = $request->Ubicacion;
            $reservar->tel          = $request->user()->tel ?? $request->Telefono;
            $reservar->asiento      = $request->Asiento;
            $reservar->id_users     = $request->user()->id_users;
            $reservar->id_carros    = $request->id_carros;
            $reservar->viaje_numero = $carroSolicitado->viaje_numero ?? 1;
            $reservar->save();

            // Notificar al conductor por email
            $carro = Carros::with('precioviaje')->find($request->id_carros);
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

            // Webhook a N8N — notificación al conductor + WhatsApp
            $webhookReserva = env('N8N_WEBHOOK_RESERVA');
            if ($webhookReserva && $carro) {
                $conductorUser = User::where('name', $carro->conductor)->first();
                try {
                    Http::timeout(5)->post($webhookReserva, [
                        'conductor'         => $carro->conductor ?? '',
                        'conductor_email'   => $conductorUser?->email ?? '',
                        'conductor_tel'     => $carro->telefono ?? '',
                        'pasajero_nombre'   => $request->user()->name ?? $request->Nombre ?? 'Pasajero',
                        'pasajero_tel'      => $request->user()->tel ?? $request->Telefono ?? '',
                        'pasajero_email'    => $request->user()->email ?? '',
                        'ubicacion'         => $request->Ubicacion,
                        'asiento'           => $request->Asiento,
                        'origen'            => $carro->precioviaje?->origen  ?? '',
                        'destino'           => $carro->precioviaje?->destino ?? '',
                        'fecha'             => $carro->fecha ?? '',
                        'hora'              => $carro->horasalida ?? '',
                        'placa'             => $carro->placa ?? '',
                        'reserva_id'        => $reservar->id_reservarviajes ?? '',
                        'fecha_reserva'     => $reservar->created_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
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
        return response()->json(
            Reservarviaje::with(['usuario', 'carro.precioviaje'])
                ->orderBy('created_at', 'desc')
                ->paginate(10)
        );
    }

    public function MisReservas(Request $request)
    {
        $userId = $request->user()->id_users;
        $carIds = \App\Models\Carros::where('id_users', $userId)->pluck('id_carros');

        return response()->json(
            Reservarviaje::with(['usuario', 'carro.precioviaje'])
                ->whereIn('id_carros', $carIds)
                ->orderBy('created_at', 'desc')
                ->paginate(10)
        );
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
            'estado' => 'required|string|in:Confirmada,confirmada,rechazada,cancelada',
            'motivo' => 'nullable|string|max:1000',
        ]);

        $oldEstado = $reservarviaje->estado;
        $newEstado = $request->estado;

        $reservarviaje->estado = $newEstado;
        $reservarviaje->save();

        if ($request->filled('motivo')) {
            $tipo = 'usuario';
            if ($request->user()->rol === 'conductor') $tipo = 'conductor';
            if ($request->user()->rol === 'admin' || $request->user()->rol === 'administrador') $tipo = 'admin';

            \App\Models\MotivosCancelacion::create([
                'id_reservarviajes' => $reservarviaje->id_reservarviajes,
                'id_users' => $request->user()->id_users,
                'motivo' => $request->motivo,
                'tipo' => $tipo,
            ]);

            $reservarviaje->motivo_cancelacion = $request->motivo;
            $reservarviaje->cancelado_por = $tipo;
            $reservarviaje->fecha_cancelacion = now();
            $reservarviaje->save();
        }

        // Cargar usuario y carro relacionados
        $usuario = User::find($reservarviaje->id_users);
        $carro   = Carros::with('precioviaje')->find($reservarviaje->id_carros);

        if ($usuario && $carro) {
            // Eliminar factura si la reserva se cancela o rechaza
            if (in_array(strtolower($newEstado), ['cancelada', 'rechazada'])) {
                Faturaviaje::where('id_reservarviajes', $reservarviaje->id_reservarviajes)->delete();
            }

            // Webhook N8N — notificación al pasajero (confirmación o rechazo)
            $webhookConfirmacion = env('N8N_WEBHOOK_CONFIRMACION');
            if ($webhookConfirmacion) {
                try {
                    Http::timeout(5)->post($webhookConfirmacion, [
                        'pasajero_nombre' => $usuario->name,
                        'pasajero_email'  => $usuario->email,
                        'pasajero_tel'    => $usuario->tel ?? '',
                        'estado'          => $newEstado,
                        'conductor'       => $carro->conductor ?? '',
                        'placa'           => $carro->placa ?? '',
                        'origen'          => $carro->precioviaje?->origen  ?? '',
                        'destino'         => $carro->precioviaje?->destino ?? '',
                        'fecha'           => $carro->fecha ?? '',
                        'hora'            => $carro->horasalida ?? '',
                        'asiento'         => $reservarviaje->asiento,
                        'reserva_id'      => $reservarviaje->id_reservarviajes,
                        'motivo'          => $request->motivo ?? '',
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al llamar webhook N8N confirmacion', ['error' => $e->getMessage()]);
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
        Faturaviaje::where('id_reservarviajes', $reservarviaje->id_reservarviajes)->delete();
        $reservarviaje->delete();

        return response()->json([
            'message' => 'Reserva eliminada exitosamente',
        ], 200);
    }

    public function Completar(Request $request, Reservarviaje $reservarviaje)
    {
        $estado = strtolower($reservarviaje->estado ?? '');

        if ($estado !== 'confirmada') {
            return response()->json([
                'message' => 'Solo puedes completar reservas que estén confirmadas.',
            ], 400);
        }

        $reservarviaje->estado = 'completada';
        $reservarviaje->save();

        return response()->json([
            'message' => '¡Viaje completado! Gracias por viajar con Mecaza.',
            'data'    => $reservarviaje,
        ], 200);
    }

    public function Calificar(Request $request, Reservarviaje $reservarviaje)
    {
        $request->validate([
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario'   => 'nullable|string|max:500',
        ]);

        if (strtolower($reservarviaje->estado) !== 'completada') {
            return response()->json(['message' => 'Solo puedes calificar viajes completados.'], 400);
        }

        if ($reservarviaje->calificacion !== null) {
            return response()->json(['message' => 'Ya calificaste este viaje.'], 400);
        }

        $reservarviaje->calificacion            = $request->calificacion;
        $reservarviaje->comentario_calificacion = $request->comentario;
        $reservarviaje->save();

        return response()->json([
            'message' => '¡Calificación registrada. Gracias!',
            'data'    => $reservarviaje,
        ], 200);
    }
}
