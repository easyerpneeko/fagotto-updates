<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\models_local\ArqueoCaja;
use App\models_local\Sell;
use App\Helpers\CurrentApp;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
                'banco_chile_20', 'fluxi'
            ];
            
            $ventasPorMedio = [];
            $totalVentas = 0;
            
            // Obtener ventas por cada método de pago
            foreach ($paymentMethods as $method) {
                $ventas = Sell::where('app_id', CurrentApp::get())
                    ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->where('payment_method', $method)
                    ->where('status', 'completed')
                    ->sum('total');
                
                $ventasPorMedio[$method] = (float)$ventas;
                $totalVentas += $ventas;
            }
            
            // Mapear keys legacy para compatibilidad (si es necesario)
            $ventasPorMedio['tarjeta_debito'] = $ventasPorMedio['debito'];
            $ventasPorMedio['tarjeta_credito'] = $ventasPorMedio['credito'];
            
            // Contar número de transacciones
            $numTransacciones = Sell::where('app_id', CurrentApp::get())
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->where('status', 'completed')
                ->count();

            return response()->json([
                'success' => true,
                'ventasEfectivo' => $ventasPorMedio['efectivo'],
                'ventasTarjetaDebito' => $ventasPorMedio['debito'],
                'ventasTarjetaCredito' => $ventasPorMedio['credito'],
                'ventasTransferencia' => $ventasPorMedio['transferencia'],
                'ventasCheque' => $ventasPorMedio['cheque'],
                'ventasValeVista' => 0, // Legacy
                'ventasOtro' => 0, // Legacy
                'totalVentas' => $totalVentas,
                'numTransacciones' => $numTransacciones,
                'fecha' => $startDate,
                'mediosPago' => $ventasPorMedio
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen del día: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar un nuevo arqueo de caja
     */
    public function guardarArqueo(Request $request)
    {
        try {
            // Validación de datos
            $validator = Validator::make($request->all(), [
                'total_contado' => 'required|numeric|min:0',
                'detalle_conteo' => 'required|string',
                'fecha_arqueo' => 'required|date',
                'observaciones' => 'nullable|string|max:1000',
                'usuario_id' => 'nullable|integer',
                'usuario_nombre' => 'nullable|string|max:255',
                'negocio_id' => 'nullable|integer',
                'negocio_nombre' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Verificar si ya existe un arqueo para el día
            $fechaArqueo = $request->fecha_arqueo;
            $arqueoExistente = ArqueoCaja::where('app_id', CurrentApp::get())
                ->whereDate('fecha_arqueo', $fechaArqueo)
                ->first();

            if ($arqueoExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un arqueo para esta fecha. Use la función de actualizar.'
                ], 400);
            }

            // Obtener ventas en efectivo del día para comparación
            $ventasEfectivo = Sell::where('app_id', CurrentApp::get())
                ->whereDate('created_at', $fechaArqueo)
                ->where('payment_method', 'efectivo')
                ->where('status', 'completed')
                ->sum('total');

            // Calcular diferencia
            $diferencia = $request->total_contado - $ventasEfectivo;

            // Crear nuevo arqueo
            $arqueo = ArqueoCaja::create([
                'app_id' => CurrentApp::get(),
                'fecha_arqueo' => $fechaArqueo,
                'total_contado' => $request->total_contado,
                'total_ventas_efectivo' => $ventasEfectivo,
                'diferencia' => $diferencia,
                'detalle_conteo' => $request->detalle_conteo,
                'observaciones' => $request->observaciones,
                'usuario_id' => $request->usuario_id ?? auth()->user()->id ?? null,
                'usuario_nombre' => $request->usuario_nombre ?? auth()->user()->name ?? 'Sistema',
                'negocio_id' => $request->negocio_id,
                'negocio_nombre' => $request->negocio_nombre
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Arqueo guardado correctamente',
                'data' => $arqueo,
                'diferencia' => $diferencia
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar arqueo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de arqueos
     */
    public function getHistorial(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            
            $arqueos = ArqueoCaja::where('app_id', CurrentApp::get())
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $arqueos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener arqueo específico por fecha
     */
    public function getArqueoPorFecha(Request $request)
    {
        try {
            $fecha = $request->get('fecha', date('Y-m-d'));
            
            $arqueo = ArqueoCaja::where('app_id', CurrentApp::get())
                ->whereDate('fecha_arqueo', $fecha)
                ->first();

            if (!$arqueo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró arqueo para esta fecha'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $arqueo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener arqueo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un arqueo existente
     */
    public function actualizarArqueo(Request $request, $id)
    {
        try {
            $arqueo = ArqueoCaja::where('app_id', CurrentApp::get())
                ->findOrFail($id);

            // Validación de datos
            $validator = Validator::make($request->all(), [
                'total_contado' => 'required|numeric|min:0',
                'detalle_conteo' => 'required|string',
                'observaciones' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Recalcular diferencia
            $diferencia = $request->total_contado - $arqueo->total_ventas_efectivo;

            // Actualizar arqueo
            $arqueo->update([
                'total_contado' => $request->total_contado,
                'diferencia' => $diferencia,
                'detalle_conteo' => $request->detalle_conteo,
                'observaciones' => $request->observaciones,
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Arqueo actualizado correctamente',
                'data' => $arqueo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar arqueo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un arqueo
     */
    public function eliminarArqueo($id)
    {
        try {
            $arqueo = ArqueoCaja::where('app_id', CurrentApp::get())
                ->findOrFail($id);

            $arqueo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Arqueo eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar arqueo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte de arqueos por período
     */
    public function reporteArqueos(Request $request)
    {
        try {
            $startDate = $request->get('startDate', date('Y-m-d', strtotime('-30 days')));
            $endDate = $request->get('endDate', date('Y-m-d'));

            $arqueos = ArqueoCaja::where('app_id', CurrentApp::get())
                ->whereBetween('fecha_arqueo', [$startDate, $endDate])
                ->orderBy('fecha_arqueo', 'desc')
                ->get();

            // Calcular estadísticas
            $totalArqueos = $arqueos->count();
            $totalContado = $arqueos->sum('total_contado');
            $totalVentasEfectivo = $arqueos->sum('total_ventas_efectivo');
            $totalDiferencias = $arqueos->sum('diferencia');
            $promedioContado = $totalArqueos > 0 ? $totalContado / $totalArqueos : 0;

            return response()->json([
                'success' => true,
                'data' => $arqueos,
                'estadisticas' => [
                    'total_arqueos' => $totalArqueos,
                    'total_contado' => $totalContado,
                    'total_ventas_efectivo' => $totalVentasEfectivo,
                    'total_diferencias' => $totalDiferencias,
                    'promedio_contado' => $promedioContado
                ],
                'periodo' => [
                    'inicio' => $startDate,
                    'fin' => $endDate
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }
}
