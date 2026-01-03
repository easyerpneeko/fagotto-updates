<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CurrentApp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class MercadiseController extends Controller
{
    /**
     * GET /api/mercadise/menu
     * Endpoint para que Mercadise lea el catálogo de productos
     * Identifica el local automáticamente por el header App-Key
     */
    public function getMenu(Request $request)
    {
        try {
            // Obtener la aplicación actual desde el middleware (automático con App-Key)
            $app = CurrentApp::App();
            
            if (!$app) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo identificar el local. Verifique el header App-Key'
                ], 401);
            }

            Log::info('Mercadise solicitando menú', [
                'app_id' => $app->id ?? $app->Id,
                'app_name' => $app->Name ?? $app->name,
                'serial' => $app->Serial ?? $app->serial
            ]);

            // Obtener productos activos de la tabla merchise_productos
            $productos = DB::connection('easyerp_master')
                ->table('merchise_productos')
                ->where('activo', 1)
                ->orderBy('orden', 'asc')
                ->orderBy('categoria', 'asc')
                ->orderBy('nombre', 'asc')
                ->get();

            // Formatear respuesta para Mercadise
            $productosFormateados = $productos->map(function($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'precio' => floatval($producto->precio),
                    'categoria' => $producto->categoria,
                    'descripcion' => $producto->descripcion,
                    'foto' => $producto->foto,
                    'disponible' => (bool) $producto->activo,
                    'orden' => $producto->orden
                ];
            });

            return response()->json([
                'success' => true,
                'local' => [
                    'id' => $app->Id ?? $app->id,
                    'nombre' => $app->Name ?? $app->name,
                    'serial' => $app->Serial ?? $app->serial
                ],
                'productos' => $productosFormateados,
                'total_productos' => $productosFormateados->count(),
                'timestamp' => now()->toDateTimeString()
            ], 200);

        } catch (Exception $e) {
            Log::error('Error al obtener menú Mercadise', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el menú',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/webhooks/mercadise
     * Webhook para recibir pedidos desde Mercadise
     * Identifica el local automáticamente por el header App-Key
     */
    public function receiveOrder(Request $request)
    {
        try {
            // Obtener la aplicación actual desde el middleware
            $app = CurrentApp::App();
            
            if (!$app) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo identificar el local. Verifique el header App-Key'
                ], 401);
            }

            $appId = $app->Id ?? $app->id;

            Log::info('Mercadise enviando pedido', [
                'app_id' => $appId,
                'app_name' => $app->Name ?? $app->name,
                'request_data' => $request->all()
            ]);

            // Validar datos del pedido
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|string',
                'customer.name' => 'required|string',
                'customer.phone' => 'required|string',
                'customer.address' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'nullable|integer',
                'items.*.nombre' => 'required|string',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.precio' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'payment_method' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                Log::warning('Validación fallida en pedido Mercadise', [
                    'errors' => $validator->errors()->toArray()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Datos del pedido inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            // Verificar si el pedido ya existe (evitar duplicados)
            $pedidoExistente = DB::connection('easyerp_master')
                ->table('merchise_pedidos')
                ->where('order_id_mercadise', $data['order_id'])
                ->first();

            if ($pedidoExistente) {
                Log::warning('Pedido duplicado detectado', [
                    'order_id' => $data['order_id'],
                    'app_id' => $appId
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pedido ya registrado previamente',
                    'order_id' => $data['order_id'],
                    'status' => $pedidoExistente->status
                ], 200);
            }

            // Insertar pedido en la base de datos
            $pedidoId = DB::connection('easyerp_master')
                ->table('merchise_pedidos')
                ->insertGetId([
                    'app_id' => $appId,
                    'order_id_mercadise' => $data['order_id'],
                    'customer_name' => $data['customer']['name'],
                    'customer_phone' => $data['customer']['phone'],
                    'customer_address' => $data['customer']['address'] ?? null,
                    'items' => json_encode($data['items'], JSON_UNESCAPED_UNICODE),
                    'total' => $data['total'],
                    'status' => 'pending',
                    'payment_method' => $data['payment_method'] ?? 'efectivo',
                    'received_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

            Log::info('Pedido Mercadise registrado exitosamente', [
                'pedido_id' => $pedidoId,
                'order_id' => $data['order_id'],
                'app_id' => $appId,
                'total' => $data['total']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pedido recibido exitosamente',
                'pedido_id' => $pedidoId,
                'order_id' => $data['order_id'],
                'status' => 'received',
                'estimated_time' => '30 minutos',
                'local' => [
                    'id' => $appId,
                    'nombre' => $app->Name ?? $app->name
                ]
            ], 201);

        } catch (Exception $e) {
            Log::error('Error al recibir pedido Mercadise', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/mercadise/orders/{orderId}/status
     * Actualizar estado de un pedido (para uso interno)
     */
    public function updateOrderStatus(Request $request, $orderId)
    {
        try {
            $app = CurrentApp::App();
            $appId = $app->Id ?? $app->id;

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,accepted,preparing,ready,delivered,cancelled'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar el pedido
            $pedido = DB::connection('easyerp_master')
                ->table('merchise_pedidos')
                ->where('id', $orderId)
                ->where('app_id', $appId)
                ->first();

            if (!$pedido) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pedido no encontrado'
                ], 404);
            }

            $status = $request->status;
            $updateData = [
                'status' => $status,
                'updated_at' => now()
            ];

            // Actualizar timestamps según el estado
            switch ($status) {
                case 'accepted':
                    $updateData['accepted_at'] = now();
                    break;
                case 'ready':
                    $updateData['ready_at'] = now();
                    break;
                case 'delivered':
                    $updateData['delivered_at'] = now();
                    break;
            }

            DB::connection('easyerp_master')
                ->table('merchise_pedidos')
                ->where('id', $orderId)
                ->update($updateData);

            Log::info('Estado de pedido Mercadise actualizado', [
                'pedido_id' => $orderId,
                'order_id' => $pedido->order_id_mercadise,
                'status' => $status,
                'app_id' => $appId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente',
                'order_id' => $pedido->order_id_mercadise,
                'status' => $status
            ], 200);

        } catch (Exception $e) {
            Log::error('Error al actualizar estado de pedido Mercadise', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/mercadise/orders
     * Listar pedidos de Mercadise del local actual
     */
    public function getOrders(Request $request)
    {
        try {
            $app = CurrentApp::App();
            $appId = $app->Id ?? $app->id;

            $query = DB::connection('easyerp_master')
                ->table('merchise_pedidos')
                ->where('app_id', $appId);

            // Filtros opcionales
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('fecha_desde')) {
                $query->where('created_at', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->where('created_at', '<=', $request->fecha_hasta);
            }

            $pedidos = $query->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();

            // Decodificar items JSON
            $pedidos = $pedidos->map(function($pedido) {
                $pedido->items = json_decode($pedido->items, true);
                return $pedido;
            });

            return response()->json([
                'success' => true,
                'pedidos' => $pedidos,
                'total' => $pedidos->count(),
                'local' => [
                    'id' => $appId,
                    'nombre' => $app->Name ?? $app->name
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('Error al listar pedidos Mercadise', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al listar pedidos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
