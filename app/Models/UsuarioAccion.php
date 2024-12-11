<?php

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioAccion extends Model
{
    use HasFactory;

    protected $table = 'usuario_accion'; // Nombre de la tabla intermedia

    protected $fillable = ['usu_id', 'acc_id']; // Campos rellenables

    public $timestamps = false; // Deshabilitar timestamps porque es una tabla intermedia
}
