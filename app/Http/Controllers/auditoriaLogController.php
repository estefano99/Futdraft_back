<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuditoriaLogController extends Controller
{
    public function listadoAuditoriaLog()
    {
        try {
            $query = AuditoriaLog::with('usuario');
            $auditoriaLog = $query->paginate(5);

            // Verificar si existen registros
            if ($auditoriaLog->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron registros de auditoria.',
                    'auditoriaLog' => [],
                ], 200);
            }

            $data = [
                'auditoriaLog' => $auditoriaLog->items(),
                'meta' => [
                    'current_page' => $auditoriaLog->currentPage(),
                    'last_page' => $auditoriaLog->lastPage(),
                    'per_page' => $auditoriaLog->perPage(),
                    'total' => $auditoriaLog->total(),
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
