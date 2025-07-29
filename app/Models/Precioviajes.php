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

}
