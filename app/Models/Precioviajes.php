<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Precioviajes extends Model
{
    protected $primaryKey = 'id_precioviajes';
    protected $fillable = ['origen', 'destino', 'precio'];

    public static function getPrecioParaRuta(?string $origen, ?string $destino): float
    {
        if (!$origen && !$destino) return 50000.0;

        $ori = mb_strtolower($origen ?? '');
        $dst = mb_strtolower($destino ?? '');

        $route = self::whereRaw('LOWER(origen) LIKE ?', ["%{$ori}%"])
                     ->whereRaw('LOWER(destino) LIKE ?', ["%{$dst}%"])
                     ->first();

        if (!$route) {
            $route = self::whereRaw('LOWER(origen) LIKE ?', ["%{$dst}%"])
                         ->whereRaw('LOWER(destino) LIKE ?', ["%{$ori}%"])
                         ->first();
        }

        return $route ? (float) $route->precio : 50000.0;
    }
}
