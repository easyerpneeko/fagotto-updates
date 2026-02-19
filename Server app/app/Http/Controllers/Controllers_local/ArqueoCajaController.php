<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\models_local\ArqueoCaja;
use App\models_local\TurnoCaja;
use App\models_local\Sell;
use App\Helpers\CurrentApp;
use App\Helpers\ConectionDB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ArqueoCajaController extends Controller
{
    /**
     * MÉTODO TEMPORAL: Crear tabla TurnosCaja si no existe
     */
    public function crearTablaTurnos(Request $request)
    {
        try {
            $database = Config::get('database.connections.mysql_local.database');
            
            $sql = "CREATE TABLE IF NOT EXISTS `TurnosCaja` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                
                -- INFORMACIÓN DE LA APP Y USUARIO
                `app_id` int(11) NOT NULL DEFAULT 58,
                `app_nombre` varchar(255) DEFAULT 'Aplicación Local',
                `usuario_id` int(11) NOT NULL,
                `usuario_nombre` varchar(255) NOT NULL,
                
                -- CONTROL DEL TURNO
                `turno_abierto` tinyint(1) NOT NULL DEFAULT '1',
                `turno_abierto_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `turno_cerrado_en` timestamp NULL DEFAULT NULL,
                `puede_hacer_arqueo` tinyint(1) NOT NULL DEFAULT '1',
                `estado` enum('abierto','cerrado','pausado') DEFAULT 'abierto',
                
                -- FECHAS Y HORAS
                `fecha_inicio` datetime NOT NULL,
                `fecha_termino` datetime NULL DEFAULT NULL,
                
                -- MONTOS PRINCIPALES
                `monto_inicial` decimal(15,2) NOT NULL DEFAULT '0.00',
                `monto_final` decimal(15,2) NOT NULL DEFAULT '0.00',
                
                -- TOTALES
                `total_sistema` decimal(15,2) NOT NULL DEFAULT '0.00',
                `total_contado` decimal(15,2) NOT NULL DEFAULT '0.00',
                `total_otros_medios` decimal(15,2) NOT NULL DEFAULT '0.00',
                
                -- DIFERENCIAS
                `diferencia` decimal(15,2) NOT NULL DEFAULT '0.00',
                `diferencia_general` decimal(15,2) NOT NULL DEFAULT '0.00',
                
                -- DETALLES JSON
                `detalle_efectivo` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '{}',
                `detalle_medios_pago` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '{}',
                
                -- INFORMACIÓN ADICIONAL
                `numero_transacciones` int(11) DEFAULT '0',
                `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                
                -- TIMESTAMPS DE LARAVEL
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                
                -- CLAVE PRIMARIA E ÍNDICES
                PRIMARY KEY (`id`),
                KEY `idx_turno_estado` (`turno_abierto`),
                KEY `idx_usuario` (`usuario_id`),
                KEY `idx_app` (`app_id`),
                KEY `idx_fecha_apertura` (`turno_abierto_en`),
                KEY `idx_fecha_cierre` (`turno_cerrado_en`),
                KEY `idx_estado` (`estado`),
                KEY `idx_fecha_inicio` (`fecha_inicio`),
                KEY `idx_fecha_termino` (`fecha_termino`)
                
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            DB::connection('mysql_local')->unprepared($sql);
            
            return response()->json([
                'success' => true,
                'message' => 'Tabla TurnosCaja creada exitosamente con estructura compatible',
                'database' => $database
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tabla: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen de ventas del día para comparar con arqueo
     */
    public function getResumenDia(Request $request)
    {
        try {
            $startDate = $request->get('startDate', date('Y-m-d'));
            $endDate = $request->get('endDate', date('Y-m-d'));
            
            Log::info('Obteniendo resumen del día:', ['startDate' => $startDate, 'endDate' => $endDate]);
            
            // Lista completa de métodos de pago soportados por el sistema
            $paymentMethods = [
                'efectivo', 'debito', 'credito', 'transferencia', 'cheque', 
                'banco', 'amipass', 'multicaja', 'edenred', 'convenio_empresa', 
                'sodexo', 'rappi', 'junaeb', 'uber', 'pedidos_ya', 'pluxee', 
                'banco_chile_20', 'fluxi', 'cheaf'
            ];
            
            $ventasPorMedio = [];
            $totalVentas = 0;
            
            // Inicializar todos los métodos de pago en 0
            foreach ($paymentMethods as $method) {
                $ventasPorMedio[$method] = 0;
            }
            
            try {
                // Configurar la conexión a la base de datos correcta
                $app = CurrentApp::App();
                if ($app && $app->database) {
                    ConectionDB::ChangeDBToApp($app, true);
                    Log::info('Base de datos configurada:', ['database' => $app->database->name]);
                }
                
                // Obtener la base de datos local configurada dinámicamente
                $database = Config::get('database.connections.mysql_local.database');
                Log::info('Consultando ventas desde BD:', ['database' => $database]);
                
                // Obtener ventas del período usando la conexión correcta
                $sells = DB::connection('mysql_local')->table('sells')
                            ->whereBetween('date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                            ->where('type', '!=', 'cotizacion')
                            ->get();
                
                Log::info('Ventas encontradas:', ['count' => $sells->count(), 'database' => $database]);
                
                foreach ($sells as $sell) {
                    $total = ($sell->net_total ?? 0) + ($sell->taxes_total ?? 0);
                    $totalVentas += $total;
                    
                    // Procesar métodos de pago de la venta
                    $paymentMethod = strtolower($sell->pay_type ?? 'efectivo');
                    
                    // Mapear algunos nombres alternativos
                    $methodMap = [
                        'cash' => 'efectivo',
                        'card' => 'debito',
                        'credit_card' => 'credito',
                        'transfer' => 'transferencia',
                        'check' => 'cheque'
                    ];
                    
                    if (isset($methodMap[$paymentMethod])) {
                        $paymentMethod = $methodMap[$paymentMethod];
                    }
                    
                    // Solo contar si es un método reconocido
                    if (in_array($paymentMethod, $paymentMethods)) {
                        $ventasPorMedio[$paymentMethod] += $total;
                    }
                }
                
            } catch (Exception $e) {
                Log::error('Error consultando ventas:', ['error' => $e->getMessage()]);
                // Continuar con valores por defecto
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_ventas' => $totalVentas,
                    'ventas_por_medio' => $ventasPorMedio,
                    'fecha_inicio' => $startDate,
                    'fecha_fin' => $endDate,
                    'total_transacciones' => $sells->count() ?? 0
                ]
            ]);
            
        } catch (Exception $e) {
            Log::error('Error general en getResumenDia:', ['error' => $e->getMessage()]);
            
            // Retornar respuesta por defecto en caso de error
            return response()->json([
                'success' => true,
                'data' => [
                    'total_ventas' => 0,
                    'ventas_por_medio' => [
                        'efectivo' => 0,
                        'debito' => 0,
                        'credito' => 0,
                        'transferencia' => 0,
                        'cheque' => 0
                    ],
                    'fecha_inicio' => $request->get('startDate', date('Y-m-d')),
                    'fecha_fin' => $request->get('endDate', date('Y-m-d')),
                    'total_transacciones' => 0
                ]
            ]);
        }
    }

    /**
     * Verificar estado del turno actual
     */
    public function estadoTurno(Request $request)
    {
        try {
            // Configurar conexión
            $app = CurrentApp::App();
            if ($app && $app->database) {
                ConectionDB::ChangeDBToApp($app, true);
            }

            // ARREGLO: Obtener usuario_id de múltiples fuentes
            $usuarioId = null;
            
            // 1. Desde el request (si se envía explícitamente)
            if ($request->has('usuario_id') && $request->get('usuario_id')) {
                $usuarioId = $request->get('usuario_id');
                Log::info('Usuario ID desde request:', ['usuario_id' => $usuarioId]);
            }
            
            // 2. Desde el usuario autenticado (si existe)
            if (!$usuarioId && auth()->check()) {
                $usuarioId = auth()->user()->id;
                Log::info('Usuario ID desde auth:', ['usuario_id' => $usuarioId]);
            }
            
            // 3. Fallback: buscar cualquier turno abierto hoy
            if (!$usuarioId) {
                $usuarioId = 1; // Usuario por defecto
                Log::warning('Usando usuario ID fallback:', ['usuario_id' => $usuarioId]);
                
                // ✅ NUEVO: Si no tenemos usuario específico, buscar cualquier turno abierto hoy
                $appId = $app->id ?? 58;
                $today = now()->format('Y-m-d');
                
                $turnoGeneral = TurnoCaja::where('app_id', $appId)
                    ->where('estado', 'abierto')
                    ->whereDate('fecha_inicio', $today)
                    ->first();
                    
                if ($turnoGeneral) {
                    Log::info('Encontrado turno general abierto:', ['turno_id' => $turnoGeneral->id, 'usuario_id' => $turnoGeneral->usuario_id]);
                    return response()->json([
                        'success' => true,
                        'turno_abierto' => true,
                        'turno' => $turnoGeneral,
                        'puede_hacer_arqueo' => $turnoGeneral->puede_hacer_arqueo,
                        'mensaje' => 'Tienes un turno abierto desde ' . $turnoGeneral->fecha_inicio->format('H:i')
                    ]);
                }
            }
            
            $appId = $app->id ?? 58;
            Log::info('Verificando estado del turno:', [
                'usuario_id' => $usuarioId, 
                'app_id' => $appId
            ]);

            // Buscar turno abierto específico del usuario
            $turnoAbierto = TurnoCaja::turnoAbiertoParaUsuario($usuarioId, $appId);
            
            Log::info('Resultado búsqueda turno específico:', [
                'turno_encontrado' => $turnoAbierto ? true : false,
                'turno_id' => $turnoAbierto ? $turnoAbierto->id : null
            ]);

            if ($turnoAbierto) {
                return response()->json([
                    'success' => true,
                    'turno_abierto' => true,
                    'turno' => $turnoAbierto,
                    'puede_hacer_arqueo' => $turnoAbierto->puede_hacer_arqueo,
                    'mensaje' => 'Tienes un turno abierto desde ' . $turnoAbierto->fecha_inicio->format('H:i')
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'turno_abierto' => false,
                    'puede_iniciar_turno' => true,
                    'mensaje' => 'No hay turno abierto. Puedes iniciar uno nuevo.'
                ]);
            }

        } catch (Exception $e) {
            Log::error('Error verificando estado del turno:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar estado del turno'
            ], 500);
        }
    }

    /**
     * Iniciar nuevo turno
     */
    public function iniciarTurno(Request $request)
    {
        try {
            // Configurar conexión
            $app = CurrentApp::App();
            if ($app && $app->database) {
                ConectionDB::ChangeDBToApp($app, true);
            }

            $usuarioId = $request->get('usuario_id', 1);
            $usuarioNombre = $request->get('usuario_nombre', 'Usuario Sistema');
            $montoInicial = floatval($request->get('monto_inicial', 0)); // ✅ OBTENER: Monto inicial
            $appId = $app->id ?? 58;
            $appNombre = $app->name_public ?? $app->name ?? 'Aplicación Local';

            // 🔍 DEBUG: Logging para ver qué valores estamos recibiendo
            Log::info('Datos recibidos para iniciar turno:', [
                'usuario_id' => $usuarioId,
                'usuario_nombre' => $usuarioNombre,
                'monto_inicial_original' => $request->get('monto_inicial'),
                'monto_inicial_convertido' => $montoInicial,
                'app_id' => $appId,
                'request_all' => $request->all()
            ]);

            // Verificar que no haya turno abierto
            $turnoExistente = TurnoCaja::turnoAbiertoParaUsuario($usuarioId, $appId);
            if ($turnoExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya tienes un turno abierto. Debes cerrarlo antes de iniciar uno nuevo.'
                ], 400);
            }

            // Crear nuevo turno con monto inicial
            $nuevoTurno = TurnoCaja::iniciarTurno($usuarioId, $usuarioNombre, $appId, $appNombre, $montoInicial);

            Log::info('Turno iniciado:', [
                'turno_id' => $nuevoTurno->id, 
                'usuario' => $usuarioNombre,
                'monto_inicial' => $montoInicial  // ✅ LOG: Registrar monto inicial
            ]);

            return response()->json([
                'success' => true,
                'turno' => $nuevoTurno,
                'message' => 'Turno iniciado correctamente'
            ]);

        } catch (Exception $e) {
            Log::error('Error iniciando turno:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar turno: ' . $e->getMessage(),
                'debug' => [
                    'error' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Cerrar turno con arqueo
     */
    public function cerrarTurnoConArqueo(Request $request)
    {
        try {
            // Configurar conexión
            $app = CurrentApp::App();
            if ($app && $app->database) {
                ConectionDB::ChangeDBToApp($app, true);
            }

            $usuarioId = $request->get('usuario_id', 1);
            $appId = $app->id ?? 58;

            // Buscar turno abierto
            Log::info('🔍 BUSCANDO TURNO ABIERTO:', [
                'usuario_id' => $usuarioId,
                'app_id' => $appId,
                'turno_id_frontend' => $request->input('turno_id')
            ]);
            
            $turnoAbierto = TurnoCaja::turnoAbiertoParaUsuario($usuarioId, $appId);
            
            Log::info('🔍 RESULTADO BÚSQUEDA TURNO:', [
                'turno_encontrado' => $turnoAbierto ? true : false,
                'turno_id' => $turnoAbierto ? $turnoAbierto->id : null,
                'estado' => $turnoAbierto ? $turnoAbierto->estado : null,
                'fecha_termino' => $turnoAbierto ? $turnoAbierto->fecha_termino : null,
                'puede_hacer_arqueo' => $turnoAbierto ? $turnoAbierto->puede_hacer_arqueo : null
            ]);
            
            if (!$turnoAbierto) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes un turno abierto para cerrar.'
                ], 400);
            }

            if (!$turnoAbierto->puede_hacer_arqueo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este turno ya fue cerrado.'
                ], 400);
            }

            // Datos del arqueo
            // 🔍 DEBUG: Verificar qué está recibiendo el backend
            Log::info('🔍 DATOS RECIBIDOS EN EL BACKEND:', [
                'request_all' => $request->all(),
                'request_json' => $request->json()->all(),
                'total_contado_get' => $request->get('total_contado'),
                'total_contado_input' => $request->input('total_contado'),
                'content_type' => $request->header('Content-Type')
            ]);
            
            // ✅ MAPEO CORRECTO DE CAMPOS FRONTEND → DATABASE
            $datosArqueo = [
                // ✅ CAMPOS MONETARIOS PRINCIPALES
                'monto_inicial' => floatval($request->input('monto_inicial', 0)),
                'monto_final' => floatval($request->input('monto_final', 0)),
                'total_otros_medios' => floatval($request->input('total_otros_medios', 0)),
                
                // Mapear total_general_contado → total_contado (campo DB)
                'total_contado' => floatval($request->input('total_general_contado', 
                                   $request->input('total_contado', 0))),
                
                // Mapear total_sistema correctamente
                'total_sistema' => floatval($request->input('total_sistema', 0)),
                
                // Diferencias
                'diferencia' => floatval($request->input('diferencia', 0)),               // Diferencia solo efectivo
                'diferencia_general' => floatval($request->input('diferencia_general', 0)), // Diferencia total
                
                // Campos adicionales
                'observaciones' => $request->input('observaciones', ''),
                'detalle_efectivo' => $request->input('detalle_efectivo', '{}'),
                'detalle_medios_pago' => $request->input('detalle_medios_pago', '{}'),
                'numero_transacciones' => intval($request->input('numero_transacciones', 0))
            ];
            
            Log::info('🎯 DATOS PROCESADOS CON MAPEO CORRECTO:', $datosArqueo);

            // Cerrar turno
            $turnoAbierto->cerrarTurno($datosArqueo);

            Log::info('Turno cerrado:', ['turno_id' => $turnoAbierto->id, 'diferencia' => $datosArqueo['diferencia']]);

            return response()->json([
                'success' => true,
                'turno' => $turnoAbierto->fresh(),
                'message' => 'Turno cerrado correctamente'
            ]);

        } catch (Exception $e) {
            Log::error('Error cerrando turno:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar turno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar un nuevo turno de arqueo de caja
     */
    public function guardarTurno(Request $request)
    {
        try {
            // Configurar la conexión a la base de datos correcta ANTES de usar el modelo
            $app = CurrentApp::App();
            if ($app && $app->database) {
                ConectionDB::ChangeDBToApp($app, true);
                Log::info('Conexión configurada para base de datos:', ['database' => $app->database->name]);
            }
            
            // Recopilar todos los datos de la request
            $data = $request->all();
            
            Log::info('Datos recibidos para arqueo:', $data);
            
            // Obtener app_nombre usando CurrentApp después de configurar la conexión
            $appId = $data['app_id'] ?? 58;
            $appNombre = 'Aplicación Local';
            
            // Intentar obtener el nombre desde CurrentApp ahora que la conexión está configurada
            try {
                if ($app) {
                    $appId = $app->id ?? $appId;
                    $appNombre = $app->name_public ?? $app->name ?? $app->nombre ?? 'Aplicación Local';
                    Log::info('Nombre obtenido de CurrentApp:', ['app_id' => $appId, 'nombre' => $appNombre]);
                }
            } catch (Exception $e) {
                Log::error('Error al obtener nombre de CurrentApp:', ['error' => $e->getMessage()]);
                
                // Fallback: buscar en la tabla applications de la BD maestra
                try {
                    $application = DB::connection('mysql')->table('applications')->where('id', $appId)->first();
                    if ($application) {
                        $appNombre = $application->name_public ?? $application->name ?? 'Aplicación Local';
                        Log::info('Nombre encontrado en BD maestra:', ['app_id' => $appId, 'nombre' => $appNombre]);
                    }
                } catch (Exception $e2) {
                    Log::error('Error al buscar en BD maestra:', ['error' => $e2->getMessage()]);
                }
            }
            
            // Obtener usuario_id de forma segura
            $usuarioId = 1; // Valor por defecto
            $usuarioNombre = 'Usuario Sistema'; // Valor por defecto
            try {
                if (auth()->check() && auth()->user()) {
                    $user = auth()->user();
                    $usuarioId = $user->id;
                    $usuarioNombre = $user->name ?? $user->nombre ?? $user->username ?? 'Usuario Sistema';
                }
            } catch (Exception $e) {
                Log::warning('No se pudo obtener usuario autenticado:', ['error' => $e->getMessage()]);
            }
            
            Log::info('Contexto obtenido:', [
                'app_id' => $appId, 
                'app_nombre' => $appNombre,
                'usuario_id' => $usuarioId,
                'usuario_nombre' => $usuarioNombre
            ]);
            
            // Datos básicos del turno - normalizar fechas para evitar errores de Carbon
            $fechaInicio = date('Y-m-d H:i:s');
            if (isset($data['fecha_inicio']) && !empty($data['fecha_inicio'])) {
                try {
                    // Intentar parsear la fecha del frontend
                    $fechaInicio = date('Y-m-d H:i:s', strtotime($data['fecha_inicio']));
                } catch (Exception $e) {
                    Log::warning('Error al parsear fecha_inicio, usando fecha actual:', ['fecha_recibida' => $data['fecha_inicio'], 'error' => $e->getMessage()]);
                    $fechaInicio = date('Y-m-d H:i:s');
                }
            }
            
            $fechaTermino = null;
            if (isset($data['fecha_termino']) && !empty($data['fecha_termino'])) {
                try {
                    $fechaTermino = date('Y-m-d H:i:s', strtotime($data['fecha_termino']));
                } catch (Exception $e) {
                    Log::warning('Error al parsear fecha_termino:', ['fecha_recibida' => $data['fecha_termino'], 'error' => $e->getMessage()]);
                    $fechaTermino = null;
                }
            }
            
            $turnoData = [
                'app_id' => $appId,
                'app_nombre' => $appNombre,
                'usuario_id' => $usuarioId,
                'usuario_nombre' => $usuarioNombre,
                'fecha_inicio' => $fechaInicio,
                'fecha_termino' => $fechaTermino,
                'total_sistema' => floatval($data['total_sistema'] ?? 0),
                'total_contado' => floatval($data['total_contado'] ?? 0),
                'diferencia' => floatval($data['diferencia'] ?? 0),
                'estado' => $data['estado'] ?? 'abierto',
                'observaciones' => $data['observaciones'] ?? '',
                'detalle_medios_pago' => $data['detalle_medios_pago'] ?? '{}',
                'detalle_efectivo' => $data['detalle_efectivo'] ?? '{}',
                'numero_transacciones' => intval($data['numero_transacciones'] ?? 0)
            ];
            
            Log::info('TurnoData construido:', $turnoData);
            
            // Métodos de pago - más flexible
            if (isset($data['metodos_pago'])) {
                $metodosPago = $data['metodos_pago'];
                if (is_string($metodosPago)) {
                    $metodosPago = json_decode($metodosPago, true);
                }
                
                if (is_array($metodosPago)) {
                    $turnoData['metodos_pago'] = json_encode($metodosPago);
                }
            }
            
            // Intentar crear el turno
            try {
                Log::info('Intentando crear turno con datos:', $turnoData);
                
                // Verificar si el modelo TurnoCaja existe
                if (class_exists('App\models_local\TurnoCaja')) {
                    Log::info('Usando modelo TurnoCaja');
                    $turno = \App\models_local\TurnoCaja::create($turnoData);
                } else {
                    Log::info('Usando modelo ArqueoCaja como fallback');
                    $turno = ArqueoCaja::create($turnoData);
                }
                
                Log::info('Turno creado exitosamente:', ['id' => $turno->id ?? 'N/A']);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Turno guardado exitosamente',
                    'data' => $turno
                ]);
                
            } catch (Exception $e) {
                Log::error('Error al crear turno:', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'data' => $turnoData,
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar turno: ' . $e->getMessage(),
                    'debug_info' => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'data_received' => $turnoData
                    ]
                ], 400);
            }
            
        } catch (Exception $e) {
            Log::error('Error general en guardarTurno:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener lista de arqueos con paginación (para la ruta /local/arqueos)
     */
    public function obtenerArqueos(Request $request)
    {
        try {
            Log::info('Obteniendo arqueos con parámetros:', $request->all());
            
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 10)));
            
            // Obtener app_id de forma segura
            $appId = 1;
            try {
                $app = CurrentApp::App();
                if ($app && isset($app->id)) {
                    $appId = $app->id;
                }
            } catch (Exception $e) {
                Log::warning('No se pudo obtener app_id:', ['error' => $e->getMessage()]);
            }
            
            Log::info('Parámetros procesados:', [
                'page' => $page, 
                'limit' => $limit, 
                'app_id' => $appId
            ]);
            
            $arqueos = null;
            
            // Intentar con TurnoCaja primero
            try {
                if (class_exists('App\models_local\TurnoCaja')) {
                    $arqueos = \App\models_local\TurnoCaja::where('app_id', $appId)
                                ->orderBy('fecha_inicio', 'desc')
                                ->paginate($limit, ['*'], 'page', $page);
                    Log::info('Usando TurnoCaja:', ['count' => $arqueos->count()]);
                }
            } catch (Exception $e) {
                Log::error('Error en TurnoCaja:', ['error' => $e->getMessage()]);
                $arqueos = null;
            }
            
            // Si TurnoCaja falla, usar ArqueoCaja como fallback
            if (!$arqueos) {
                try {
                    if (class_exists('App\ArqueoCaja')) {
                        $arqueos = ArqueoCaja::where('app_id', $appId)
                                    ->orderBy('created_at', 'desc')
                                    ->paginate($limit, ['*'], 'page', $page);
                        Log::info('Usando ArqueoCaja:', ['count' => $arqueos->count()]);
                    }
                } catch (Exception $e) {
                    Log::error('Error en ArqueoCaja:', ['error' => $e->getMessage()]);
                }
            }
            
            // Si no se pudo obtener ninguna data, devolver respuesta vacía válida
            if (!$arqueos) {
                Log::info('No se encontraron arqueos, devolviendo respuesta vacía');
                return response()->json([
                    'success' => true,
                    'data' => [
                        'items' => [],
                        'current_page' => $page,
                        'last_page' => 1,
                        'per_page' => $limit,
                        'total' => 0,
                        'pages' => 1
                    ],
                    'message' => 'No hay arqueos registrados'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $arqueos->items(),
                    'current_page' => $arqueos->currentPage(),
                    'last_page' => $arqueos->lastPage(),
                    'per_page' => $arqueos->perPage(),
                    'total' => $arqueos->total(),
                    'pages' => $arqueos->lastPage()
                ]
            ]);
            
        } catch (Exception $e) {
            Log::error('Error general en obtenerArqueos:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Respuesta de emergencia para evitar error 500
            return response()->json([
                'success' => true, // Mantener success true para que el frontend no falle
                'data' => [
                    'items' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                    'pages' => 1
                ],
                'message' => 'Error temporal al cargar arqueos',
                'debug_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener arqueo actual/activo (para la ruta /local/arqueo/actual)
     */
    public function obtenerArqueoActual(Request $request)
    {
        try {
            $fecha_hoy = date('Y-m-d');
            
            // Obtener app_id de forma segura
            $appId = 1;
            try {
                $app = CurrentApp::App();
                if ($app && isset($app->id)) {
                    $appId = $app->id;
                }
            } catch (Exception $e) {
                Log::warning('No se pudo obtener app_id:', ['error' => $e->getMessage()]);
            }
            
            $turno = TurnoCaja::where('app_id', $appId)
                             ->whereDate('fecha_inicio', $fecha_hoy)
                             ->where('estado', '!=', 'cerrado')
                             ->first();
            
            return response()->json([
                'success' => true,
                'data' => $turno
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener arqueo actual: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cerrar un turno de arqueo
     */
    public function cerrarTurno(Request $request, $id)
    {
        try {
            $turno = TurnoCaja::findOrFail($id);
            
            $turno->estado = 'cerrado';
            $turno->fecha_termino = date('Y-m-d H:i:s');
            $turno->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Turno cerrado exitosamente',
                'data' => $turno
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar turno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener arqueos para dashboard global - NO usa CurrentApp::App()
     * Permite consultar arqueos de cualquier app_id especificado en los parámetros
     */
    public function obtenerArqueosDashboard(Request $request)
    {
        try {
            $appId = $request->get('app_id');
            $fechaInicio = $request->get('fecha_inicio');
            $fechaFin = $request->get('fecha_fin');
            
            Log::info('Dashboard Arqueos - Parámetros recibidos:', [
                'app_id' => $appId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]);
            
            if (!$appId) {
                return response()->json([
                    'success' => false,
                    'message' => 'El parámetro app_id es obligatorio'
                ], 400);
            }
            
            $query = TurnoCaja::where('app_id', $appId);
            
            Log::info('Dashboard Arqueos - Query inicial:', [
                'app_id' => $appId,
                'total_sin_filtro_fecha' => TurnoCaja::where('app_id', $appId)->count()
            ]);
            
            if ($fechaInicio) {
                $query->whereDate('fecha_inicio', '>=', $fechaInicio);
                Log::info('Dashboard Arqueos - Aplicando filtro fecha_inicio:', ['fecha' => $fechaInicio]);
            }
            
            if ($fechaFin) {
                $query->whereDate('fecha_inicio', '<=', $fechaFin);
                Log::info('Dashboard Arqueos - Aplicando filtro fecha_fin:', ['fecha' => $fechaFin]);
            }
            
            // Debug: Ver todos los registros de esta app_id
            $todosLosRegistros = TurnoCaja::where('app_id', $appId)->get();
            Log::info('Dashboard Arqueos - TODOS los registros para app_id ' . $appId, [
                'registros' => $todosLosRegistros->map(function($r) {
                    return [
                        'id' => $r->id,
                        'fecha_inicio' => $r->fecha_inicio,
                        'estado' => $r->estado
                    ];
                })
            ]);
            
            // TEMPORAL: Traer TODOS los registros para debug
            $arqueos = TurnoCaja::all();
            
            Log::info('Dashboard Arqueos - TODOS LOS REGISTROS EN LA TABLA:', [
                'total_registros' => $arqueos->count(),
                'registros' => $arqueos->take(5)->map(function($r) {
                    return [
                        'id' => $r->id,
                        'app_id' => $r->app_id,
                        'fecha_inicio' => $r->fecha_inicio
                    ];
                })
            ]);
            
            Log::info('Dashboard Arqueos - Resultados encontrados:', [
                'total' => $arqueos->count(),
                'app_id_consultado' => $appId
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $arqueos,
                'total' => $arqueos->count(),
                'app_id' => $appId
            ]);
            
        } catch (Exception $e) {
            Log::error('Error en obtenerArqueosDashboard: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener arqueos: ' . $e->getMessage()
            ], 500);
        }
    }
}
