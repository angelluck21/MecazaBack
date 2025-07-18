<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class agregarcarros extends Model
{
    protected $fillable = [
        'conductor',
        'imagencarro',
        'placa',
        'asientos',
        'destino',
        'horasalida',
        'fecha',
    ];
}
