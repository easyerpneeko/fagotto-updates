<?php

namespace App\Http\Controllers\Controllers_local;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComprasFabricaController extends Controller
{
    /**
     * Obtener reporte de ventas por producto desde pedidos finales
     * Agrupa por producto y suma las cantidades vendidas
     */
    public function getVentasPorProducto(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');
            
            // Obtener todos los pedidos finales usando el modelo Requests
            $query = \App\models_local\Requests::select('id', 'products', 'created_at', 'status', 'app_id')
                ->where('status', '!=', 'cancelado');
            
            // Filtrar por rango de fechas si se proporciona
            if ($fechaInicio && $fechaFin) {
                $query->whereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
            }
            
            $pedidos = $query->orderBy('created_at', 'desc')->get();
            
            // Agrupar productos y sumar cantidades
            $ventasPorProducto = [];
            
            foreach ($pedidos as $pedido) {
                $productos = json_decode($pedido->products, true);
                
                if (!$productos) continue;
                
                foreach ($productos as $producto) {
                    $nombre = $producto['name'] ?? 'Sin nombre';
                    $cantidad = floatval($producto['quantity'] ?? 0);
                    $precio = floatval($producto['price'] ?? 0);
                    $unidad = $producto['unidad_medida'] ?? 'unidad';
                    
                    if (!isset($ventasPorProducto[$nombre])) {
                        $ventasPorProducto[$nombre] = [
                            'producto' => $nombre,
                            'cantidad_total' => 0,
                            'unidad_medida' => $unidad,
                            'total_vendido' => 0,
                            'precio_promedio' => 0,
                            'ventas_count' => 0
                        ];
                    }
                    
                    $ventasPorProducto[$nombre]['cantidad_total'] += $cantidad;
                    $ventasPorProducto[$nombre]['total_vendido'] += ($cantidad * $precio);
                    $ventasPorProducto[$nombre]['precio_promedio'] += $precio;
                    $ventasPorProducto[$nombre]['ventas_count']++;
                }
            }
            
            // Calcular precio promedio y ordenar por cantidad
            $resultado = [];
            foreach ($ventasPorProducto as $key => $item) {
                if ($item['ventas_count'] > 0) {
                    $item['precio_promedio'] = $item['precio_promedio'] / $item['ventas_count'];
                }
                $resultado[] = $item;
            }
            
            // Ordenar por cantidad vendida (mayor a menor)
            usort($resultado, function($a, $b) {
                return $b['cantidad_total'] <=> $a['cantidad_total'];
            });
            
            // Calcular estadísticas generales
            $stats = [
                'total_productos' => count($resultado),
                'total_pedidos' => $pedidos->count(),
                'monto_total' => array_sum(array_column($resultado, 'total_vendido')),
                'fecha_inicio' => $fechaInicio ?? 'Todos',
                'fecha_fin' => $fechaFin ?? 'Todos'
            ];
            
            return response()->json([
                'success' => true,
                'data' => $resultado,
                'stats' => $stats
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('ComprasFabricaController@getVentasPorProducto Error: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener reporte de ventas: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener top productos más vendidos
     */
    public function getTopProductos(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            
            // Reutilizar la lógica del reporte completo
            $response = $this->getVentasPorProducto($request);
            $data = json_decode($response->getContent(), true);
            
            if ($data['success']) {
                $topProductos = array_slice($data['data'], 0, $limit);
                
                return response()->json([
                    'success' => true,
                    'data' => $topProductos
                ], 200);
            }
            
            return $response;
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener top productos: ' . $e->getMessage()
            ], 500);
        }
    }
}
