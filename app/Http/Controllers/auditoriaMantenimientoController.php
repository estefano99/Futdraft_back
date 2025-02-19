<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaMantenimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuditoriaMantenimientoController extends Controller
{
    public function listadoAuditoriaMantenimiento()
    {
        try {
            $query = AuditoriaMantenimiento::with(['usuario', 'mantenimiento']);
            $auditoria = $query->paginate(5);

            // Verificar si existen registros
            if ($auditoria->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron registros de auditoria.',
                    'auditoria' => [],
                ], 200);
            }

            $data = [
                'auditoria' => $auditoria->items(),
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
