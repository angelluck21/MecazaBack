<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carros extends Model
{
    protected $primaryKey = 'id_carros';
    protected $fillable = [
        'conductor',
        'telefono',
        'placa',
        'asientos',
        'destino',
        'horasalida',
        'fecha',
        'id_estados',
        'id_users',
    ];

    public function estado()
    {
        return $this->belongsTo(Estadoscarro::class, 'id_estados');
    }

    public function conductor()
    {
        return $this-> belongsTo(User::class, 'id_user');
    }
}
