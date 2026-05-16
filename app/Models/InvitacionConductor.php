<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvitacionConductor extends Model
{
    protected $table = 'invitaciones_conductores';

    protected $fillable = [
        'email',
        'token',
        'usado',
        'creado_por',
        'expires_at',
    ];

    protected $casts = [
        'usado'      => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function isValida(): bool
    {
        return !$this->usado && $this->expires_at->isFuture();
    }
}
