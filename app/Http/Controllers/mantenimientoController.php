<?php

namespace App\Http\Controllers;

use App\Commands\CancelarMantenimiento;
use App\Commands\FinalizarMantenimiento;
use App\Commands\IniciarMantenimiento;
use App\Models\AuditoriaMantenimiento;
use App\Models\Mantenimiento;
use App\Models\Tarea;
use App\States\Cancelado;
use App\States\Completado;
use App\States\EnProgreso;
use App\States\Pendiente;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MantenimientoController extends Controller
{

    public function listadoMantenimientos(Request $request)
    {
        try {
            $descripcion = $request->input("descripcion");
            $fecha = $request->input("fecha");
            $estado = $request->input("estado");

            // Iniciar la consulta con eager loading de la relación 'responsable'
            $query = Mantenimiento::with(['responsable', 'tipoMantenimiento']);

            if (!empty($descripcion)) {
                $query->where('descripcion', 'LIKE', "%{$descripcion}%");
            }
            if (!empty($estado)) {
                $query->where('estado', 'LIKE', "%{$estado}%");
            }
            if (!empty($fecha)) {
                $query->where('fecha', 'LIKE', "%{$fecha}%");
            }

            $mantenimientos = $query->paginate(5);

            // Verificar si existen registros
            if ($mantenimientos->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron Mantenimientos.',
                    'mantenimientos' => [],
                ], 200);
            }

            $data = [
                'mantenimientos' => $mantenimientos->items(),
                'meta' => [
                    'current_page' => $mantenimientos->currentPage(),
                    'last_page' => $mantenimientos->lastPage(),
                    'per_page' => $mantenimientos->perPage(),
                    'total' => $mantenimientos->total(),
                ],
                'status' => 200
            ];

            // Respuesta exitosa con los registros
            return response()->json($data, 200);
        } catch (\Exception $e) {
            // Manejar cualquier excepción
            return response()->json([
                'message' => 'Ocurrió un error al obtener los mantenimientos.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function obtenerMantenimientoById($id)
    {
        try {
            $tareas = Tarea::where('mantenimiento_id', $id)->get();
            return response()->json([
                'tareas' => $tareas,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al obtener los mantenimientos.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listadoMantenimientosSelect()
    {
        try {
            // Obtén la fecha y hora actual (usando Carbon)
            $fechaActual = Carbon::now();

            // Consulta: solo mantenimientos activos (pendiente o en_progreso)
            // y cuya fecha sea mayor o igual a la fecha actual.
            $mantenimientos = Mantenimiento::with(['responsable', 'tipoMantenimiento'])
                ->whereIn('estado', ['pendiente', 'en_progreso'])
                ->where('fecha', '>=', $fechaActual)
                ->get();

                Log::info('Mantenimientos encontrados:', ['mantenimientos' => $mantenimientos]);

            // Verificar si se encontraron registros
            if ($mantenimientos->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron mantenimientos.',
                    'mantenimientos' => [],
                ], 200);
            }

            // Respuesta exitosa con los registros
            return response()->json([
                'mantenimientos' => $mantenimientos,
                'status' => 200,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al obtener los mantenimientos.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function crearMantenimiento(Request $request)
    {

        Log::info($request);
        $validator = Validator::make($request->all(), [
            'responsable' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('tipo_usuario', 'empleado');
                }),
            ],
            'fecha' => 'required|date_format:Y-m-d H:i:s',
            'fecha_fin' => 'nullable|date_format:Y-m-d H:i:s',
            'descripcion' => 'required|string|max:255',
            'estado' => 'nullable|in:pendiente,en_progreso,completado,cancelado',
            'tipo_mantenimiento_id' => 'required|integer|exists:tipos_mantenimiento,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // 🔥 Se crea con el estado `pendiente` por defecto
            $mantenimiento = new Mantenimiento([
                'responsable' => $request->responsable,
                'fecha' => $request->fecha,
                'fecha_fin' => $request->fecha_fin ?? null,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado, // Estado inicial
                'tipo_mantenimiento_id' => $request->tipo_mantenimiento_id,
            ]);

            $mantenimiento->save();

            AuditoriaMantenimiento::create([
                'user_id' => $request->responsable,
                'accion' => 'creado',
                'estado_previo' => '',
                'estado_nuevo' => $request->estado,
                'mantenimiento_id' => $mantenimiento->id,
            ]);

            // Cargar relaciones
            $mantenimiento->load(['responsable', 'tipoMantenimiento']);

            Log::info('Mantenimiento creado exitosamente', ['mantenimiento' => $mantenimiento]);

            return response()->json([
                'message' => 'Mantenimiento creado exitosamente.',
                'mantenimiento' => $mantenimiento,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el Mantenimiento.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function editarMantenimiento(Request $request, $id)
    {
        try {
            $mantenimiento = Mantenimiento::find($id);
            if (!$mantenimiento) {
                return response()->json([
                    'message' => 'Mantenimiento no encontrado.',
                ], 404);
            }

            $estadoAuditoriaPrevio = $mantenimiento->estado;

            $validator = Validator::make($request->all(), [
                'responsable' => [
                    'required',
                    'integer',
                    Rule::exists('users', 'id')->where(function ($query) {
                        $query->where('tipo_usuario', 'empleado');
                    }),
                ],
                'fecha' => 'required|date_format:Y-m-d H:i:s',
                'fecha_fin' => 'nullable|date_format:Y-m-d H:i:s',
                'descripcion' => 'required|string|max:255',
                'estado' => 'required|in:pendiente,en_progreso,completado,cancelado',
                'tipo_mantenimiento_id' => 'required|integer|exists:tipos_mantenimiento,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Actualizar los campos generales sin modificar aún el estado
            $mantenimiento->responsable = $request->responsable;
            $mantenimiento->fecha = $request->fecha;
            $mantenimiento->fecha_fin = $request->fecha_fin ?? null;
            $mantenimiento->descripcion = $request->descripcion;
            $mantenimiento->tipo_mantenimiento_id = $request->tipo_mantenimiento_id;

            $nuevoEstado = $request->estado;

            switch ($mantenimiento->estado) {
                case 'pendiente':
                    if ($nuevoEstado === 'en_progreso') {
                        $command = new IniciarMantenimiento();
                    } else {
                        throw new Exception("Transición no permitida de {$mantenimiento->estado} a {$nuevoEstado}.");
                    }
                    break;
                case 'en_progreso':
                    if ($nuevoEstado === 'completado') {
                        $command = new FinalizarMantenimiento();
                    } elseif ($nuevoEstado === 'cancelado') {
                        $command = new CancelarMantenimiento();
                    } else {
                        throw new Exception("Transición no permitida de {$mantenimiento->estado} a {$nuevoEstado}.");
                    }
                    break;
                default:
                    throw new Exception("No se permiten transiciones desde el estado {$mantenimiento->estado}.");
            }

            $command->execute($mantenimiento);
            $mantenimiento->save();

            // Registrar auditoría
            AuditoriaMantenimiento::create([
                'user_id' => $request->responsable,
                'accion' => 'actualizado',
                'estado_previo' => $estadoAuditoriaPrevio,
                'estado_nuevo' => $mantenimiento->estado,
                'mantenimiento_id' => $mantenimiento->id,
            ]);

            // Cargar relaciones si es necesario
            $mantenimiento->load(['responsable', 'tipoMantenimiento']);

            return response()->json([
                'message' => 'Mantenimiento actualizado exitosamente.',
                'mantenimiento' => $mantenimiento,
            ], 200);
        } catch (Exception $e) {
            Log::error('Error al actualizar el mantenimiento: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ocurrió un error al actualizar el mantenimiento.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function eliminarMantenimiento($id)
    {
        try {
            $mantenimiento = Mantenimiento::find($id);

            if (!$mantenimiento) {
                return response()->json([
                    'message' => 'Mantenimiento no encontrado.',
                ], 404);
            }

            // Eliminar tareas asociadas
            Tarea::where('mantenimiento_id', $id)->delete();
            // Luego, eliminar el mantenimiento
            Mantenimiento::destroy($id);

            return response()->json([
                'message' => 'Mantenimiento eliminado exitosamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al eliminar el Mantenimiento.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function obtenerReportes(Request $request)
    {
        // Obtén el año de la solicitud o usa uno por defecto (por ejemplo, 2025)
        $year = $request->input('year');

        try {
            // Consulta para agrupar por mes y estado
            $datosReporte = DB::table('mantenimientos')
                ->selectRaw('MONTH(fecha) as mes, estado, COUNT(*) as cantidad')
                ->whereYear('fecha', $year)
                ->groupBy(DB::raw('MONTH(fecha)'), 'estado')
                ->get();

            // Define los estados a considerar
            $estados = ['pendiente', 'en_progreso', 'completado', 'cancelado'];

            // Inicializa un array con 12 ceros para cada estado
            $report = [];
            foreach ($estados as $estado) {
                $report[$estado] = array_fill(1, 12, 0);
            }

            Log::info($report);

            // Recorre los resultados y asigna la cantidad a cada mes y estado
            foreach ($datosReporte as $row) {
                $mes = (int)$row->mes;
                $estado = $row->estado;
                if (isset($report[$estado])) {
                    $report[$estado][$mes] = $row->cantidad;
                }
            }

            // Log::info($report);

            // Prepara las series para el gráfico
            $series = [];
            foreach ($report as $estado => $data) {
                $series[] = [
                    'name' => ucfirst($estado),
                    'data' => array_values($data),
                ];
            }
            Log::info("series: " . json_encode($series));

            return response()->json([
                'message' => 'Datos obtenidos exitosamente.',
                'series' => $series,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron obtener los reportes.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
