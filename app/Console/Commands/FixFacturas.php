<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Faturaviaje;
use App\Models\Carros;

class FixFacturas extends Command
{
    protected $signature   = 'facturas:fix-rutas';
    protected $description = 'Actualiza las facturas existentes con origen, destino y precio correcto desde precioviaje';

    public function handle()
    {
        $facturas = Faturaviaje::whereNull('origen')
            ->orWhere('origen', '')
            ->orWhereNull('destino')
            ->orWhere('destino', '')
            ->get();

        if ($facturas->isEmpty()) {
            $this->info('No hay facturas con origen/destino vacío.');
            return 0;
        }

        $this->info("Encontradas {$facturas->count()} factura(s) para corregir...");
        $bar = $this->output->createProgressBar($facturas->count());
        $bar->start();

        $corregidas = 0;
        $sinRuta    = 0;

        foreach ($facturas as $factura) {
            $carro = Carros::with('precioviaje')->find($factura->id_carros);

            if (!$carro || !$carro->precioviaje) {
                $sinRuta++;
                $bar->advance();
                continue;
            }

            $pv       = $carro->precioviaje;
            $subtotal = (float) $pv->precio;
            $impuesto = $subtotal * 0.19;

            $factura->update([
                'origen'          => $pv->origen,
                'destino'         => $pv->destino,
                'id_precioviajes' => $pv->id_precioviajes,
                'subtotal'        => $subtotal,
                'impuesto'        => $impuesto,
                'total'           => $subtotal + $impuesto,
            ]);

            $corregidas++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Corregidas: {$corregidas}");

        if ($sinRuta > 0) {
            $this->warn("Sin ruta asociada (no actualizadas): {$sinRuta}");
        }

        return 0;
    }
}
