<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class FaturaviajeController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'id_factura' => 'required|exists:owner_register_properties,id_propiedad',
            'id_users' => 'required|string',
            'destino' => 'required|string',
            'id_carros' => 'required|date',
            'id_precioviajes' => 'required|date',
            'detalles' => 'required|string',
        ]);

        $data = $request->only(['id_propiedad', 'propietario', 'inquilino', 'fecha', 'detalles']);
        // Generar PDF
        $pdf = Pdf::loadView('contracts.contracts', ['contracts' => $data]);

        // Generar nombre del archivo
        $nombreArchivo = 'contracts_' . Str::slug($data['inquilino']) . '_' . now()->format('Ymd_His') . '.pdf';

        // Guardar en storage/app/public/contratos
        $rutaAlmacenamiento = "public/contratos/{$nombreArchivo}";
        Storage::put($rutaAlmacenamiento, $pdf->output());

        // Guardar en base de datos
        $contracts = Owner_Register_Contract::create([
            'id_propiedad' => $data['id_propiedad'],
            'propietario' => $data['propietario'],
            'inquilino' => $data['inquilino'],
            'fecha' => $data['fecha'],
            'detalles' => $data['detalles'],
            'archivo_pdf' => Storage::url("contratos/{$nombreArchivo}"),
        ]);


        return response()->json([
            'mensaje' => 'Contrato generado y guardado exitosamente.',
            'contrato' => $contracts,
        ]);
    }
}
