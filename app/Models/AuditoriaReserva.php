<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaReserva extends Model
{
    use HasFactory;

    protected $table = "auditoria_reservas";
    public $timestamps = false;

    protected $fillable = [
        'reserva_id',
        'accion',
        'datos_previos',
        'datos_nuevos',
        'actor_id',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }
}
