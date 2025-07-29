<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservarviaje extends Model
{
    protected $primaryKey = 'id_reservarviajes';
    protected $fillable = [
        'regate',
        'comentario',
        'ubicacion',
        'asiento',
        'id_users',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_users');
    }
}
