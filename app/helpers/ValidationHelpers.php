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

    public static function reservaRules() {
        return [
            'precio' => 'required|numeric|min:0',
            'fecha' => 'required|date_format:Y-m-d H:i:s',
            'cancha_id' => 'required|exists:cancha,id',
            'usuario_id' => 'required|exists:users,id',
        ];
    }

    public static function reservaMessages() {
        return
            [
                'precio.required' => 'El campo precio es obligatorio.',
                'precio.numeric' => 'El campo precio debe ser un número.',
                'precio.min' => 'El precio debe ser mayor o igual a 0.',
                'fecha.required' => 'El campo fecha es obligatorio.',
                'fecha.date_format' => 'El campo fecha debe estar en el formato Y-m-d H:i:s.',
                'cancha_id.required' => 'El campo cancha_id es obligatorio.',
                'cancha_id.exists' => 'El campo cancha_id debe ser un ID válido de una cancha existente.',
                'usuario_id.required' => 'El campo usuario_id es obligatorio.',
                'usuario_id.exists' => 'El campo usuario_id debe ser un ID válido de un usuario existente.',
            ];
    }
}
