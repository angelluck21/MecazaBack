<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Precioviajes extends Model
{
    protected $primaryKey = 'id_precioviajes';
    protected $fillable = [
        'zara-mede',
        'zara-cauca',
        'cauca-mede',
    ];

    /**
     * Devuelve el precio correcto según la ruta origen → destino.
     * Busca parcialmente "zar", "cau", "med" en los textos para ser flexible.
     */
    public static function getPrecioParaRuta(?string $origen, ?string $destino): float
    {
        $p = self::first();
        if (!$p) return 50000.0;

        $ori = mb_strtolower($origen ?? '');
        $dst = mb_strtolower($destino ?? '');

        $esZara  = str_contains($ori, 'zar');
        $esCauca = str_contains($ori, 'cau') || str_contains($ori, 'cac');
        $dstMede = str_contains($dst, 'med');
        $dstCauc = str_contains($dst, 'cau') || str_contains($dst, 'cac');
        $dstZara = str_contains($dst, 'zar');

        if ($esZara  && $dstMede) return (float)($p->{'zara-mede'}  ?? 120000);
        if ($esZara  && $dstCauc) return (float)($p->{'zara-cauca'} ?? 30000);
        if ($esCauca && $dstMede) return (float)($p->{'cauca-mede'} ?? 100000);
        // rutas inversas
        if ($dstZara && $esCauca) return (float)($p->{'zara-cauca'} ?? 30000);
        if ($dstZara && str_contains($ori, 'med')) return (float)($p->{'zara-mede'}  ?? 120000);
        if ($dstCauc && str_contains($ori, 'med')) return (float)($p->{'cauca-mede'} ?? 100000);

        // Si no hay coincidencia tomar la primera columna disponible
        return (float)($p->{'zara-mede'} ?? $p->valor ?? 50000);
    }
}
