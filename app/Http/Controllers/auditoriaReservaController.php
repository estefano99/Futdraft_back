<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaReserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuditoriaReservaController extends Controller
{
    public function listadoAuditoriaReserva()
    {
        try {
            $query = AuditoriaReserva::with(['actor', 'reserva']);
            $auditoria = $query->paginate(5);

            // Verificar si existen registros
            if ($auditoria->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron registros de auditoria.',
                    'auditoria' => [],
                ], 200);
            }

            // Iterar sobre cada elemento para decodificar los campos JSON
            $auditoriaItems = $auditoria->items();
            foreach ($auditoriaItems as $item) {
                if ($item->datos_previos) {
                    $item->datos_previos = json_decode($item->datos_previos, true);
                }
                if ($item->datos_nuevos) {
                    $item->datos_nuevos = json_decode($item->datos_nuevos, true);
                }
            }

            $data = [
                'auditoria' => $auditoriaItems,
                'meta' => [
                    'current_page' => $auditoria->currentPage(),
                    'last_page' => $auditoria->lastPage(),
                    'per_page' => $auditoria->perPage(),
                    'total' => $auditoria->total(),
                ],
                'status' => 200
            ];

            return response()->json($data, 200);
        } catch (\Exception $e) {
            // Manejar cualquier excepción
            return response()->json([
                'message' => 'Ocurrió un error al obtener los registros de auditoria.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
