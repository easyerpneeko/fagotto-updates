<?php

namespace App\Http\Controllers\Controllers_local;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\CurrentApp;

class PedidoFinalStockController extends Controller
{
    /**
     * Obtener todos los productos con stock
     * GET /api/local/pedidofinal/stock
     */
    public function getStock(Request $request)
    {
        try {
            $productos = DB::table('pedidofinal_precios')
                ->where('activo', 1)
                ->orderBy('categoria', 'asc')
                ->orderBy('producto', 'asc')
                ->get();

            return response()->json($productos, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener productos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar stock de un producto
     * PUT /api/local/pedidofinal/stock/{id}
     */
    public function updateStock(Request $request, $id)
    {
        try {
            $request->validate([
                'stock' => 'required|numeric',
                'tipo' => 'required|in:agregar,establecer'
            ]);

            // Obtener el producto actual
            $producto = DB::table('pedidofinal_precios')
                ->where('id', $id)
                ->first();

            if (!$producto) {
                return response()->json([
                    'error' => 'Producto no encontrado'
                ], 404);
            }

            $stockAnterior = $producto->stock ?? 0;
            $stockNuevo = 0;
            $stockAgregado = 0;

            // Calcular nuevo stock según el tipo
            if ($request->tipo === 'agregar') {
                $stockAgregado = $request->stock;
                $stockNuevo = $stockAnterior + $stockAgregado;
            } else { // establecer
                $stockNuevo = $request->stock;
                $stockAgregado = $stockNuevo - $stockAnterior;
            }

            // Actualizar stock del producto
            DB::table('pedidofinal_precios')
                ->where('id', $id)
                ->update([
                    'stock' => $stockNuevo
                ]);

            // Registrar en historial
            DB::table('pedidofinal_stock_history')->insert([
                'producto_id' => $id,
                'producto_nombre' => $producto->producto,
                'stock_anterior' => $stockAnterior,
                'stock_agregado' => $stockAgregado,
                'stock_nuevo' => $stockNuevo,
                'usuario' => $request->user ?? 'Sistema',
                'fecha' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock actualizado correctamente',
                'data' => [
                    'id' => $id,
                    'producto' => $producto->producto,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'stock_agregado' => $stockAgregado
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Datos inválidos',
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar stock',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de cambios de stock
     * GET /api/local/pedidofinal/stock/history
     */
    public function getStockHistory(Request $request)
    {
        try {
            $query = DB::table('pedidofinal_stock_history')
                ->orderBy('fecha', 'desc')
                ->orderBy('id', 'desc');

            // Filtrar por producto si se proporciona
            if ($request->has('producto_id')) {
                $query->where('producto_id', $request->producto_id);
            }

            // Filtrar por fecha si se proporciona
            if ($request->has('fecha_inicio')) {
                $query->where('fecha', '>=', $request->fecha_inicio);
            }

            if ($request->has('fecha_fin')) {
                $query->where('fecha', '<=', $request->fecha_fin . ' 23:59:59');
            }

            // Limitar resultados si se proporciona
            $limit = $request->input('limit', 100);
            $historial = $query->limit($limit)->get();

            return response()->json($historial, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener historial',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
