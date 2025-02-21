<?php

namespace App\Commands;

use App\Models\Mantenimiento;
use Exception;

class CancelarMantenimiento implements MantenimientoCommand
{
    public function execute(Mantenimiento $mantenimiento): void
    {
        // Verifica que el mantenimiento esté en estado "en_progreso"
        if ($mantenimiento->estado !== 'en_progreso') {
            throw new Exception("Solo se puede cacnelar un mantenimiento que esté en estado 'en_progreso'.");
        }
        // Actualiza el estado a "cancelado"
        $mantenimiento->estado = 'cancelado';
    }
}
