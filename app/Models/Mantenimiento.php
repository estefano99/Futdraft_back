<?php

namespace App\Models;

use App\States\EstadoCancelado;
use App\States\EstadoCompletado;
use App\States\EstadoEnProgreso;
use App\States\EstadoPendiente;
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

    private $state; // Estado actual


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setState($this->estado); // Asigna el estado al instanciar
    }

    /**
     * Asigna el estado dinámicamente basado en el valor del campo 'estado'.
     */
    public function setState($estado)
    {
        Log::info($estado . " SET STATE");
        if (!$estado) {
            $estado = 'pendiente'; // Si está vacío o nulo, lo asigna a 'pendiente'
        }
        switch ($estado) {
            case 'pendiente':
                $this->state = new EstadoPendiente($this);
                break;
            case 'en_progreso':
                $this->state = new EstadoEnProgreso($this);
                break;
            case 'completado':
                $this->state = new EstadoCompletado($this);
                break;
            case 'cancelado':
                $this->state = new EstadoCancelado($this);
                break;
            default:
                throw new \Exception("Estado no válido: $estado");
        }
    }

    /**
     * Métodos de cambio de estado delegados al estado actual.
     */
    public function iniciar()
    {
        $this->state->iniciar();
    }

    public function completar()
    {
        $this->state->completar();
    }

    public function cancelar()
    {
        $this->state->cancelar();
    }

    /**
     * Retorna el estado actual, y lo configura si aún no está instanciado.
     */
    public function getState()
    {
        if (!$this->state) {
            $this->setState($this->estado);
        }
        return $this->state;
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable');
    }

    public function tipoMantenimiento()
    {
        return $this->belongsTo(TipoMantenimiento::class, 'tipo_mantenimiento_id');
    }
}
