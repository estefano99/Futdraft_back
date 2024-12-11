<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupos';

    protected $fillable = ['nombre', 'descripcion', 'estado', 'codigo'];

    public function acciones()
    {
        return $this->belongsToMany(Accion::class, 'grupo_accion', 'grupo_id', 'accion_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'grupo_usuario', 'grupo_id', 'user_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }


    public function scopeInactivos($query)
    {
        return $query->where('estado', 0);
    }
}
