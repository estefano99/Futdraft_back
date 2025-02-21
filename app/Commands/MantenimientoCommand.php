<?php


namespace App\Commands;

use App\Models\Mantenimiento;

interface MantenimientoCommand
{
    /**
     * Ejecuta la acción sobre el mantenimiento.
     *
     * @param Mantenimiento $mantenimiento
     * @throws \Exception
     */
    public function execute(Mantenimiento $mantenimiento): void;
}
