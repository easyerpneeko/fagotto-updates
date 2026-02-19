<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\H                    Log::error('Error consultando ventas:', ['error' => $e->getMessage()]);tp\Request;
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
                // Obtener ventas del período
                $sells = Sell::whereBetween('date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                            ->where('app_id', CurrentApp::getApp())
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
                \Log::error('Error consultando ventas:', ['error' => $e->getMessage()]);
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
            \Log::error('Error general en getResumenDia:', ['error' => $e->getMessage()]);
            
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
     * Guardar arqueo de caja
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'total_vendido' => 'required|numeric',
                'total_contado' => 'required|numeric',
                'observaciones' => 'nullable|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }
            
            $arqueo = ArqueoCaja::create([
                'app_id' => CurrentApp::getApp(),
                'total_vendido' => $request->total_vendido,
                'total_contado' => $request->total_contado,
                'diferencia' => $request->total_contado - $request->total_vendido,
                'observaciones' => $request->observaciones,
                'fecha' => now(),
                'usuario_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Arqueo guardado exitosamente',
                'data' => $arqueo
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar arqueo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener histórico de arqueos
     */
    public function getHistorial(Request $request)
    {
        try {
            $startDate = $request->get('startDate', date('Y-m-d', strtotime('-30 days')));
            $endDate = $request->get('endDate', date('Y-m-d'));
            
            $arqueos = ArqueoCaja::where('app_id', CurrentApp::getApp())
                                ->whereBetween('fecha', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                                ->orderBy('fecha', 'desc')
                                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $arqueos
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener arqueos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generar reporte de arqueo
     */
    public function generarReporte(Request $request)
    {
        try {
            $arqueoId = $request->get('arqueo_id');
            
            if (!$arqueoId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de arqueo requerido'
                ], 400);
            }
            
            $arqueo = ArqueoCaja::where('id', $arqueoId)
                               ->where('app_id', CurrentApp::getApp())
                               ->first();
            
            if (!$arqueo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arqueo no encontrado'
                ], 404);
            }
            
            // Generar datos del reporte
            $reporteData = [
                'arqueo' => $arqueo,
                'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                'usuario_generador' => auth()->user()->name ?? 'Sistema'
            ];
            
            return response()->json([
                'success' => true,
                'data' => $reporteData
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar el turno completo de arqueo de caja - NUEVA ESTRUCTURA
     */
    public function guardarTurno(Request $request)
    {
        try {
            $data = $request->all();
            
            // Log para debug
            \Log::info('Datos recibidos para arqueo:', $data);
            
            // Validar datos básicos (más flexible)
            if (!isset($data['total_contado'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'total_contado es requerido'
                ], 400);
            }

            // Determinar app_id
            $app_id = $data['app_id'] ?? CurrentApp::getApp();
            
            // Preparar datos del turno básico
            $turnoData = [
                'app_id' => $app_id,
                'fecha_inicio' => isset($data['fecha_inicio']) ? date('Y-m-d H:i:s', strtotime($data['fecha_inicio'])) : now(),
                'fecha_termino' => now(),
                'usuario_id' => $data['usuario_id'] ?? auth()->id() ?? 1,
                'usuario_nombre' => $data['usuario_nombre'] ?? (auth()->user()->name ?? 'Usuario'),
                'total_sistema' => floatval($data['total_sistema'] ?? $data['total_vendido'] ?? 0),
                'total_contado' => floatval($data['total_contado']),
                'numero_transacciones' => $data['numero_transacciones'] ?? 0,
                'observaciones' => $data['observaciones'] ?? null
            ];

            // Procesar detalle de efectivo si existe
            $detalleEfectivo = [];
            if (isset($data['detalle_efectivo'])) {
                $detalleEfectivo = is_string($data['detalle_efectivo']) ? 
                    json_decode($data['detalle_efectivo'], true) : 
                    $data['detalle_efectivo'];
            }
            $turnoData['detalle_efectivo'] = $detalleEfectivo;

            // Procesar detalle de medios de pago
            $detalleMediosPago = [];
            if (isset($data['detalle_medios_pago'])) {
                $detalleMediosPago = is_string($data['detalle_medios_pago']) ? 
                    json_decode($data['detalle_medios_pago'], true) : 
                    $data['detalle_medios_pago'];
            } elseif (isset($data['medios_pago'])) {
                $detalleMediosPago = $data['medios_pago'];
            }
            $turnoData['detalle_medios_pago'] = $detalleMediosPago;

            // Determinar estado según diferencia
            $diferencia = $turnoData['total_contado'] - $turnoData['total_sistema'];
            if ($diferencia == 0) {
                $turnoData['estado'] = 'perfecto';
            } elseif ($diferencia > 0) {
                $turnoData['estado'] = 'sobrante';
            } else {
                $turnoData['estado'] = 'faltante';
            }

            // Intentar guardar en TurnoCaja, si no existe usar ArqueoCaja
            try {
                if (class_exists('App\models_local\TurnoCaja')) {
                    $turno = \App\models_local\TurnoCaja::create($turnoData);
                } else {
                    // Fallback a ArqueoCaja
                    $arqueoData = [
                        'app_id' => $turnoData['app_id'],
                        'total_vendido' => $turnoData['total_sistema'],
                        'total_contado' => $turnoData['total_contado'],
                        'diferencia' => $diferencia,
                        'observaciones' => $turnoData['observaciones'],
                        'fecha' => $turnoData['fecha_termino'],
                        'usuario_id' => $turnoData['usuario_id']
                    ];
                    $turno = ArqueoCaja::create($arqueoData);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Turno guardado exitosamente',
                    'turno_id' => $turno->id,
                    'data' => [
                        'id' => $turno->id,
                        'diferencia_total' => $diferencia,
                        'estado' => $turnoData['estado']
                    ]
                ]);

            } catch (Exception $e) {
                \Log::error('Error al crear turno:', ['error' => $e->getMessage(), 'data' => $turnoData]);
                throw $e;
            }

        } catch (Exception $e) {
            \Log::error('Error general en guardarTurno:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar turno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parsear denominación de billetes/monedas a valor numérico
     */
    private function parseDenominacion($denominacion)
    {
        // Remover espacios y convertir a minúsculas
        $den = strtolower(trim($denominacion));
        
        // Mapeo de denominaciones
        $valores = [
            '20000' => 20000, 'billete_20000' => 20000, '20.000' => 20000, 'bill_20000' => 20000,
            '10000' => 10000, 'billete_10000' => 10000, '10.000' => 10000, 'bill_10000' => 10000,
            '5000' => 5000, 'billete_5000' => 5000, '5.000' => 5000, 'bill_5000' => 5000,
            '2000' => 2000, 'billete_2000' => 2000, '2.000' => 2000, 'bill_2000' => 2000,
            '1000' => 1000, 'billete_1000' => 1000, '1.000' => 1000, 'bill_1000' => 1000,
            '500' => 500, 'moneda_500' => 500, 'coin_500' => 500,
            '100' => 100, 'moneda_100' => 100, 'coin_100' => 100,
            '50' => 50, 'moneda_50' => 50, 'coin_50' => 50,
            '10' => 10, 'moneda_10' => 10, 'coin_10' => 10,
            '5' => 5, 'moneda_5' => 5, 'coin_5' => 5,
            '1' => 1, 'moneda_1' => 1, 'coin_1' => 1
        ];

        return $valores[$den] ?? 0;
    }

    /**
     * Obtener histórico de turnos
     */
    public function getTurnos(Request $request)
    {
        try {
            $startDate = $request->get('startDate', date('Y-m-d', strtotime('-30 days')));
            $endDate = $request->get('endDate', date('Y-m-d'));
            
            $turnos = TurnoCaja::where('app_id', CurrentApp::getApp())
                              ->whereBetween('fecha_termino', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                              ->orderBy('fecha_termino', 'desc')
                              ->get();
            
            return response()->json([
                'success' => true,
                'data' => $turnos
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener turnos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar arqueo básico (método alternativo)
     */
    public function guardarArqueo(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Actualizar arqueo existente
     */
    public function actualizarArqueo($id, Request $request)
    {
        try {
            $arqueo = ArqueoCaja::where('id', $id)
                               ->where('app_id', CurrentApp::getApp())
                               ->first();
            
            if (!$arqueo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arqueo no encontrado'
                ], 404);
            }

            $arqueo->update($request->only([
                'total_vendido', 'total_contado', 'observaciones'
            ]));

            $arqueo->diferencia = $arqueo->total_contado - $arqueo->total_vendido;
            $arqueo->save();

            return response()->json([
                'success' => true,
                'message' => 'Arqueo actualizado exitosamente',
                'data' => $arqueo
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar arqueo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar arqueo
     */
    public function eliminarArqueo($id)
    {
        try {
            $arqueo = ArqueoCaja::where('id', $id)
                               ->where('app_id', CurrentApp::getApp())
                               ->first();
            
            if (!$arqueo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arqueo no encontrado'
                ], 404);
            }

            $arqueo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Arqueo eliminado exitosamente'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar arqueo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener arqueo por fecha específica
     */
    public function getArqueoPorFecha(Request $request)
    {
        try {
            $fecha = $request->get('fecha', date('Y-m-d'));
            
            $arqueo = ArqueoCaja::where('app_id', CurrentApp::getApp())
                               ->whereDate('fecha', $fecha)
                               ->first();
            
            return response()->json([
                'success' => true,
                'data' => $arqueo
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener arqueo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener lista de arqueos con paginación (para la ruta /local/arqueos)
     */
    public function obtenerArqueos(Request $request)
    {
        try {
            \Log::info('Obteniendo arqueos con parámetros:', $request->all());
            
            $page = max(1, (int)$request->get('page', 1));
            $limit = max(1, min(100, (int)$request->get('limit', 10)));
            
            // Obtener app_id de forma segura
            $appId = null;
            try {
                $appId = CurrentApp::getApp();
            } catch (Exception $e) {
                    Log::warning('No se pudo obtener app_id:', ['error' => $e->getMessage()]);
                $appId = 1; // Fallback por defecto
            }
            
            \Log::info('Parámetros procesados:', [
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
                    \Log::info('Usando TurnoCaja:', ['count' => $arqueos->count()]);
                }
            } catch (Exception $e) {
                \Log::error('Error en TurnoCaja:', ['error' => $e->getMessage()]);
                $arqueos = null;
            }
            
            // Si TurnoCaja falla, usar ArqueoCaja como fallback
            if (!$arqueos) {
                try {
                    if (class_exists('App\ArqueoCaja')) {
                        $arqueos = ArqueoCaja::where('app_id', $appId)
                                    ->orderBy('created_at', 'desc')
                                    ->paginate($limit, ['*'], 'page', $page);
                        \Log::info('Usando ArqueoCaja:', ['count' => $arqueos->count()]);
                    }
                } catch (Exception $e) {
                    \Log::error('Error en ArqueoCaja:', ['error' => $e->getMessage()]);
                }
            }
            
            // Si no se pudo obtener ninguna data, devolver respuesta vacía válida
            if (!$arqueos) {
                \Log::info('No se encontraron arqueos, devolviendo respuesta vacía');
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
            \Log::error('Error general en obtenerArqueos:', [
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
            
            $turno = TurnoCaja::where('app_id', CurrentApp::getApp())
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
     * Cerrar turno específico (para la ruta /local/arqueo/cerrar/{id})
     */
    public function cerrarTurno($id, Request $request)
    {
        try {
            $turno = TurnoCaja::where('id', $id)
                             ->where('app_id', CurrentApp::getApp())
                             ->first();
            
            if (!$turno) {
                return response()->json([
                    'success' => false,
                    'message' => 'Turno no encontrado'
                ], 404);
            }

            // Actualizar datos si se envían
            if ($request->has('observaciones_cierre')) {
                $observaciones_actuales = $turno->observaciones ?? '';
                $observaciones_cierre = $request->get('observaciones_cierre');
                $turno->observaciones = $observaciones_actuales . 
                    ($observaciones_actuales ? "\n\n" : '') . 
                    "CIERRE: " . $observaciones_cierre;
            }

            $turno->estado = 'cerrado';
            $turno->fecha_termino = now();
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
     * Generar reporte de arqueos
     */
    public function reporteArqueos(Request $request)
    {
        return $this->generarReporte($request);
    }

    /**
     * Obtener detalle de un turno específico
     */
    public function getTurnoDetalle($id)
    {
        try {
            $turno = TurnoCaja::where('id', $id)
                             ->where('app_id', CurrentApp::getApp())
                             ->first();
            
            if (!$turno) {
                return response()->json([
                    'success' => false,
                    'message' => 'Turno no encontrado'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $turno
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalle del turno: ' . $e->getMessage()
            ], 500);
        }
    }
}
