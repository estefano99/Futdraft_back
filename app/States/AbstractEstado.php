<?php
namespace App\States;

use App\Models\Mantenimiento;

abstract class AbstractEstado implements EstadoMantenimiento
{
    protected $mantenimiento;

    // Mapeo: estado destino => método a ejecutar para la transición
    protected $allowedTransitions = [];

    public function __construct(Mantenimiento $mantenimiento)
    {
        $this->mantenimiento = $mantenimiento;
    }

    /**
     * Intenta realizar la transición al estado indicado.
     * Si la transición no está permitida, lanza una excepción.
     */
    public function transitionTo(string $nuevoEstado)
    {
        if (!array_key_exists($nuevoEstado, $this->allowedTransitions)) {
            throw new \Exception("Transición a $nuevoEstado no permitida desde el estado {$this->mantenimiento->estado}.");
        }
        $method = $this->allowedTransitions[$nuevoEstado];
        return $this->$method();
    }

    // Los métodos que deben implementar las clases concretas.
    abstract public function iniciar();
    abstract public function completar();
    abstract public function cancelar();
}
