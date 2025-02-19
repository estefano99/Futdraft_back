<?php

namespace App\States;

interface EstadoMantenimiento
{
    public function iniciar();
    public function completar();
    public function cancelar();
}
