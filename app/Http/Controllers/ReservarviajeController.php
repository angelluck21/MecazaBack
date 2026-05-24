<?php

namespace App\Http\Controllers;

use App\Models\Reservarviaje;
use App\Models\Carros;
use App\Models\Notificacion;
use App\Models\User;
use App\Models\Faturaviaje;
use App\Models\Precioviajes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificacionReservaConductor;
use App\Mail\CorreoReservaConfirmada;
use App\Jobs\SendWebhookJob;

class ReservarviajeController extends Controller
{
    private function isAdmin(Request $request): bool
    {
        $rol = strtolower($request->user()?->rol ?? '');
        return $rol === 'admin' || $rol === 'administrador';
    }

    public function Create(Request $request)
    {
        $request->validate([
            'Nombre'    => 'required|string|max:255',
            'Ubicacion' => 'required|string|min:10|max:500',
            'Asiento'   => 'required|integer|min:1|max:4',
            'id_carros' => 'required|integer|exists:carros,id_carros',
        ]);

        try {
            // ── Transacción con bloqueo pesimista para evitar reservas simultáneas ────
            $resultado = DB::transaction(function () use ($request) {
                // lockForUpdate() impide que otro proceso lea este registro
                // hasta que la transacción termine
                $carro = Carros::lockForUpdate()->find($request->id_carros);

                if (!$carro) {
                    return ['error' => 'Vehículo no encontrado.', 'status' => 404];
                }

                if (intval($carro->id_estados) === 2) {
                    return ['error' => 'Este viaje ya está en curso y no acepta nuevas reservas.', 'status' => 422];
                }

                // Verificar que el asiento no esté tomado para este viaje
                $asientoOcupado = Reservarviaje::where('id_carros', $request->id_carros)
                    ->where('asiento', $request->Asiento)
                    ->where('viaje_numero', $carro->viaje_numero ?? 1)
                    ->whereRaw('LOWER(estado) IN (?, ?)', ['pendiente', 'confirmada'])
                    ->lockForUpdate()
                    ->exists();

                if ($asientoOcupado) {
                    return ['error' => 'Este asiento ya está reservado. Por favor elige otro.', 'status' => 422];
                }

                $reservar               = new Reservarviaje();
                $reservar->nombre       = $request->Nombre;
                $reservar->ubicacion    = $request->Ubicacion;
                $reservar->tel          = $request->user()->tel ?? $request->Telefono;
                $reservar->asiento      = $request->Asiento;
                $reservar->id_users     = $request->user()->id_users;
                $reservar->id_carros    = $request->id_carros;
                $reservar->viaje_numero = $carro->viaje_numero ?? 1;
                $reservar->save();

                return ['reserva' => $reservar];
            });

            if (isset($resultado['error'])) {
                return response()->json(['message' => $resultado['error']], $resultado['status']);
            }

            $reservar = $resultado['reserva'];

            // ── Notificaciones fuera de la transacción (no bloquean la BD) ─────────
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
            // Notificación in-app al conductor
            if ($carro) {
                $conductorUser = User::where('name', $carro->conductor)->first();
                if ($conductorUser) {
                    try {
                        Notificacion::crear(
                            $conductorUser->id_users,
                            'Nueva reserva',
                            ($request->user()->name ?? 'Un pasajero') . ' reservó el asiento ' . $request->Asiento . ' en tu viaje a ' . ($carro->precioviaje?->destino ?? 'destino'),
                            'info',
                            ['id_reservarviajes' => $reservar->id_reservarviajes, 'id_carros' => $carro->id_carros]
                        );
                    } catch (\Exception $e) {
                        Log::error('Error al crear notificación de reserva', ['error' => $e->getMessage()]);
                    }
                }
            }

            $webhookReserva = env('N8N_WEBHOOK_RESERVA');
            if ($webhookReserva && $carro) {
                $conductorUser = User::where('name', $carro->conductor)->first();
                SendWebhookJob::dispatch($webhookReserva, [
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

    public function MisReservasUsuario(Request $request)
    {
        $userId = $request->user()->id_users;

        // Only active: pendiente, confirmada, and completada-without-rating (triggers rating modal)
        $reservas = Reservarviaje::with(['carro.precioviaje'])
            ->where('id_users', $userId)
            ->where(function ($q) {
                $q->whereRaw('LOWER(estado) IN (?, ?)', ['pendiente', 'confirmada'])
                  ->orWhere(function ($q2) {
                      $q2->whereRaw('LOWER(estado) = ?', ['completada'])
                         ->whereNull('calificacion');
                  });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data'    => $reservas,
            'message' => 'Mis reservas activas',
        ], 200);
    }

    public function HistorialUsuario(Request $request)
    {
        $userId = $request->user()->id_users;

        return response()->json(
            Reservarviaje::with(['carro.precioviaje'])
                ->where('id_users', $userId)
                ->where(function ($q) {
                    $q->whereRaw('LOWER(estado) IN (?, ?)', ['rechazada', 'cancelada'])
                      ->orWhere(function ($q2) {
                          $q2->whereRaw('LOWER(estado) = ?', ['completada'])
                             ->whereNotNull('calificacion');
                      });
                })
                ->orderBy('updated_at', 'desc')
                ->paginate(10)
        );
    }

    public function Update(Request $request, Reservarviaje $reservarviaje)
    {
        if (!$this->isAdmin($request) && $reservarviaje->id_users !== $request->user()->id_users) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

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
            'estado' => 'required|string|in:confirmada,rechazada,cancelada',
            'motivo' => 'nullable|string|max:1000',
        ]);

        $authUser  = $request->user();
        $newEstado = strtolower($request->estado);

        if (!$this->isAdmin($request)) {
            if ($newEstado === 'cancelada') {
                // El pasajero cancela su propia reserva
                if ($reservarviaje->id_users !== $authUser->id_users) {
                    return response()->json(['message' => 'No autorizado.'], 403);
                }
            } else {
                // El conductor confirma o rechaza — debe ser dueño del carro
                $carro = Carros::find($reservarviaje->id_carros);
                if (!$carro || $carro->id_users !== $authUser->id_users) {
                    return response()->json(['message' => 'No autorizado.'], 403);
                }
            }
        }

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

            // Notificaciones in-app
            try {
                $ruta = ($carro->precioviaje?->origen ?? '') . ' → ' . ($carro->precioviaje?->destino ?? '');
                if ($newEstado === 'confirmada') {
                    Notificacion::crear(
                        $usuario->id_users,
                        'Reserva confirmada',
                        'Tu reserva en el viaje ' . $ruta . ' fue confirmada por ' . $carro->conductor,
                        'success',
                        ['id_reservarviajes' => $reservarviaje->id_reservarviajes]
                    );
                } elseif ($newEstado === 'rechazada') {
                    Notificacion::crear(
                        $usuario->id_users,
                        'Reserva rechazada',
                        'Tu reserva en el viaje ' . $ruta . ' fue rechazada.',
                        'warning',
                        ['id_reservarviajes' => $reservarviaje->id_reservarviajes]
                    );
                } elseif ($newEstado === 'cancelada') {
                    // Passenger cancelled → notify conductor
                    $conductorUser = User::where('name', $carro->conductor)->first();
                    if ($conductorUser) {
                        Notificacion::crear(
                            $conductorUser->id_users,
                            'Pasajero canceló reserva',
                            ($usuario->name ?? 'Un pasajero') . ' canceló su reserva del asiento ' . $reservarviaje->asiento . ' en el viaje ' . $ruta,
                            'warning',
                            ['id_reservarviajes' => $reservarviaje->id_reservarviajes]
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error al crear notificación de confirmación', ['error' => $e->getMessage()]);
            }

            // Webhook N8N — notificación al pasajero (confirmación o rechazo)
            $webhookConfirmacion = env('N8N_WEBHOOK_CONFIRMACION');
            if ($webhookConfirmacion) {
                SendWebhookJob::dispatch($webhookConfirmacion, [
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
            }
        }

        return response()->json([
            'message' => 'Reserva actualizada exitosamente',
            'data'    => $reservarviaje,
        ], 200);
    }

    public function Destroy(Request $request, Reservarviaje $reservarviaje)
    {
        if (!$this->isAdmin($request) && $reservarviaje->id_users !== $request->user()->id_users) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        Faturaviaje::where('id_reservarviajes', $reservarviaje->id_reservarviajes)->delete();
        $reservarviaje->delete();

        return response()->json([
            'message' => 'Reserva eliminada exitosamente',
        ], 200);
    }

    public function Completar(Request $request, Reservarviaje $reservarviaje)
    {
        if (!$this->isAdmin($request) && $reservarviaje->id_users !== $request->user()->id_users) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

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
        if ($reservarviaje->id_users !== $request->user()->id_users) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

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

    public function CalificarPasajero(Request $request, Reservarviaje $reservarviaje)
    {
        $carro = Carros::find($reservarviaje->id_carros);
        if (!$this->isAdmin($request)) {
            if (!$carro || $carro->id_users !== $request->user()->id_users) {
                return response()->json(['message' => 'No autorizado.'], 403);
            }
        }

        $request->validate([
            'calificacion_conductor' => 'required|integer|min:1|max:5',
            'comentario_conductor'   => 'nullable|string|max:500',
        ]);

        if (strtolower($reservarviaje->estado) !== 'completada') {
            return response()->json(['message' => 'Solo puedes calificar pasajeros de viajes completados.'], 400);
        }

        if ($reservarviaje->calificacion_conductor !== null) {
            return response()->json(['message' => 'Ya calificaste a este pasajero.'], 400);
        }

        $reservarviaje->calificacion_conductor = $request->calificacion_conductor;
        $reservarviaje->comentario_conductor   = $request->comentario_conductor;
        $reservarviaje->save();

        return response()->json([
            'message' => '¡Calificación registrada!',
            'data'    => $reservarviaje,
        ], 200);
    }
}
