<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservarviaje extends Model
{
    protected $primaryKey = 'id_reservarviajes';
    protected $fillable = [
        'nombre',
        'ubicacion',
        'tel',
        'asiento',
        'estado',
        'id_users',
        'id_carros'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_users');
    }

    public function carro()
    {
        return $this->belongsTo(Carros::class, 'id_carros');
    }
}
