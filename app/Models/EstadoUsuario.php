<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoUsuario extends Model
{
    use HasFactory;

    protected $table = 'estado_usuarios'; // Nombre de la tabla

    protected $fillable = ['est_usu_nombre']; // Campos rellenables

    public function usuarios()
    {
        return $this->hasMany(User::class, 'estado_usuario_id');
    }
}
