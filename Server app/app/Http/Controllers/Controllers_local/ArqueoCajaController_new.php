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
            
            // Obtener ventas del período
            $sells = Sell::whereBetween('date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                        ->where('app_id', CurrentApp::getApp())
                        ->where('type', '!=', 'cotizacion')
                        ->get();
            
            foreach ($sells as $sell) {
                $total = $sell->net_total + $sell->taxes_total;
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
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total_ventas' => $totalVentas,
                    'ventas_por_medio' => $ventasPorMedio,
                    'fecha_inicio' => $startDate,
                    'fecha_fin' => $endDate,
                    'total_transacciones' => $sells->count()
                ]
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen: ' . $e->getMessage()
            ], 500);
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
    public function index(Request $request)
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
            
            // Validar datos requeridos
            if (!isset($data['metodos_pago']) || !isset($data['total_vendido']) || !isset($data['total_contado'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos incompletos para guardar turno'
                ], 400);
            }

            // Preparar datos del turno con tu nueva estructura
            $turnoData = [
                'app_id' => $data['app_id'] ?? CurrentApp::getApp(),
                'fecha_inicio' => isset($data['hora_inicio']) ? date('Y-m-d H:i:s', strtotime($data['hora_inicio'])) : now(),
                'fecha_termino' => now(),
                'usuario_id' => $data['usuario_id'] ?? auth()->id(),
                'usuario_nombre' => $data['usuario_nombre'] ?? (auth()->user()->name ?? 'Usuario'),
                'total_sistema' => floatval($data['total_vendido']),
                'total_contado' => floatval($data['total_contado']),
                'numero_transacciones' => $data['numero_transacciones'] ?? 0,
                'observaciones' => $data['observaciones'] ?? null
            ];

            // Procesar detalle de efectivo
            $detalleEfectivo = [];
            if (isset($data['efectivo_detalle'])) {
                $totalEfectivoContado = 0;
                foreach ($data['efectivo_detalle'] as $denominacion => $cantidad) {
                    if ($cantidad > 0) {
                        $valor = $this->parseDenominacion($denominacion);
                        $subtotal = $valor * intval($cantidad);
                        $detalleEfectivo[$denominacion] = [
                            'cantidad' => intval($cantidad),
                            'valor_unitario' => $valor,
                            'subtotal' => $subtotal
                        ];
                        $totalEfectivoContado += $subtotal;
                    }
                }
                $detalleEfectivo['total_contado'] = $totalEfectivoContado;
                $detalleEfectivo['total_sistema'] = $data['metodos_pago']['efectivo']['vendido'] ?? 0;
                $detalleEfectivo['diferencia'] = $totalEfectivoContado - ($data['metodos_pago']['efectivo']['vendido'] ?? 0);
            }
            $turnoData['detalle_efectivo'] = $detalleEfectivo;

            // Procesar detalle de medios de pago
            $detalleMediosPago = [];
            $metodosConDiferencia = [];
            
            foreach ($data['metodos_pago'] as $metodo => $valores) {
                $vendido = floatval($valores['vendido'] ?? 0);
                $contado = floatval($valores['contado'] ?? 0);
                $diferencia = $contado - $vendido;
                
                $detalleMediosPago[$metodo] = [
                    'sistema' => $vendido,
                    'contado' => $contado,
                    'diferencia' => $diferencia
                ];

                // Recopilar métodos con diferencia (tolerancia de 0.01 para evitar problemas de float)
                if (abs($diferencia) > 0.01) {
                    $metodosConDiferencia[] = $metodo;
                }
            }
            
            $turnoData['detalle_medios_pago'] = $detalleMediosPago;
            $turnoData['metodos_con_diferencia'] = implode(',', $metodosConDiferencia);

            // Determinar estado según tu ENUM
            $diferenciaTotalAbs = abs($turnoData['total_contado'] - $turnoData['total_sistema']);
            if ($diferenciaTotalAbs == 0) {
                $turnoData['estado'] = 'perfecto';
            } elseif (count($metodosConDiferencia) > 0) {
                $turnoData['estado'] = 'con_diferencias';
            } else {
                $turnoData['estado'] = 'completado';
            }

            // Guardar el turno
            $turno = TurnoCaja::create($turnoData);

            return response()->json([
                'success' => true,
                'message' => 'Turno guardado exitosamente',
                'turno_id' => $turno->id,
                'data' => [
                    'id' => $turno->id,
                    'diferencia_total' => $turno->diferencia_total,
                    'metodos_con_diferencia' => $turno->metodos_con_diferencia,
                    'estado' => $turno->estado,
                    'duracion_minutos' => $turno->duracion_minutos
                ]
            ]);

        } catch (Exception $e) {
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
