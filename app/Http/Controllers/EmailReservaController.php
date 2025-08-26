<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmailReservaController extends Controller
{
    private function enviarEmailConductor($reserva, $carro, $request)
    {
        try {
            // Datos para el email
            $emailData = [
                'conductorNombre' => $carro->conductor,
                'conductorEmail' => $carro->email,
                'pasajeroNombre' => $request->Nombre,
                'asiento' => $request->Asiento,
                'ubicacion' => $request->Ubicacion,
                'placa' => $carro->placa,
                'destino' => $carro->destino,
                'fecha' => $carro->fecha,
                'hora' => $carro->horasalida,
                'fechaReserva' => now()->format('d/m/Y H:i:s')
            ];

            // Enviar email usando Mailtrap
            Mail::to($carro->email)->send(new NuevaReservaConductor($emailData));

        } catch (\Exception $e) {
            \Log::error('Error al enviar email al conductor: ' . $e->getMessage());
        }
    }
}
