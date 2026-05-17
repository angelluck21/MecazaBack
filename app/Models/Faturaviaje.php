<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faturaviaje extends Model
{
    protected $table = 'Factura';
    protected $primaryKey = 'id_factura';
    protected $fillable = [
        'id_factura',
        'id_users',
        'id_carros',
        'id_precioviajes',
        'id_reservarviajes',
        'destino',
        'subtotal',
        'impuesto',
        'total',
        'numero_factura',
        'fecha_emision',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_users');
    }

    public function carro()
    {
        return $this->belongsTo(Carros::class, 'id_carros');
    }

    public function reserva()
    {
        return $this->belongsTo(Reservarviaje::class, 'id_reservarviajes');
    }

    public function precio()
    {
        return $this->belongsTo(Precioviajes::class, 'id_precioviajes');
    }
}
