<?php

namespace App\States;

use App\Models\Mantenimiento;

class EstadoCancelado implements EstadoMantenimiento
{
    private $mantenimiento;

    public function __construct(Mantenimiento $mantenimiento)
    {
        $this->mantenimiento = $mantenimiento;
    }

    public function iniciar()
    {
        throw new \Exception("No se puede iniciar un mantenimiento cancelado.");
    }

    public function completar()
    {
        throw new \Exception("No se puede completar un mantenimiento cancelado.");
    }

    public function cancelar()
    {
        throw new \Exception("El mantenimiento ya está cancelado.");
    }
}
