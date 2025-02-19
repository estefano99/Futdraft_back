<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaMantenimiento extends Model
{
    use HasFactory;

    protected $table = "auditoria_mantenimiento";
    public $timestamps = false;

    protected $fillable = [
        'mantenimiento_id',
        'estado_previo',
        'estado_nuevo',
        'accion',
        'user_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class, 'mantenimiento_id');
    }
}
