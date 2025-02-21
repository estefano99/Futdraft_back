<?php

namespace App\Commands;

use App\Models\Mantenimiento;
use Exception;

class IniciarMantenimiento implements MantenimientoCommand
{
    public function execute(Mantenimiento $mantenimiento): void
    {
        // Verifica que el mantenimiento esté en estado "pendiente"
        if ($mantenimiento->estado !== 'pendiente') {
            throw new Exception("Solo se puede iniciar un mantenimiento que esté en estado 'pendiente'.");
        }
        // Actualiza el estado a "en_progreso"
        $mantenimiento->estado = 'en_progreso';
    }
}
