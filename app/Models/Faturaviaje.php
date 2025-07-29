<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faturaviaje extends Model
{
    protected $fillable = [
        'id_factura',
        'id_users',
        'destino',
        'id_carros',
        'id_precioviajes',
    ];
}
