<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Colonia extends Model
{
    protected $table = 'colonias';

    protected $fillable = [
        'codigo_postal',
        'colonia',
        'municipio',
        'estado',
    ];
}
