<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;

    protected $table = "tareas";

    protected $fillable = [
        'descripcion',
        'empleado_id',
        'mantenimiento_id',
        'fecha_asignacion',
        'fecha_completado'
    ];

    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class, 'mantenimiento_id');
    }

    public function empleado()
    {
        return $this->belongsTo(User::class, 'empleado_id');
    }
}
