<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accion extends Model
{
    use HasFactory;

    protected $table = 'acciones';

    protected $fillable = ['nombre', 'for_id'];

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_accion', 'accion_id', 'grupo_id');
    }

    public function formularios()
    {
        return $this->belongsTo(Formulario::class, 'id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_accion', 'acc_id', 'usu_id');
    }
}
