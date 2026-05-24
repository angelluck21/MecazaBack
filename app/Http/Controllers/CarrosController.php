<?php

namespace App\Http\Controllers;

use App\Models\Carros;
use App\Models\Notificacion;
use App\Models\Reservarviaje;
use App\Models\Faturaviaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Rules\RealImageMime;

class CarrosController extends Controller
{
    private function isAdmin(Request $request): bool
    {
        $rol = strtolower($request->user()?->rol ?? '');
        return $rol === 'admin' || $rol === 'administrador';
    }

    // ── Crear vehículo (solo datos del carro, sin viaje) ─────────────────────────
    public function Create(Request $request)
    {
        $request->validate([
            'Imagencarro' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048', new RealImageMime()],
            'Conductor'   => 'required|string',
            'Telefono'    => 'required|string',
            'Placa'       => 'required|string|unique:carros,placa',
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
                'asientos'       => 4,
                'id_estados'     => 4, // Fuera de servicio por defecto
                'id_users'       => $request->user()->id_users,
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
            'fecha'          => 'required|date|after_or_equal:today',
        ]);

        if (!$this->isAdmin($request) && $carro->id_users !== $request->user()->id_users) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

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

    // ── Listar carros activos — público, con filtros + paginación ────────────────
    public function GetAll(Request $request)
    {
        $query = Carros::with('reservas', 'precioviaje')
            ->whereNotIn('id_estados', [4, 5]);

        if ($request->filled('origen')) {
            $query->whereHas('precioviaje', fn($q) => $q->where('origen', $request->origen));
        }
        if ($request->filled('destino')) {
            $query->whereHas('precioviaje', fn($q) => $q->where('destino', $request->destino));
        }
        if ($request->filled('fecha')) {
            $query->where('fecha', $request->fecha);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('conductor', 'like', "%$s%")
                ->orWhere('placa',   'like', "%$s%")
                ->orWhereHas('precioviaje', fn($q2) => $q2
                    ->where('origen',  'like', "%$s%")
                    ->orWhere('destino','like', "%$s%")
                )
            );
        }

        return response()->json($query->paginate(10));
    }

    // ── Listar TODOS los carros (admin, todos los estados) — paginado ─────────────
    public function GetAllAdmin()
    {
        return response()->json(
            Carros::with(['reservas', 'precioviaje', 'estado'])
                ->orderBy('updated_at', 'desc')
                ->paginate(10)
        );
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
        if (!$this->isAdmin($request) && $carro->id_users !== $request->user()->id_users) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

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
        $request->validate(['id_estados' => 'required|integer|in:1,2,3,4,5']);

        if (!$this->isAdmin($request) && $carro->id_users !== $request->user()->id_users) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $carro->update(['id_estados' => $request->id_estados]);

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
            'data'    => $carro->fresh()->load('estado'),
        ], 200);
    }

    public function Destroy(Request $request, Carros $carro)
    {
        if (!$this->isAdmin($request) && $carro->id_users !== $request->user()->id_users) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $carro->delete();
        return response()->json(['message' => 'Carro eliminado exitosamente'], 200);
    }

    public function IniciarViaje(Request $request, Carros $carro)
    {
        if (!$this->isAdmin($request) && $carro->id_users !== $request->user()->id_users) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $carro->update(['id_estados' => 2]);

        // Notify all confirmed passengers
        try {
            $carro->load('precioviaje');
            $ruta = ($carro->precioviaje?->origen ?? '') . ' → ' . ($carro->precioviaje?->destino ?? '');
            Reservarviaje::where('id_carros', $carro->id_carros)
                ->whereRaw('LOWER(estado) = ?', ['confirmada'])
                ->pluck('id_users')
                ->each(function ($userId) use ($carro, $ruta) {
                    Notificacion::crear(
                        $userId,
                        '¡Tu viaje ha comenzado!',
                        'El viaje ' . $ruta . ' con ' . $carro->conductor . ' ha iniciado. ¡Buen viaje!',
                        'success',
                        ['id_carros' => $carro->id_carros]
                    );
                });
        } catch (\Exception $e) {
            Log::error('Error al crear notificaciones de inicio de viaje', ['error' => $e->getMessage()]);
        }

        return response()->json(['message' => 'Viaje iniciado correctamente.', 'data' => $carro->fresh()], 200);
    }

    public function TerminarViaje(Request $request, Carros $carro)
    {
        if (!$this->isAdmin($request) && $carro->id_users !== $request->user()->id_users) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $reservas = Reservarviaje::where('id_carros', $carro->id_carros)
            ->whereRaw('LOWER(estado) = ?', ['confirmada'])
            ->get();

        foreach ($reservas as $reserva) {
            $reserva->estado = 'completada';
            $reserva->save();

            // Notify passenger: trip completed, rate it
            try {
                $ruta = ($carro->precioviaje?->origen ?? '') . ' → ' . ($carro->precioviaje?->destino ?? '');
                Notificacion::crear(
                    $reserva->id_users,
                    'Viaje completado',
                    '¡Llegaste! El viaje ' . $ruta . ' ha finalizado. Califica tu experiencia.',
                    'info',
                    ['id_reservarviajes' => $reserva->id_reservarviajes]
                );
            } catch (\Exception $e) {
                Log::error('Error al crear notificación de viaje completado', ['error' => $e->getMessage()]);
            }

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
                        'numero_factura'    => 'FAC-' . now()->format('YmdHis') . '-' . $reserva->id_reservarviajes,
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // Unique constraint de BD: factura ya existe, ignorar
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

        return response()->json(
            Reservarviaje::with(['usuario', 'carro.precioviaje'])
                ->whereHas('carro', fn($q) => $q->where('id_users', $userId))
                ->where('estado', 'completada')
                ->orderBy('updated_at', 'desc')
                ->paginate(10)
        );
    }
}
