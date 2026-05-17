<?php

namespace App\Console\Commands;

use App\Models\Reservarviaje;
use App\Models\Carros;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCompletarReservas extends Command
{
    protected $signature   = 'reservas:auto-completar';
    protected $description = 'Marca como completadas las reservas confirmadas cuyo viaje fue hace más de 5 días';

    public function handle(): void
    {
        $limite = now()->subDays(5)->toDateString();

        // Traer todas las reservas confirmadas
        $reservas = Reservarviaje::where('estado', 'Confirmada')
            ->with('carro')
            ->get();

        $total = 0;

        foreach ($reservas as $reserva) {
            $fechaViaje = $reserva->carro?->fecha;

            // Si la fecha del viaje ya pasó hace más de 5 días
            if ($fechaViaje && $fechaViaje <= $limite) {
                $reserva->estado = 'completada';
                $reserva->save();
                $total++;

                Log::info("Reserva #{$reserva->id_reservarviajes} auto-completada (viaje: {$fechaViaje})");
            }
        }

        $this->info("Auto-completadas: {$total} reserva(s).");
    }
}
