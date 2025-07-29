<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estadoscarro extends Model
{
    protected $primaryKey = 'id_estados';
    protected $fillable = [
        'estados',
    ];
}
