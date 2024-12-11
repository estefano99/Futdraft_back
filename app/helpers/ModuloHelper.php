<?php

namespace App\Helpers;

use App\Models\Modulo;

class ModuloHelper
{
    /**
     * Obtener los módulos basados en las acciones proporcionadas.
     *
     * @param array $accionesTotales
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function obtenerModulos(array $accionesTotales)
    {
        return Modulo::whereHas('formularios.acciones', function ($query) use ($accionesTotales) {
            $query->whereIn('id', $accionesTotales);
        })
        ->with(['formularios.acciones' => function ($query) use ($accionesTotales) {
            $query->whereIn('id', $accionesTotales);
        }])
        ->get();
    }
}
