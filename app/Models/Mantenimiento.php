<?php

namespace App\Models;

use App\States\MantenimientoEstado;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Mantenimiento extends Model
{
    use HasFactory;

    protected $table = "mantenimientos";

    protected $fillable = [
        'responsable',
        'fecha',
        'fecha_fin',
        'descripcion',
        'estado',
        'tipo_mantenimiento_id',
    ];

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable');
    }

    public function tipoMantenimiento()
    {
        return $this->belongsTo(TipoMantenimiento::class, 'tipo_mantenimiento_id');
    }

    // En App/Models/Mantenimiento.php

    public function cambiarEstado(string $nuevoEstado)
    {
        // Define las transiciones permitidas
        $transiciones = [
            'pendiente'   => ['en_progreso'],
            'en_progreso' => ['completado', 'cancelado'],
            'completado'  => [],
            'cancelado'   => [],
        ];

        if (!array_key_exists($this->estado, $transiciones)) {
            throw new \Exception("El estado actual no es válido.");
        }

        if (!in_array($nuevoEstado, $transiciones[$this->estado])) {
            throw new \Exception("Transición de {$this->estado} a {$nuevoEstado} no permitida.");
        }

        $this->estado = $nuevoEstado;
    }
}
