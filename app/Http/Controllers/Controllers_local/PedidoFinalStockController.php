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

    /**
     * Actualizar precio de un producto
     * PUT /api/local/pedidofinal/precio/{id}
     */
    public function updatePrecio(Request $request, $id)
    {
        try {
            $request->validate([
                'precio_por_unidad' => 'required|numeric|min:0'
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

            $precioAnterior = $producto->precio_por_unidad ?? 0;
            $precioNuevo = $request->precio_por_unidad;

            // Actualizar precio del producto
            DB::table('pedidofinal_precios')
                ->where('id', $id)
                ->update([
                    'precio_por_unidad' => $precioNuevo
                ]);

            // Registrar en historial de precios
            DB::table('pedidofinal_precio_history')->insert([
                'producto_id' => $id,
                'producto_nombre' => $producto->producto,
                'precio_anterior' => $precioAnterior,
                'precio_nuevo' => $precioNuevo,
                'usuario' => $request->user ?? 'Sistema',
                'fecha' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Precio actualizado correctamente',
                'data' => [
                    'id' => $id,
                    'producto' => $producto->producto,
                    'precio_anterior' => $precioAnterior,
                    'precio_nuevo' => $precioNuevo
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Datos inválidos',
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar precio',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de cambios de precio
     * GET /api/local/pedidofinal/precio/history
     */
    public function getPrecioHistory(Request $request)
    {
        try {
            $query = DB::table('pedidofinal_precio_history')
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
                'error' => 'Error al obtener historial de precios',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar nombre de un producto
     * PUT /api/local/pedidofinal/nombre/{id}
     */
    public function updateNombre(Request $request, $id)
    {
        try {
            $request->validate([
                'producto' => 'required|string|max:100'
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

            $nombreAnterior = $producto->producto;
            $nombreNuevo = $request->producto;

            // Actualizar nombre del producto
            DB::table('pedidofinal_precios')
                ->where('id', $id)
                ->update([
                    'producto' => $nombreNuevo
                ]);

            // Registrar en historial de nombres
            DB::table('pedidofinal_nombre_history')->insert([
                'producto_id' => $id,
                'nombre_anterior' => $nombreAnterior,
                'nombre_nuevo' => $nombreNuevo,
                'usuario' => $request->user ?? 'Sistema',
                'fecha' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nombre del producto actualizado correctamente',
                'data' => [
                    'id' => $id,
                    'nombre_anterior' => $nombreAnterior,
                    'nombre_nuevo' => $nombreNuevo
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Datos inválidos',
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar nombre',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de cambios de nombre
     * GET /api/local/pedidofinal/nombre/history
     */
    public function getNombreHistory(Request $request)
    {
        try {
            $query = DB::table('pedidofinal_nombre_history')
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
                'error' => 'Error al obtener historial de nombres',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar múltiples campos de un producto (para Excel)
     * PUT /api/local/pedidofinal/stock/update-all/{id}
     */
    public function updateAll(Request $request, $id)
    {
        try {
            // Obtener el producto actual
            $producto = DB::table('pedidofinal_precios')
                ->where('id', $id)
                ->first();

            if (!$producto) {
                return response()->json([
                    'error' => 'Producto no encontrado'
                ], 404);
            }

            // Preparar datos para actualizar
            $datosActualizar = [];
            $cambiosRealizados = [];

            // Campos permitidos para actualizar
            $camposPermitidos = [
                'producto',
                'categoria',
                'stock',
                'unidad_medida',
                'unidad_venta',
                'precio_por_unidad',
                'activo'
            ];

            foreach ($camposPermitidos as $campo) {
                if ($request->has($campo)) {
                    $valorNuevo = $request->input($campo);
                    $valorAnterior = $producto->$campo ?? null;
                    
                    // Solo actualizar si el valor cambió
                    if ($valorNuevo != $valorAnterior) {
                        $datosActualizar[$campo] = $valorNuevo;
                        $cambiosRealizados[$campo] = [
                            'anterior' => $valorAnterior,
                            'nuevo' => $valorNuevo
                        ];
                    }
                }
            }

            // Si hay cambios, actualizar
            if (!empty($datosActualizar)) {
                DB::table('pedidofinal_precios')
                    ->where('id', $id)
                    ->update($datosActualizar);

                // Registrar cambios en historial
                $usuario = $request->user ?? 'Sistema';
                
                foreach ($cambiosRealizados as $campo => $valores) {
                    // Registrar según el tipo de cambio
                    if ($campo === 'stock') {
                        DB::table('pedidofinal_stock_history')->insert([
                            'producto_id' => $id,
                            'producto_nombre' => $datosActualizar['producto'] ?? $producto->producto,
                            'stock_anterior' => $valores['anterior'],
                            'stock_agregado' => $valores['nuevo'] - $valores['anterior'],
                            'stock_nuevo' => $valores['nuevo'],
                            'usuario' => $usuario,
                            'fecha' => now()
                        ]);
                    } elseif ($campo === 'precio_por_unidad') {
                        DB::table('pedidofinal_precio_history')->insert([
                            'producto_id' => $id,
                            'producto_nombre' => $datosActualizar['producto'] ?? $producto->producto,
                            'precio_anterior' => $valores['anterior'],
                            'precio_nuevo' => $valores['nuevo'],
                            'usuario' => $usuario,
                            'fecha' => now()
                        ]);
                    } elseif ($campo === 'producto') {
                        DB::table('pedidofinal_nombre_history')->insert([
                            'producto_id' => $id,
                            'nombre_anterior' => $valores['anterior'],
                            'nombre_nuevo' => $valores['nuevo'],
                            'usuario' => $usuario,
                            'fecha' => now()
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado correctamente',
                'data' => [
                    'id' => $id,
                    'cambios' => $cambiosRealizados
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar producto',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar stock reportado por un negocio
     * POST /api/local/pedidofinal/stock-negocio
     */
    public function registrarStockPorNegocio(Request $request)
    {
        try {
            $request->validate([
                'id_producto' => 'required|integer',
                'cantidad_reportada' => 'required|numeric',
                'id_negocio' => 'nullable|integer',
                'app_id' => 'nullable|string',
                'nombre_negocio' => 'nullable|string',
                'usuario' => 'nullable|string',
                'observacion' => 'nullable|string'
            ]);

            // Obtener datos del producto
            $producto = DB::table('pedidofinal_precios')
                ->where('id', $request->id_producto)
                ->first();

            if (!$producto) {
                return response()->json([
                    'error' => 'Producto no encontrado'
                ], 404);
            }

            // Insertar registro
            $id = DB::table('pedidofinal_stock_por_negocio')->insertGetId([
                'id_producto' => $request->id_producto,
                'producto_nombre' => $producto->producto,
                'id_negocio' => $request->id_negocio,
                'app_id' => $request->app_id,
                'nombre_negocio' => $request->nombre_negocio,
                'cantidad_reportada' => $request->cantidad_reportada,
                'unidad_medida' => $producto->unidad_medida,
                'usuario' => $request->usuario ?? 'Sistema',
                'observacion' => $request->observacion,
                'fecha_registro' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock registrado correctamente',
                'data' => ['id' => $id]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar stock',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de stock por negocio
     * GET /api/local/pedidofinal/stock-negocio
     * Parámetros opcionales: id_negocio, app_id, id_producto, fecha_desde, fecha_hasta
     */
    public function getStockPorNegocio(Request $request)
    {
        try {
            $query = DB::table('pedidofinal_stock_por_negocio');

            // Filtros
            if ($request->has('id_negocio')) {
                $query->where('id_negocio', $request->id_negocio);
            }

            if ($request->has('app_id')) {
                $query->where('app_id', $request->app_id);
            }

            if ($request->has('id_producto')) {
                $query->where('id_producto', $request->id_producto);
            }

            if ($request->has('nombre_negocio')) {
                $query->where('nombre_negocio', 'LIKE', '%' . $request->nombre_negocio . '%');
            }

            if ($request->has('fecha_desde')) {
                $query->where('fecha_registro', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->where('fecha_registro', '<=', $request->fecha_hasta . ' 23:59:59');
            }

            $registros = $query
                ->orderBy('fecha_registro', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $registros,
                'total' => $registros->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener historial',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener último stock reportado por cada negocio para cada producto
     * GET /api/local/pedidofinal/stock-negocio/resumen
     */
    public function getResumenStockPorNegocio(Request $request)
    {
        try {
            // SOLUCIÓN SIMPLE: Traer todos los registros y agrupar en PHP
            $todosLosRegistros = DB::table('pedidofinal_stock_por_negocio')
                ->leftJoin('pedidofinal_precios as p', 'pedidofinal_stock_por_negocio.id_producto', '=', 'p.id')
                ->select(
                    'pedidofinal_stock_por_negocio.*',
                    'p.categoria',
                    'p.precio_por_unidad'
                )
                ->orderBy('pedidofinal_stock_por_negocio.fecha_registro', 'desc')
                ->get();

            \Log::info('🔍 DEBUG - Total registros en tabla: ' . $todosLosRegistros->count());
            
            // Agrupar en PHP para obtener el último por negocio-producto
            $resumen = [];
            $yaVistos = [];
            
            foreach ($todosLosRegistros as $registro) {
                $negocioId = $registro->id_negocio ?? $registro->app_id ?? 'null';
                $productoId = $registro->id_producto;
                $key = "{$negocioId}_{$productoId}";
                
                // Solo agregar si no hemos visto esta combinación
                if (!isset($yaVistos[$key])) {
                    $resumen[] = $registro;
                    $yaVistos[$key] = true;
                    \Log::info("   ✅ Agregado: {$registro->nombre_negocio} - {$registro->producto_nombre}");
                }
            }

            \Log::info('🔍 Resumen Stock por Negocio - Total en resumen: ' . count($resumen));

            return response()->json([
                'success' => true,
                'data' => $resumen,
                'total' => count($resumen),
                'debug_total_registros' => $todosLosRegistros->count()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('❌ Error en getResumenStockPorNegocio: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Error al obtener resumen',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * ADMIN: Obtener último stock reportado por cada negocio para cada producto
     * SIN filtro por llave - Ver todos los negocios
     * GET /api/local/admin/pedidofinal/stock-negocio/resumen
     */
    public function getResumenStockPorNegocioAdmin(Request $request)
    {
        try {
            \Log::info('🔓 ADMIN: getResumenStockPorNegocioAdmin - Sin filtro de llave');
            \Log::info('🔍 ADMIN: Request headers: ' . json_encode($request->headers->all()));
            
            // Usar DB:: normal en vez de connection('master')
            $todosLosRegistros = DB::table('pedidofinal_stock_por_negocio')
                ->leftJoin('pedidofinal_precios as p', 'pedidofinal_stock_por_negocio.id_producto', '=', 'p.id')
                ->select(
                    'pedidofinal_stock_por_negocio.*',
                    'p.categoria',
                    'p.precio_por_unidad'
                )
                ->orderBy('pedidofinal_stock_por_negocio.fecha_registro', 'desc')
                ->get();

            \Log::info('🔍 ADMIN - Total registros en DB: ' . $todosLosRegistros->count());
            
            // Agrupar en PHP para obtener el último por negocio-producto
            $resumen = [];
            $yaVistos = [];
            
            foreach ($todosLosRegistros as $registro) {
                $negocioId = $registro->id_negocio ?? $registro->app_id ?? 'null';
                $productoId = $registro->id_producto;
                $key = "{$negocioId}_{$productoId}";
                
                if (!isset($yaVistos[$key])) {
                    $resumen[] = $registro;
                    $yaVistos[$key] = true;
                }
            }

            \Log::info('✅ ADMIN - Total en resumen: ' . count($resumen));

            return response()->json([
                'success' => true,
                'data' => $resumen,
                'total' => count($resumen)
            ], 200);

        } catch (\Exception $e) {
            \Log::error('❌ Error en getResumenStockPorNegocioAdmin: ' . $e->getMessage());
            \Log::error('❌ Error trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener resumen',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ADMIN: Obtener historial completo de stock por negocio
     * SIN filtro por llave - Ver todos los negocios
     * GET /api/local/admin/pedidofinal/stock-negocio
     */
    public function getStockPorNegocioAdmin(Request $request)
    {
        try {
            \Log::info('🔓 ADMIN: getStockPorNegocioAdmin - Sin filtro de llave');
            \Log::info('🔍 ADMIN: Request headers: ' . json_encode($request->headers->all()));
            
            // Usar DB:: normal en vez de connection('master')
            $query = DB::table('pedidofinal_stock_por_negocio')
                ->leftJoin('pedidofinal_precios as p', 'pedidofinal_stock_por_negocio.id_producto', '=', 'p.id')
                ->select(
                    'pedidofinal_stock_por_negocio.*',
                    'p.categoria',
                    'p.precio_por_unidad'
                );

            // Aplicar filtros si existen
            if ($request->has('id_negocio')) {
                $query->where('id_negocio', $request->id_negocio);
            }

            if ($request->has('app_id')) {
                $query->where('app_id', $request->app_id);
            }

            if ($request->has('id_producto')) {
                $query->where('id_producto', $request->id_producto);
            }

            if ($request->has('nombre_negocio')) {
                $query->where('nombre_negocio', 'LIKE', '%' . $request->nombre_negocio . '%');
            }

            if ($request->has('fecha_desde')) {
                $query->where('fecha_registro', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->where('fecha_registro', '<=', $request->fecha_hasta . ' 23:59:59');
            }

            $registros = $query
                ->orderBy('fecha_registro', 'desc')
                ->get();

            \Log::info('✅ ADMIN - Total registros encontrados: ' . $registros->count());

            return response()->json([
                'success' => true,
                'data' => $registros,
                'total' => $registros->count()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('❌ Error en getStockPorNegocioAdmin: ' . $e->getMessage());
            \Log::error('❌ Error trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener historial',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
