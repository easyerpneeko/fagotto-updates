<?php

namespace App\Http\Controllers\Controllers_local;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Aplication;
use App\Helpers\ConectionDB;

class ArqueosController extends Controller
{
    /**
     * Obtener arqueos de caja por negocio y fecha
     */
    public function obtenerArqueos(Request $request)
    {
        try {
            // Obtener parámetros
            $appId = $request->get('id') ?: $request->get('app_id');
            $fechaInicio = $request->get('fecha_inicio');
            $fechaFin = $request->get('fecha_fin') ?: $fechaInicio;
            
            if (!$appId) {
                return response()->json([
                    'success' => false,
                    'message' => 'app_id es requerido',
                    'data' => []
                ], 400);
            }

            // PASO CRUCIAL: Obtener la aplicación y cambiar la conexión a su BD
            $app = Aplication::find($appId);
            if (!$app) {
                return response()->json([
                    'success' => false,
                    'message' => 'Negocio no encontrado',
                    'data' => []
                ], 404);
            }

            // Cambiar la conexión mysql_local a la BD del negocio
            ConectionDB::ChangeDBToApp($app, true);
            
            // Log para debugging
            \Log::info('🔍 Consultando arqueos:', [
                'app_id' => $appId,
                'app_name' => $app->name,
                'database' => $app->database->name,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'mysql_local_db' => Config::get('database.connections.mysql_local.database')
            ]);

            // Ahora usar la conexión mysql_local (ya apunta a la BD del negocio)
            $query = DB::connection('mysql_local')->table('TurnosCaja')
                ->select([
                    'id', 'app_id', 'fecha_inicio', 'fecha_termino', 
                    'monto_inicial', 'monto_final', 'total_sistema', 'total_contado',
                    'total_tarjeta', 'total_transferencia', 'total_cheque', 'total_credito',
                    'total_uber_eats', 'total_otros_medios', 'total_general',
                    'diferencia', 'diferencia_general', 'estado', 'usuario_nombre',
                    'observaciones', 'detalle_efectivo', 'detalle_medios_pago',
                    'numero_transacciones', 'created_at', 'updated_at'
                ])
                ->where('app_id', $appId);
            
            // Filtrar por fecha si se proporciona
            if ($fechaInicio) {
                $query->whereDate('fecha_inicio', '>=', $fechaInicio);
            }
            
            if ($fechaFin) {
                $query->whereDate('fecha_inicio', '<=', $fechaFin);
            }
            
            $arqueos = $query->orderBy('fecha_inicio', 'desc')->get();
            
            // Log de resultados
            \Log::info('📊 Arqueos encontrados:', [
                'total' => $arqueos->count(),
                'app_id' => $appId,
                'app_name' => $app->name,
                'database' => $app->database->name,
                'fecha' => $fechaInicio
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Arqueos obtenidos correctamente',
                'data' => [
                    'items' => $arqueos,
                    'total' => $arqueos->count(),
                    'app_info' => [
                        'id' => $app->id,
                        'name' => $app->name,
                        'database' => $app->database->name
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Error obteniendo arqueos:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Crear un nuevo arqueo de caja
     */
    public function crearArqueo(Request $request)
    {
        try {
            $appId = $request->get('app_id');
            if (!$appId) {
                return response()->json([
                    'success' => false,
                    'message' => 'app_id es requerido'
                ], 400);
            }

            // Cambiar conexión a la BD del negocio
            $app = Aplication::find($appId);
            if (!$app) {
                return response()->json([
                    'success' => false,
                    'message' => 'Negocio no encontrado'
                ], 404);
            }

            ConectionDB::ChangeDBToApp($app, true);

            $data = $request->all();
            $data['fecha_inicio'] = $data['fecha_inicio'] ?? now();
            $data['estado'] = $data['estado'] ?? 'abierto';
            $data['created_at'] = now();
            $data['updated_at'] = now();

            $arqueoId = DB::connection('mysql_local')->table('TurnosCaja')->insertGetId($data);

            return response()->json([
                'success' => true,
                'message' => 'Arqueo creado correctamente',
                'data' => [
                    'id' => $arqueoId,
                    'arqueo' => $data
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Error creando arqueo:', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creando arqueo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un arqueo específico por ID
     */
    public function obtenerArqueo($id)
    {
        try {
            // Para este método necesitaríamos también el app_id para cambiar la conexión
            // Por simplicidad, asumiré que se usa dentro del contexto de una app
            $arqueo = DB::connection('mysql_local')->table('TurnosCaja')->where('id', $id)->first();

            if (!$arqueo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arqueo no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $arqueo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo arqueo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}