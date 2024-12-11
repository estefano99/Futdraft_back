<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formulario extends Model
{
    use HasFactory;

    protected $table = 'formularios';

    protected $fillable = ['nombre', 'mod_id'];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'mod_id');
    }

    public function acciones()
    {
        return $this->hasMany(Accion::class, 'for_id');
    }
}
