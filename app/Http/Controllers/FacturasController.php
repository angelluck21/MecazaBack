<?php

namespace App\Http\Controllers;

use App\Models\Faturaviaje;
use App\Models\Reservarviaje;
use App\Models\Carros;
use App\Models\Precioviajes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

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

            $carro = Carros::with('precioviaje')->findOrFail($reserva->id_carros);

            $precioviaje    = $carro->precioviaje;
            $subtotal       = $precioviaje ? (float) $precioviaje->precio : 50000.0;
            $impuesto       = $subtotal * 0.10;
            $total          = $subtotal - $impuesto;
            $numero_factura = 'FAC-' . date('YmdHis') . '-' . $id_reservarviajes;

            $factura = Faturaviaje::create([
                'id_users'          => $reserva->id_users,
                'id_carros'         => $reserva->id_carros,
                'id_precioviajes'   => $precioviaje?->id_precioviajes ?? 1,
                'id_reservarviajes' => $id_reservarviajes,
                'origen'            => $precioviaje?->origen  ?? '',
                'destino'           => $precioviaje?->destino ?? '',
                'subtotal'          => $subtotal,
                'impuesto'          => $impuesto,
                'total'             => $total,
                'numero_factura'    => $numero_factura,
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
            $carro   = Carros::with('precioviaje')->find($factura->id_carros);

            $html = view('facturas.pdf', [
                'factura' => $factura,
                'usuario' => $usuario,
                'reserva' => $reserva,
                'carro'   => $carro,
            ])->render();

            $pdf = Pdf::loadHTML($html)
                ->setPaper('a4')
                ->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true]);

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
                ->with(['usuario', 'carro.precioviaje', 'reserva'])
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

    public function GetByUsuario(Request $request)
    {
        try {
            $facturas = Faturaviaje::where('id_users', $request->user()->id_users)
                ->with(['usuario', 'carro.precioviaje', 'reserva'])
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
        return response()->json(
            Faturaviaje::with(['usuario', 'carro.precioviaje', 'reserva'])
                ->orderBy('created_at', 'desc')
                ->paginate(10)
        );
    }

    public function DescargarTodas()
    {
        $facturas = Faturaviaje::with(['usuario', 'carro.precioviaje', 'reserva'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($facturas->isEmpty()) {
            return response()->json(['message' => 'No hay facturas para descargar.'], 404);
        }

        $zipPath = storage_path('app/temp_facturas_' . now()->format('YmdHis') . '.zip');
        $zip     = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'No se pudo crear el archivo ZIP.'], 500);
        }

        foreach ($facturas as $factura) {
            try {
                $carro   = Carros::with('precioviaje')->find($factura->id_carros);
                $usuario = $factura->usuario;
                $reserva = $factura->reserva;

                $html = view('facturas.pdf', [
                    'factura' => $factura,
                    'usuario' => $usuario,
                    'reserva' => $reserva,
                    'carro'   => $carro,
                ])->render();

                $pdf     = Pdf::loadHTML($html)->setPaper('a4')
                    ->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true]);
                $content = $pdf->output();

                $nombre = preg_replace('/[^A-Za-z0-9\-_]/', '_', $factura->numero_factura ?? "FAC-{$factura->id_factura}");
                $zip->addFromString("{$nombre}.pdf", $content);
            } catch (\Exception $e) {
                Log::error('Error al generar PDF para ZIP', ['id' => $factura->id_factura, 'error' => $e->getMessage()]);
            }
        }

        $zip->close();

        return response()->download($zipPath, 'facturas_mecaza_' . now()->format('Ymd') . '.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
