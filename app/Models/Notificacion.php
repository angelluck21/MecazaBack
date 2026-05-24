<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'id_users',
        'titulo',
        'mensaje',
        'tipo',
        'leida',
        'datos',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'datos' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_users', 'id_users');
    }

    public static function crear(int $userId, string $titulo, string $mensaje, string $tipo = 'info', array $datos = []): self
    {
        return static::create([
            'id_users' => $userId,
            'titulo'   => $titulo,
            'mensaje'  => $mensaje,
            'tipo'     => $tipo,
            'datos'    => !empty($datos) ? $datos : null,
        ]);
    }
}
