<?php

namespace App\Console\Commands;

use App\Models\Faturaviaje;
use Illuminate\Console\Command;

class RecalcularFacturas extends Command
{
    protected $signature   = 'facturas:recalcular';
    protected $description = 'Recalcula todas las facturas: quita IVA y aplica descuento web del 10%';

    public function handle()
    {
        $facturas = Faturaviaje::all();
        $total    = $facturas->count();
        $this->info("Procesando {$total} facturas...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($facturas as $factura) {
            $subtotal = (float) $factura->subtotal;
            $descuento = $subtotal * 0.10;

            $factura->impuesto = $descuento;
            $factura->total    = $subtotal - $descuento;
            $factura->save();

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Listo. Todas las facturas fueron recalculadas con descuento del 10%.');
    }
}
