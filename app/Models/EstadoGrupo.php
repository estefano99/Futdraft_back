<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoGrupo extends Model
{
    use HasFactory;

    protected $table = 'estado_grupos'; // Nombre de la tabla

    protected $fillable = ['est_gru_nombre']; // Campos rellenables

    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'est_gru_id');
    }
}
