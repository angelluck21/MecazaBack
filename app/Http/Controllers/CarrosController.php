<?php

namespace App\Http\Controllers;

use App\Models\Carros;
use App\Models\Reservarviaje;
use App\Models\Faturaviaje;
use App\Models\Precioviajes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CarrosController extends Controller
{
    public function Create(Request $request)
    {
        $request->validate([
            'Imagencarro' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'Conductor'   => 'required|string',
            'Telefono'    => 'required|string',
            'Placa'       => 'required|string|unique:carros,placa',
            'Asientos'    => 'required|integer|min:1|max:4',
            'Origen'      => 'nullable|string|max:255',
            'Destino'     => 'required|string',
            'Horasalida'  => 'required|string',
            'Fecha'       => 'required|date',
            'Estado'      => 'required|integer',
            'Userid'      => 'required|integer',
        ]);

        try {
            $urlImagen = null;

            if ($request->hasFile('Imagencarro')) {
                $imagen       = $request->file('Imagencarro');
                $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();

                $ok = Storage::disk('public')->putFileAs('carros', $imagen, $nombreImagen);

                if (!$ok || !Storage::disk('public')->exists("carros/{$nombreImagen}")) {
                    Log::error('No se pudo guardar la imagen del carro', [
                        'path'  => "carros/{$nombreImagen}",
                        'disk'  => 'public',
                    ]);
                    abort(500, 'No se pudo guardar la imagen.');
                }

                $urlImagen = Storage::url("carros/{$nombreImagen}");
            }

            $carro = Carros::create([
                'conductor'   => $request->Conductor,
                'imagencarro' => $urlImagen,
                'telefono'    => $request->Telefono,
                'placa'       => $request->Placa,
                'asientos'    => $request->Asientos,
                'origen'      => $request->Origen,
                'destino'     => $request->Destino,
                'horasalida'  => $request->Horasalida,
                'fecha'       => $request->Fecha,
                'id_estados'  => $request->Estado,
                'id_users'    => $request->Userid,
            ]);

            return response()->json([
                'message' => 'Carro agregado exitosamente',
                'data'    => $carro->load('estado'),
                'imagen'  => $urlImagen,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al crear carro', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Error al guardar el carro',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function GetAll()
    {
        return response()->json([
            'data'    => Carros::with('reservas')->get(),
            'message' => 'Consulta de carros exitosa',
        ], 200);
    }

    public function Update(Request $request, Carros $carro)
    {
        $carro->update([
            'conductor'  => $request->Conductor,
            'placa'      => $request->Placa,
            'asientos'   => $request->Asientos,
            'origen'     => $request->Origen,
            'destino'    => $request->Destino,
            'horasalida' => $request->Horasalida,
            'fecha'      => $request->Fecha,
        ]);

        return response()->json([
            'message' => 'Carro actualizado exitosamente',
        ], 200);
    }

    public function UpdateEstado(Request $request, Carros $carro)
    {
        $request->validate([
            'id_estados' => 'required|integer',
        ]);

        $carro->update([
            'id_estados' => $request->id_estados,
        ]);

        return response()->json([
            'message' => 'Estado actualizado exitosamente',
            'data'    => $carro->fresh()->load('estado'),
        ], 200);
    }

    public function Destroy(Carros $carro)
    {
        $carro->delete();

        return response()->json([
            'message' => 'Carro eliminado exitosamente',
        ], 200);
    }

    public function IniciarViaje(Request $request, Carros $carro)
    {
        // Poner el carro en estado "En viaje" (id_estados = 2)
        $carro->update(['id_estados' => 2]);

        return response()->json([
            'message' => 'Viaje iniciado correctamente.',
            'data'    => $carro->fresh(),
        ], 200);
    }

    public function TerminarViaje(Request $request, Carros $carro)
    {
        // 1. Completar todas las reservas confirmadas de este carro y generar factura
        $reservas = Reservarviaje::where('id_carros', $carro->id_carros)
            ->whereIn('estado', ['Confirmada', 'confirmada'])
            ->get();

        foreach ($reservas as $reserva) {
            $reserva->estado = 'completada';
            $reserva->save();

            // Generar factura si aún no tiene
            $tieneFactura = Faturaviaje::where('id_reservarviajes', $reserva->id_reservarviajes)->exists();
            if (!$tieneFactura) {
                try {
                    $precio   = Precioviajes::first();
                    $subtotal = Precioviajes::getPrecioParaRuta($carro->origen ?? null, $carro->destino ?? null);
                    $impuesto = $subtotal * 0.19;
                    Faturaviaje::create([
                        'id_users'          => $reserva->id_users,
                        'id_carros'         => $reserva->id_carros,
                        'id_precioviajes'   => $precio?->id_precioviajes ?? 1,
                        'id_reservarviajes' => $reserva->id_reservarviajes,
                        'origen'            => $carro->origen ?? '',
                        'destino'           => $carro->destino ?? '',
                        'subtotal'          => $subtotal,
                        'impuesto'          => $impuesto,
                        'total'             => $subtotal + $impuesto,
                        'numero_factura'    => 'FAC-' . now()->format('YmdHis') . rand(10,99) . '-' . $reserva->id_reservarviajes,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error al generar factura al terminar viaje', ['error' => $e->getMessage()]);
                }
            }
        }

        // 2. Eliminar el carro
        $carro->delete();

        return response()->json([
            'message' => 'Viaje finalizado. El vehículo ha sido eliminado.',
        ], 200);
    }
}
