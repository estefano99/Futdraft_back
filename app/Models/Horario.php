<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $table = "horario";

    protected $fillable = [
        'fecha',
        'horario_apertura',
        'horario_cierre',
        'duracion_turno',
        'cancha_id'
    ];
}
