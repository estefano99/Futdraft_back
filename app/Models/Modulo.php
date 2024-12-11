<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    protected $table = 'modulos'; // Nombre de la tabla

    protected $fillable = ['nombre']; // Campos rellenables

    public function formularios()
    {
        return $this->hasMany(Formulario::class, 'mod_id');
    }
}
