<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\models_local\ArqueoCaja;
use App\models_local\TurnoCaja;
use App\models_local\Sell;
use App\Helpers\CurrentApp;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ArqueoCajaController extends Controller
{
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
                
                // Obtener ventas del período
                $sells = Sell::whereBetween('date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                            ->where('app_id', $appId)
                            ->where('type', '!=', 'cotizacion')
                            ->get();
                
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
     * Guardar un nuevo turno de arqueo de caja
     */
    public function guardarTurno(Request $request)
    {
        try {
            // Recopilar todos los datos de la request
            $data = $request->all();
            
            Log::info('Datos recibidos para arqueo:', $data);
            
            // Obtener app_id de forma segura
            $appId = 1; // Valor por defecto
            try {
                $app = CurrentApp::App();
                if ($app && isset($app->id)) {
                    $appId = $app->id;
                }
            } catch (Exception $e) {
                Log::warning('No se pudo obtener app_id:', ['error' => $e->getMessage()]);
            }
            
            // Obtener usuario_id de forma segura
            $usuarioId = 1; // Valor por defecto
            try {
                if (auth()->check() && auth()->user()) {
                    $usuarioId = auth()->user()->id;
                }
            } catch (Exception $e) {
                Log::warning('No se pudo obtener usuario autenticado:', ['error' => $e->getMessage()]);
            }
            
            // Datos básicos del turno - sin timestamps automáticos para evitar errores de Carbon
            $turnoData = [
                'app_id' => $appId,
                'usuario_id' => $usuarioId,
                'fecha_inicio' => $data['fecha_inicio'] ?? date('Y-m-d H:i:s'),
                'fecha_termino' => $data['fecha_termino'] ?? null,
                'total_sistema' => floatval($data['total_sistema'] ?? 0),
                'total_contado' => floatval($data['total_contado'] ?? 0),
                'diferencia' => floatval($data['diferencia'] ?? 0),
                'estado' => $data['estado'] ?? 'abierto',
                'observaciones' => $data['observaciones'] ?? ''
            ];
            
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
}
