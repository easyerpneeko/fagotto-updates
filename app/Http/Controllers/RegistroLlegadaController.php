<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\User;

class RegistroLlegadaController extends Controller
{
    /**
     * Obtener todos los registros de llegada
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $fecha = $request->get('fecha', Carbon::today()->format('Y-m-d'));
            $limit = $request->get('limit', 50);
            
            $query = DB::table('registros_llegada as rl')
                ->join('users as u', 'rl.empleado_id', '=', 'u.id')
                ->select(
                    'rl.id',
                    'rl.empleado_id',
                    'u.name as empleado_nombre',
                    'u.cargo as empleado_cargo',
                    'rl.fecha_registro',
                    'rl.fingerprint_hash',
                    'rl.ip_address',
                    'rl.user_agent',
                    'rl.created_at'
                )
                ->where('rl.fecha_solo', $fecha)
                ->orderBy('rl.fecha_registro', 'desc')
                ->limit($limit);

            $registros = $query->get();

            return response()->json([
                'success' => true,
                'data' => $registros,
                'fecha_filtro' => $fecha,
                'total' => $registros->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los registros de llegada',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo registro de llegada
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Debug: Log datos recibidos
            \Log::info('========== REGISTRO LLEGADA DEBUG ==========');
            \Log::info('Datos recibidos:', $request->all());
            \Log::info('Headers:', $request->headers->all());
            \Log::info('============================================');
            
            // Validar datos
            $validator = Validator::make($request->all(), [
                'empleado_id' => 'required|integer|exists:users,id',
                'fingerprint_data' => 'required|array',
                'fingerprint_data.id' => 'required|string',
                'fingerprint_data.type' => 'required|string',
                'fingerprint_data.rawId' => 'required|array',
                'fingerprint_data.response' => 'required|array'
            ]);

            if ($validator->fails()) {
                \Log::error('Validación falló:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de registro inválidos',
                    'errors' => $validator->errors(),
                    'received_data' => $request->all() // DEBUG: Incluir datos recibidos
                ], 400);
            }

            $empleadoId = $request->input('empleado_id');
            $fingerprintData = $request->input('fingerprint_data');
            
            // Verificar que el empleado existe
            $empleado = User::find($empleadoId);
            if (!$empleado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            // Verificar si ya existe un registro hoy para este empleado
            $registroExistente = DB::table('registros_llegada')
                ->where('empleado_id', $empleadoId)
                ->where('fecha_solo', Carbon::today()->format('Y-m-d'))
                ->first();

            if ($registroExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'El empleado ya tiene un registro de llegada el día de hoy',
                    'registro_existente' => [
                        'id' => $registroExistente->id,
                        'fecha_registro' => $registroExistente->fecha_registro
                    ]
                ], 409);
            }

            // Crear hash de la huella dactilar para almacenamiento seguro
            $fingerprintHash = hash('sha256', json_encode($fingerprintData));
            
            // Obtener información adicional
            $ipAddress = $request->ip();
            $userAgent = $request->header('User-Agent');
            
            // Crear el registro
            $registroId = DB::table('registros_llegada')->insertGetId([
                'empleado_id' => $empleadoId,
                'fecha_registro' => Carbon::now(),
                'fecha_solo' => Carbon::today()->format('Y-m-d'),
                'fingerprint_hash' => $fingerprintHash,
                'fingerprint_credential_id' => $fingerprintData['id'],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // Obtener el registro completo para la respuesta
            $registro = DB::table('registros_llegada as rl')
                ->join('users as u', 'rl.empleado_id', '=', 'u.id')
                ->select(
                    'rl.id',
                    'rl.empleado_id',
                    'u.name as empleado_nombre',
                    'u.cargo as empleado_cargo',
                    'rl.fecha_registro',
                    'rl.created_at'
                )
                ->where('rl.id', $registroId)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Registro de llegada creado exitosamente',
                'data' => $registro
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el registro de llegada',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas del día
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function estadisticas(Request $request)
    {
        try {
            $fecha = $request->get('fecha', Carbon::today()->format('Y-m-d'));
            
            // Total de registros hoy
            $totalHoy = DB::table('registros_llegada')
                ->where('fecha_solo', $fecha)
                ->count();

            // Empleados que han registrado llegada hoy
            $empleadosActivos = DB::table('registros_llegada')
                ->where('fecha_solo', $fecha)
                ->distinct('empleado_id')
                ->count();

            // Hora promedio de llegada
            $promedioHora = DB::table('registros_llegada')
                ->where('fecha_solo', $fecha)
                ->avg(DB::raw('TIME_TO_SEC(TIME(fecha_registro))'));

            $horaPromedioFormateada = '--:--';
            if ($promedioHora) {
                $horas = floor($promedioHora / 3600);
                $minutos = floor(($promedioHora % 3600) / 60);
                $horaPromedioFormateada = sprintf('%02d:%02d', $horas, $minutos);
            }

            // Total de empleados activos en el sistema
            $totalEmpleados = User::where('status', 'active')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_hoy' => $totalHoy,
                    'empleados_activos' => $empleadosActivos,
                    'total_empleados' => $totalEmpleados,
                    'promedio_hora' => $horaPromedioFormateada,
                    'fecha' => $fecha
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener lista de empleados
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function empleados(Request $request)
    {
        try {
            $empleados = User::select('id', 'name', 'email', 'cargo')
                ->where('status', 'active')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $empleados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de empleados',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de un registro específico
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $registro = DB::table('registros_llegada as rl')
                ->join('users as u', 'rl.empleado_id', '=', 'u.id')
                ->select(
                    'rl.id',
                    'rl.empleado_id',
                    'u.name as empleado_nombre',
                    'u.email as empleado_email',
                    'u.cargo as empleado_cargo',
                    'rl.fecha_registro',
                    'rl.ip_address',
                    'rl.user_agent',
                    'rl.created_at'
                )
                ->where('rl.id', $id)
                ->first();

            if (!$registro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registro no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $registro
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un registro (solo para administradores)
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $registro = DB::table('registros_llegada')->where('id', $id)->first();
            
            if (!$registro) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registro no encontrado'
                ], 404);
            }

            DB::table('registros_llegada')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}