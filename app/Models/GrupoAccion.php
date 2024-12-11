<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoAccion extends Model
{
    use HasFactory;

    protected $table = 'grupo_accion'; // Nombre de la tabla intermedia

    protected $fillable = ['gru_id', 'acc_id']; // Campos rellenables

    public $timestamps = true; // Deshabilitar timestamps porque es una tabla intermedia
}
