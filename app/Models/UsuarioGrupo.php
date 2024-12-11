<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioGrupo extends Model
{
    use HasFactory;

    protected $table = 'usuario_grupo'; // Nombre de la tabla intermedia

    protected $fillable = ['usu_id', 'gru_id']; // Campos rellenables

    public $timestamps = false; // Deshabilitar timestamps porque es una tabla intermedia
}
