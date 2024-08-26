<?php

namespace App\Helpers;

class ValidationHelpers
{

    public static function horarioRules()
    {
        return [
            'cancha_id' => 'required|exists:cancha,id',
            'fecha' => 'required|date_format:Y-m-d',
            'horario_apertura' => 'required|date_format:H:i:s',
            'horario_cierre' => 'required|date_format:H:i:s',
            'duracion_turno' => 'required|date_format:H:i:s',
        ];
    }

    public static function horarioMessages()
    {
        return [
            'cancha_id.required' => 'El ID de la cancha es obligatorio.',
            'cancha_id.exists' => 'La cancha seleccionada no existe.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date_format' => 'La fecha debe ser una fecha válida en formato YYYY-MM-DD.',
            'horario_apertura.required' => 'El horario de apertura es obligatorio.',
            'horario_apertura.date_format' => 'El horario de apertura debe estar en el formato HH:MM:SS.',
            'horario_cierre.required' => 'El horario de cierre es obligatorio.',
            'horario_cierre.date_format' => 'El horario de cierre debe estar en el formato HH:MM:SS.',
            'duracion_turno.required' => 'La duración del turno es obligatoria.',
            'duracion_turno.date_format' => 'La duración del turno debe estar en el formato HH:MM:SS.',
        ];
    }
}
