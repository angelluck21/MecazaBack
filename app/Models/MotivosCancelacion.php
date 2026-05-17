<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MotivosCancelacion extends Model
{
    protected $table = 'motivos_cancelacion';
    protected $fillable = [
        'id_reservarviajes',
        'id_users',
        'motivo',
        'tipo',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reservarviaje::class, 'id_reservarviajes');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_users');
    }
}
