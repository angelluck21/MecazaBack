<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carros extends Model
{
    protected $primaryKey = 'id_carros';

    protected $casts = [
        'asientos' => 'integer',
    ];

    protected $fillable = [
        'conductor',
        'imagencarro',
        'telefono',
        'placa',
        'asientos',
        'horasalida',
        'fecha',
        'id_estados',
        'id_users',
        'id_precioviaje',
    ];

    public function precioviaje()
    {
        return $this->belongsTo(Precioviajes::class, 'id_precioviaje', 'id_precioviajes');
    }

    public function estado()
    {
        return $this->belongsTo(Estadoscarro::class, 'id_estados');
    }

    public function conductorUsuario()
    {
        return $this->belongsTo(User::class, 'id_users');
    }

    public function reservas()
    {
        return $this->hasMany(Reservarviaje::class, 'id_carros');
    }
}
