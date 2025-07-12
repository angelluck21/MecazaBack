<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    protected $fillable = [
        "id_usuario",
        'Nombre',
        'Correo',
        'Contrasena',
    ];

    protected $hidden = [
        'Contrasena',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
