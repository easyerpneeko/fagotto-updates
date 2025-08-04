<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UberEatsController extends Controller
{
    private string $tokenUrl = 'https://login.uber.com/oauth/v2/token';
    private string $orderUrl = 'https://api.uber.com/v1/delivery';

    /**
     * Obtener el access token para Uber Eats API
     */
    private function getAccessToken(): ?string
    {
        $cacheKey = 'uberEats:accessToken';
        $accessToken = Cache::get($cacheKey);

        if ($accessToken) {
            return $accessToken;
        }

        $response = Http::asForm()->post($this->tokenUrl, [
            'client_id' => env('UBER_EATS_CLIENT_ID'),
            'client_secret' => env('UBER_EATS_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
            'scope' => env('UBER_EATS_SCOPE', 'eats.store eats.store.status.write eats.order eats.store.orders.read'),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $accessToken = $data['access_token'];
            $expiresIn = $data['expires_in'] ?? 3600;

            // Guardar en cache por menos tiempo del que expira
            Cache::put($cacheKey, $accessToken, $expiresIn - 300);

            return $accessToken;
        }

        Log::error('Error obteniendo access token de Uber Eats', [
            'response' => $response->body(),
            'status' => $response->status()
        ]);

        return null;
    }

    /**
     * Hacer request autenticado a la API de Uber Eats
     */
    private function authenticatedRequest()
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            throw new \Exception('No se pudo obtener access token de Uber Eats');
        }

        return Http::baseUrl($this->orderUrl)
            ->asJson()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ]);
    }

    /**
     * Obtener órdenes de una tienda específica
     */
    public function getOrders(Request $request)
    {
        try {
            $storeId = $request->get('store_id', env('UBER_EATS_STORE_ID'));
            
            if (!$storeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Store ID es requerido'
                ], 400);
            }

            $response = $this->authenticatedRequest()
                ->get("/store/{$storeId}/orders?expand=deliveries,carts,payment");

            if ($response->successful()) {
                $orders = $response->json();
                
                // Formatear las órdenes para el frontend
                $formattedOrders = $this->formatOrders($orders);

                return response()->json([
                    'success' => true,
                    'data' => $formattedOrders,
                    'total' => count($formattedOrders)
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener órdenes de Uber Eats',
                'error' => $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('Error en UberEatsController::getOrders', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una orden específica
     */
    public function getOrder(Request $request, $orderId)
    {
        try {
            $response = $this->authenticatedRequest()
                ->get("/order/{$orderId}?expand=deliveries,carts,payment");

            if ($response->successful()) {
                $order = $response->json();
                
                return response()->json([
                    'success' => true,
                    'data' => $this->formatSingleOrder($order)
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Orden no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error en UberEatsController::getOrder', [
                'orderId' => $orderId,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la orden'
            ], 500);
        }
    }

    /**
     * Aceptar una orden
     */
    public function acceptOrder(Request $request, $orderId)
    {
        try {
            $data = [];
            
            if ($request->has('ready_for_pickup_time')) {
                $data['ready_for_pickup_time'] = $request->get('ready_for_pickup_time');
            }
            
            if ($request->has('external_id')) {
                $data['external_id'] = $request->get('external_id');
            }

            $response = $this->authenticatedRequest()
                ->patch("/order/{$orderId}/accept_pos_order", $data);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Orden aceptada exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al aceptar la orden'
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('Error en UberEatsController::acceptOrder', [
                'orderId' => $orderId,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al aceptar la orden'
            ], 500);
        }
    }

    /**
     * Formatear las órdenes para el frontend
     */
    private function formatOrders($ordersData)
    {
        if (!isset($ordersData['orders'])) {
            return [];
        }

        return array_map(function ($order) {
            return $this->formatSingleOrder($order);
        }, $ordersData['orders']);
    }

    /**
     * Formatear una orden individual
     */
    private function formatSingleOrder($order)
    {
        $items = [];
        
        if (isset($order['cart']['items'])) {
            foreach ($order['cart']['items'] as $item) {
                $items[] = [
                    'id' => $item['id'] ?? null,
                    'name' => $item['title'] ?? 'Sin nombre',
                    'quantity' => $item['quantity'] ?? 1,
                    'price' => ($item['price'] ?? 0) / 100, // Convertir centavos a pesos
                    'special_instructions' => $item['special_instructions'] ?? null,
                ];
            }
        }

        return [
            'id' => $order['id'] ?? null,
            'display_id' => $order['display_id'] ?? null,
            'status' => $order['current_state'] ?? 'unknown',
            'created_at' => $order['placed_at'] ?? null,
            'customer_name' => $order['eater']['first_name'] ?? 'Cliente Uber',
            'customer_phone' => $order['eater']['phone_number'] ?? null,
            'total_amount' => ($order['payment']['total_amount'] ?? 0) / 100,
            'items' => $items,
            'delivery_info' => [
                'address' => $order['delivery']['location']['address'] ?? null,
                'notes' => $order['delivery']['notes'] ?? null,
            ],
            'raw_data' => $order // Para debugging
        ];
    }

    /**
     * Webhook para recibir notificaciones de Uber Eats
     */
    public function webhook(Request $request)
    {
        try {
            Log::info('Webhook de Uber Eats recibido', [
                'headers' => $request->headers->all(),
                'body' => $request->all()
            ]);

            $eventType = $request->header('X-Uber-Event-Type');
            $data = $request->all();

            // Procesar diferentes tipos de eventos
            switch ($eventType) {
                case 'orders.notification':
                    $this->handleOrderNotification($data);
                    break;
                
                case 'orders.status_changed':
                    $this->handleOrderStatusChanged($data);
                    break;
                
                default:
                    Log::info('Evento de Uber Eats no manejado', ['event_type' => $eventType]);
            }

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('Error en webhook de Uber Eats', [
                'message' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Manejar notificación de nueva orden
     */
    private function handleOrderNotification($data)
    {
        Log::info('Nueva orden de Uber Eats recibida', $data);
        
        // Aquí puedes agregar lógica para:
        // - Notificar al frontend
        // - Crear orden automáticamente en tu sistema
        // - Enviar notificaciones push
        // etc.
    }

    /**
     * Manejar cambio de estado de orden
     */
    private function handleOrderStatusChanged($data)
    {
        Log::info('Estado de orden de Uber Eats cambió', $data);
        
        // Aquí puedes agregar lógica para actualizar
        // el estado en tu sistema local
    }

    /**
     * Configurar integración de tienda
     */
    public function activateIntegration(Request $request)
    {
        try {
            $storeId = $request->get('store_id', env('UBER_EATS_STORE_ID'));
            $isOrderManager = $request->get('is_order_manager', true);

            $response = $this->authenticatedRequest()
                ->post("/eats/stores/{$storeId}/pos-data", [
                    'pos_integration_enabled' => true,
                    'is_order_manager' => $isOrderManager
                ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Integración activada exitosamente'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al activar integración',
                'error' => $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('Error en activateIntegration', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al activar integración'
            ], 500);
        }
    }
}
