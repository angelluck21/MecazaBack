<?php

namespace App\Http\Controllers;

use App\Models\Carros;
use App\Models\Reservarviaje;
use App\Models\Faturaviaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CarrosController extends Controller
{
    // ── Crear vehículo (solo datos del carro, sin viaje) ─────────────────────────
    public function Create(Request $request)
    {
        $request->validate([
            'Imagencarro' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'Conductor'   => 'required|string',
            'Telefono'    => 'required|string',
            'Placa'       => 'required|string|unique:carros,placa',
            'Asientos'    => 'required|integer|min:1|max:20',
            'Userid'      => 'required|integer',
        ]);

        try {
            $urlImagen = null;

            if ($request->hasFile('Imagencarro')) {
                $imagen       = $request->file('Imagencarro');
                $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();

                $ok = Storage::disk('public')->putFileAs('carros', $imagen, $nombreImagen);

                if (!$ok || !Storage::disk('public')->exists("carros/{$nombreImagen}")) {
                    Log::error('No se pudo guardar la imagen del carro', ['path' => "carros/{$nombreImagen}"]);
                    abort(500, 'No se pudo guardar la imagen.');
                }

                $urlImagen = Storage::url("carros/{$nombreImagen}");
            }

            $carro = Carros::create([
                'conductor'      => $request->Conductor,
                'imagencarro'    => $urlImagen,
                'telefono'       => $request->Telefono,
                'placa'          => $request->Placa,
                'asientos'       => $request->Asientos,
                'id_estados'     => 4, // Fuera de servicio por defecto
                'id_users'       => $request->Userid,
                'id_precioviaje' => null,
                'horasalida'     => null,
                'fecha'          => null,
            ]);

            return response()->json([
                'message' => 'Vehículo registrado. Ahora asígnale un viaje.',
                'data'    => $carro->load('estado'),
                'imagen'  => $urlImagen,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al crear carro', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al guardar el vehículo',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ── Asignar viaje a un carro (estado 4 o 5) ──────────────────────────────────
    public function AsignarViaje(Request $request, Carros $carro)
    {
        $request->validate([
            'id_precioviaje' => 'required|integer',
            'horasalida'     => 'required|string',
            'fecha'          => 'required|date',
        ]);

        $estadoActual = (int) $carro->id_estados;
        if ($estadoActual !== 4 && $estadoActual !== 5) {
            return response()->json([
                'message' => 'Solo se puede asignar un viaje a vehículos fuera de servicio o con viaje terminado.',
            ], 422);
        }

        // Cancelar reservas activas del viaje anterior (pendiente y confirmada rezagadas)
        // Las completadas no se tocan — siguen en el historial del usuario
        Reservarviaje::where('id_carros', $carro->id_carros)
            ->whereRaw('LOWER(estado) IN (?, ?)', ['pendiente', 'confirmada'])
            ->update(['estado' => 'cancelada']);

        $carro->update([
            'id_precioviaje' => $request->id_precioviaje,
            'horasalida'     => $request->horasalida,
            'fecha'          => $request->fecha,
            'id_estados'     => 1,
            'viaje_numero'   => $carro->viaje_numero + 1,
        ]);

        return response()->json([
            'message' => 'Viaje asignado correctamente.',
            'data'    => $carro->fresh()->load('estado', 'precioviaje'),
        ], 200);
    }

    // ── Listar todos los carros activos (público, sin estado 4 ni 5) ─────────────
    public function GetAll()
    {
        return response()->json([
            'data'    => Carros::with('reservas', 'precioviaje')
                ->whereNotIn('id_estados', [4, 5])
                ->get(),
            'message' => 'Consulta de carros exitosa',
        ], 200);
    }

    // ── Listar TODOS los carros (admin, todos los estados) ───────────────────────
    public function GetAllAdmin()
    {
        return response()->json([
            'data'    => Carros::with(['reservas', 'precioviaje', 'estado'])
                ->orderBy('updated_at', 'desc')
                ->get(),
            'message' => 'Consulta de carros exitosa',
        ], 200);
    }

    // ── Mis carros (conductor autenticado, todos los estados) ────────────────────
    public function MisCarros(Request $request)
    {
        $userId = $request->user()->id_users;

        $carros = Carros::with(['precioviaje', 'estado', 'reservas'])
            ->where('id_users', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'data'    => $carros,
            'message' => 'Mis vehículos',
        ], 200);
    }

    public function Update(Request $request, Carros $carro)
    {
        $carro->update([
            'conductor'  => $request->Conductor,
            'placa'      => $request->Placa,
            'asientos'   => $request->Asientos,
            'horasalida' => $request->Horasalida,
            'fecha'      => $request->Fecha,
        ]);

        return response()->json(['message' => 'Carro actualizado exitosamente'], 200);
    }

    public function UpdateEstado(Request $request, Carros $carro)
    {
        $request->validate(['id_estados' => 'required|integer']);
        $carro->update(['id_estados' => $request->id_estados]);

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
            'data'    => $carro->fresh()->load('estado'),
        ], 200);
    }

    public function Destroy(Carros $carro)
    {
        $carro->delete();
        return response()->json(['message' => 'Carro eliminado exitosamente'], 200);
    }

    public function IniciarViaje(Request $request, Carros $carro)
    {
        $carro->update(['id_estados' => 2]);

        // Generar factura para cada reserva confirmada del carro
        $reservasConfirmadas = Reservarviaje::where('id_carros', $carro->id_carros)
            ->whereIn('estado', ['Confirmada', 'confirmada'])
            ->get();

        $precioRecord = $carro->load('precioviaje')->precioviaje;
        $subtotal     = $precioRecord ? (float) $precioRecord->precio : 50000.0;
        $impuesto     = $subtotal * 0.10;

        foreach ($reservasConfirmadas as $reserva) {
            $yaExiste = Faturaviaje::where('id_reservarviajes', $reserva->id_reservarviajes)->exists();
            if (!$yaExiste) {
                try {
                    Faturaviaje::create([
                        'id_users'          => $reserva->id_users,
                        'id_carros'         => $reserva->id_carros,
                        'id_precioviajes'   => $precioRecord?->id_precioviajes ?? 1,
                        'id_reservarviajes' => $reserva->id_reservarviajes,
                        'origen'            => $precioRecord?->origen ?? '',
                        'destino'           => $precioRecord?->destino ?? '',
                        'subtotal'          => $subtotal,
                        'impuesto'          => $impuesto,
                        'total'             => $subtotal - $impuesto,
                        'numero_factura'    => 'FAC-' . now()->format('YmdHis') . rand(10, 99) . '-' . $reserva->id_reservarviajes,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al generar factura al iniciar viaje', ['error' => $e->getMessage()]);
                }
            }
        }

        return response()->json(['message' => 'Viaje iniciado correctamente.', 'data' => $carro->fresh()], 200);
    }

    public function TerminarViaje(Request $request, Carros $carro)
    {
        $reservas = Reservarviaje::where('id_carros', $carro->id_carros)
            ->whereRaw('LOWER(estado) = ?', ['confirmada'])
            ->get();

        foreach ($reservas as $reserva) {
            $reserva->estado = 'completada';
            $reserva->save();

            $tieneFactura = Faturaviaje::where('id_reservarviajes', $reserva->id_reservarviajes)->exists();
            if (!$tieneFactura) {
                try {
                    $precioRecord = $carro->precioviaje;
                    $subtotal     = $precioRecord ? (float) $precioRecord->precio : 50000.0;
                    $impuesto     = $subtotal * 0.10;

                    Faturaviaje::create([
                        'id_users'          => $reserva->id_users,
                        'id_carros'         => $reserva->id_carros,
                        'id_precioviajes'   => $precioRecord?->id_precioviajes ?? 1,
                        'id_reservarviajes' => $reserva->id_reservarviajes,
                        'origen'            => $precioRecord?->origen ?? '',
                        'destino'           => $precioRecord?->destino ?? '',
                        'subtotal'          => $subtotal,
                        'impuesto'          => $impuesto,
                        'total'             => $subtotal - $impuesto,
                        'numero_factura'    => 'FAC-' . now()->format('YmdHis') . rand(10, 99) . '-' . $reserva->id_reservarviajes,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al generar factura al terminar viaje', ['error' => $e->getMessage()]);
                }
            }
        }

        $carro->update(['id_estados' => 5]);

        return response()->json(['message' => 'Viaje finalizado correctamente.'], 200);
    }

    public function HistorialConductor(Request $request)
    {
        $userId = $request->user()->id_users;

        // Traer todas las reservas completadas de los carros del conductor,
        // agrupadas por viaje_numero para mostrar cada viaje por separado.
        $reservas = Reservarviaje::with(['usuario', 'carro.precioviaje'])
            ->whereHas('carro', fn($q) => $q->where('id_users', $userId))
            ->where('estado', 'completada')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Agrupar por carro + viaje_numero + minuto exacto de completado.
        // El minuto separa viajes del mismo día aunque compartan viaje_numero=1 (datos pre-migración).
        // TerminarViaje actualiza todas las reservas del mismo viaje dentro del mismo segundo,
        // por lo que caen en el mismo minuto → mismo grupo.
        $viajes = $reservas
            ->groupBy(fn($r) => $r->id_carros . '_' . $r->viaje_numero . '_' . ($r->updated_at?->format('Y-m-d H:i') ?? 'sin_fecha'))
            ->map(function ($grupo) {
                $primera = $grupo->first();
                $carro   = $primera->carro;
                return [
                    'id_carros'    => $primera->id_carros,
                    'viaje_numero' => $primera->viaje_numero,
                    'placa'        => $carro?->placa,
                    'precioviaje'  => $carro?->precioviaje,
                    'fecha'        => $primera->updated_at?->toDateString(),
                    'horasalida'   => $carro?->horasalida,
                    'reservas'     => $grupo->values(),
                ];
            })
            ->sortByDesc('fecha')
            ->values();

        return response()->json(['data' => $viajes, 'message' => 'Historial del conductor']);
    }
}
