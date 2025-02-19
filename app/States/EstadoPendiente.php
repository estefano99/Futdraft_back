<?php

namespace App\States;

use App\Models\Mantenimiento;
use Illuminate\Support\Facades\Log;

class EstadoPendiente implements EstadoMantenimiento
{
    private $mantenimiento;

    public function __construct(Mantenimiento $mantenimiento)
    {
        $this->mantenimiento = $mantenimiento;
    }

    public function iniciar()
    {
        Log::info('--------------EstadoPendiente: iniciar()-------------');
        $this->mantenimiento->estado = 'en_progreso';
        $this->mantenimiento->setState('en_progreso'); // Cambia el estado en la memoria
        $this->mantenimiento->save();
    }

    public function completar()
    {
        Log::info('--------------EstadoPendiente: completar()-------------');
        throw new \Exception("No se puede completar un mantenimiento pendiente sin iniciarlo.");
    }

    public function cancelar()
    {
        Log::info('--------------EstadoPendiente: cancelar()-------------');
        $this->mantenimiento->estado = 'cancelado';
        $this->mantenimiento->setState('cancelado'); // Cambia el estado internamente
        $this->mantenimiento->save();
    }
}
