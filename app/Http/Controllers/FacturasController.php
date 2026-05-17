<?php

namespace App\Http\Controllers;

use App\Models\Faturaviaje;
use App\Models\Reservarviaje;
use App\Models\Carros;
use App\Models\Precioviajes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PDF;

class FacturasController extends Controller
{
    public function GenerarFactura(Request $request, $id_reservarviajes)
    {
        try {
            $reserva = Reservarviaje::findOrFail($id_reservarviajes);

            if ($reserva->estado !== 'Confirmada' && $reserva->estado !== 'confirmada') {
                return response()->json([
                    'message' => 'Solo se pueden generar facturas para reservas confirmadas',
                ], 400);
            }

            $carro = Carros::findOrFail($reserva->id_carros);
            $precio = Precioviajes::first() ?? (object)['valor' => 0];

            $subtotal = (float)($precio->valor ?? 50000);
            $impuesto = $subtotal * 0.19;
            $total = $subtotal + $impuesto;
            $numero_factura = 'FAC-' . date('YmdHis') . '-' . $id_reservarviajes;

            $factura = Faturaviaje::create([
                'id_users' => $reserva->id_users,
                'id_carros' => $reserva->id_carros,
                'id_precioviajes' => $precio->id_precioviajes ?? 1,
                'id_reservarviajes' => $id_reservarviajes,
                'destino' => $carro->destino,
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'total' => $total,
                'numero_factura' => $numero_factura,
            ]);

            return response()->json([
                'message' => 'Factura generada exitosamente',
                'data' => $factura->load(['usuario', 'carro', 'reserva']),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al generar factura', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al generar la factura',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function DescargarFactura($id_factura)
    {
        try {
            $factura = Faturaviaje::findOrFail($id_factura);
            $usuario = $factura->usuario;
            $reserva = $factura->reserva;
            $carro = $factura->carro;

            $html = view('facturas.pdf', [
                'factura' => $factura,
                'usuario' => $usuario,
                'reserva' => $reserva,
                'carro' => $carro,
            ])->render();

            $pdf = PDF::loadHTML($html)
                ->setPaper('a4')
                ->setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);

            return $pdf->download("factura-{$factura->numero_factura}.pdf");
        } catch (\Exception $e) {
            Log::error('Error al descargar factura PDF', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al descargar la factura',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function GetByReserva($id_reservarviajes)
    {
        try {
            $factura = Faturaviaje::where('id_reservarviajes', $id_reservarviajes)
                ->with(['usuario', 'carro', 'reserva'])
                ->first();

            if (!$factura) {
                return response()->json([
                    'message' => 'Factura no encontrada',
                ], 404);
            }

            return response()->json([
                'data' => $factura,
                'message' => 'Factura obtenida exitosamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener factura', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al obtener la factura',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function GetByUsuario($id_users)
    {
        try {
            $facturas = Faturaviaje::where('id_users', $id_users)
                ->with(['usuario', 'carro', 'reserva'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => $facturas,
                'message' => 'Facturas del usuario obtenidas exitosamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener facturas del usuario', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al obtener las facturas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function GetAll()
    {
        return response()->json([
            'data' => Faturaviaje::with(['usuario', 'carro', 'reserva'])->get(),
            'message' => 'Consulta de facturas exitosa',
        ], 200);
    }
}
