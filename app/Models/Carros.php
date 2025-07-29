<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carros extends Model
{
    protected $fillable = [
        'conductor',
        'imagencarro',
        'telefono',
        'placa',
        'asientos',
        'destino',
        'horasalida',
        'fecha',
    ];

    public function estado()
    {
        return $this->belongsTo(estadoscarros::class, 'id_estados');
    }
}
