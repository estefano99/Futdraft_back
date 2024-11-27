<?php

use App\Models\Horario;
use Illuminate\Support\Facades\Log;

if (!function_exists('calcularHorarioReserva')) {
    /**
     * Calcula los campos start y end de una reserva basada en su fecha y horario correspondiente.
     *
     * @param object $reserva
     * @return object
     */
    function calcularHorarioReserva($reserva) {
        $fechaSinHora = date('Y-m-d', strtotime($reserva->fecha));

        $horario = Horario::where('cancha_id', $reserva->cancha_id)
            ->where('fecha', $fechaSinHora)
            ->first();

        $duracionEnMinutos = 0;
        if ($horario && $horario->duracion_turno) {
            list($horas, $minutos, $segundos) = explode(':', $horario->duracion_turno);
            $duracionEnMinutos = ($horas * 60) + $minutos + ($segundos / 60);
        }

        $reserva->start = $reserva->fecha;
        $reserva->end = date('Y-m-d H:i:s', strtotime($reserva->fecha) + ($duracionEnMinutos * 60));

        return $reserva;
    }
}
