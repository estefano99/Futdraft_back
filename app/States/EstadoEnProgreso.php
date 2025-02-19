<?php

namespace App\States;

use App\Models\Mantenimiento;
use Illuminate\Support\Facades\Log;

class EstadoEnProgreso implements EstadoMantenimiento
{
    private $mantenimiento;

    public function __construct(Mantenimiento $mantenimiento)
    {
        $this->mantenimiento = $mantenimiento;
    }

    public function iniciar()
    {
        Log::info('--------------EstadoEnProgreso: iniciar()-------------');
        throw new \Exception("El mantenimiento ya está en progreso.");
    }

    public function completar()
    {
        $this->mantenimiento->estado = 'completado';
        $this->mantenimiento->setState('completado');
        $this->mantenimiento->save();
    }

    public function cancelar()
    {
        $this->mantenimiento->estado = 'cancelado';
        $this->mantenimiento->setState('cancelado');
        $this->mantenimiento->save();
    }
}
