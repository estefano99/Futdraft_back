<?php

namespace App\States;

use App\Models\Mantenimiento;

class EstadoCompletado implements EstadoMantenimiento
{
    private $mantenimiento;

    public function __construct(Mantenimiento $mantenimiento)
    {
        $this->mantenimiento = $mantenimiento;
    }

    public function iniciar()
    {
        throw new \Exception("El mantenimiento ya fue completado.");
    }

    public function completar()
    {
        throw new \Exception("El mantenimiento ya está completado.");
    }

    public function cancelar()
    {
        throw new \Exception("No se puede cancelar un mantenimiento completado.");
    }
}
