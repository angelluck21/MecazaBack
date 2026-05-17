<?php

namespace App\Http\Controllers;

use App\Models\MotivosCancelacion;
use App\Models\Reservarviaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MotivosCancelacionController extends Controller
{
    public function Create(Request $request)
    {
        $request->validate([
            'id_reservarviajes' => 'required|exists:reservarviajes,id_reservarviajes',
            'motivo' => 'required|string|max:1000',
            'tipo' => 'required|in:usuario,conductor,admin',
        ]);

        try {
            $motivo = MotivosCancelacion::create([
                'id_reservarviajes' => $request->id_reservarviajes,
                'id_users' => $request->user()->id_users,
                'motivo' => $request->motivo,
                'tipo' => $request->tipo,
            ]);

            return response()->json([
                'message' => 'Motivo de cancelación guardado exitosamente',
                'data' => $motivo,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al guardar motivo de cancelación', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al guardar el motivo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function GetByReserva($id_reservarviajes)
    {
        try {
            $motivos = MotivosCancelacion::where('id_reservarviajes', $id_reservarviajes)
                ->with(['usuario'])
                ->get();

            return response()->json([
                'data' => $motivos,
                'message' => 'Motivos recuperados exitosamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener motivos', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al obtener los motivos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function GetAll()
    {
        return response()->json([
            'data' => MotivosCancelacion::with(['usuario', 'reserva'])->get(),
            'message' => 'Consulta de motivos exitosa',
        ], 200);
    }
}
