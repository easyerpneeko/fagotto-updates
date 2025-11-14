<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UberEatsController extends Controller
{
    private $baseUrl = 'https://api.uber.com/v1/eats';
    private $accessToken;
    
    public function __construct()
    {
        // Token de acceso de Uber Eats (configurar en .env)
        $this->accessToken = env('UBER_EATS_ACCESS_TOKEN');
    }
    
    /**
     * Helper method to make HTTP requests with Guzzle
     */
    private function makeHttpRequest($method, $url, $data = [])
    {
        try {
            $client = new Client();
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json'
                ]
            ];
            
            if (!empty($data)) {
                $options['json'] = $data;
            }
            
            $response = $client->request($method, $url, $options);
            
            return [
                'success' => true,
                'status' => $response->getStatusCode(),
                'data' => json_decode($response->getBody(), true)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 500
            ];
        }
    }
    
    /**
     * Verificar estado de conexión con Uber Eats
     */
    public function getConnectionStatus()
    {
        try {
            if (!$this->accessToken) {
                return response()->json([
                    'connected' => false,
                    'status' => 'no_token',
                    'message' => 'Access token not configured. Please complete OAuth flow.'
                ]);
            }
            
            $result = $this->makeHttpRequest('GET', $this->baseUrl . '/stores');
                
            return response()->json([
                'connected' => $result['success'] && $result['status'] === 200,
                'status' => $result['status'] ?? 'error',
                'data' => $result['data'] ?? null,
                'message' => $result['success'] ? 'Connected to Uber Eats' : 'Failed to connect: ' . ($result['error'] ?? 'Unknown error')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Uber Eats connection check failed: ' . $e->getMessage());
            return response()->json([
                'connected' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener pedidos pendientes
     */
    public function getPendingOrders()
    {
        try {
            $result = $this->makeHttpRequest('GET', $this->baseUrl . '/orders?status=pending');
            
            if ($result['success']) {
                return response()->json($result['data']);
            }
            
            return response()->json(['error' => $result['error']], 400);
            
        } catch (\Exception $e) {
            Log::error('Error getting pending orders: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
    
    /**
     * Obtener pedidos activos
     */
    public function getActiveOrders()
    {
        try {
            $result = $this->makeHttpRequest('GET', $this->baseUrl . '/orders?status=accepted');
            
            if ($result['success']) {
                return response()->json($result['data']);
            }
            
            return response()->json(['error' => $result['error']], 400);
            
        } catch (\Exception $e) {
            Log::error('Error getting active orders: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
    
    /**
     * Aceptar un pedido
     */
    public function acceptOrder($orderId, Request $request)
    {
        try {
            $result = $this->makeHttpRequest('POST', $this->baseUrl . "/orders/{$orderId}/accept_pos_order", [
                'reason' => $request->get('reason', '')
            ]);
            
            if ($result['success']) {
                Log::info("Order {$orderId} accepted");
                return response()->json(['success' => true, 'message' => 'Order accepted']);
            }
            
            return response()->json(['error' => $result['error']], 400);
            
        } catch (\Exception $e) {
            Log::error("Error accepting order {$orderId}: " . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
    
    /**
     * Rechazar un pedido
     */
    public function rejectOrder($orderId, Request $request)
    {
        try {
            $result = $this->makeHttpRequest('POST', $this->baseUrl . "/orders/{$orderId}/deny_pos_order", [
                'reason_code' => $request->get('reason_code', 'unavailable'),
                'reason' => $request->get('reason', 'Store temporarily unavailable')
            ]);
            
            if ($result['success']) {
                Log::info("Order {$orderId} rejected");
                return response()->json(['success' => true, 'message' => 'Order rejected']);
            }
            
            return response()->json(['error' => $result['error']], 400);
            
        } catch (\Exception $e) {
            Log::error("Error rejecting order {$orderId}: " . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
    
    /**
     * Actualizar estado de un pedido
     */
    public function updateOrderStatus($orderId, Request $request)
    {
        try {
            $status = $request->get('status');
            $result = $this->makeHttpRequest('POST', $this->baseUrl . "/orders/{$orderId}/status", [
                'status' => $status
            ]);
            
            if ($result['success']) {
                Log::info("Order {$orderId} status updated to {$status}");
                return response()->json(['success' => true, 'message' => 'Order status updated']);
            }
            
            return response()->json(['error' => $result['error']], 400);
            
        } catch (\Exception $e) {
            Log::error("Error updating order {$orderId} status: " . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
    
    /**
     * Cancelar un pedido
     */
    public function cancelOrder($orderId, Request $request)
    {
        try {
            $result = $this->makeHttpRequest('POST', $this->baseUrl . "/orders/{$orderId}/cancel", [
                'reason_code' => $request->get('reason_code', 'store_closed'),
                'reason' => $request->get('reason', 'Store closed')
            ]);
            
            if ($result['success']) {
                Log::info("Order {$orderId} cancelled");
                return response()->json(['success' => true, 'message' => 'Order cancelled']);
            }
            
            return response()->json(['error' => $result['error']], 400);
            
        } catch (\Exception $e) {
            Log::error("Error cancelling order {$orderId}: " . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
    
    /**
     * Obtener configuración de la tienda
     */
    public function getStoreConfig()
    {
        try {
            $result = $this->makeHttpRequest('GET', $this->baseUrl . '/stores');
            
            if ($result['success']) {
                return response()->json($result['data']);
            }
            
            return response()->json(['error' => $result['error']], 400);
            
        } catch (\Exception $e) {
            Log::error('Error getting store config: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
    
    /**
     * Webhook para recibir notificaciones de Uber Eats
     */
    public function webhook(Request $request)
    {
        try {
            $payload = $request->all();
            Log::info('Uber Eats webhook received', $payload);
            
            // Aquí puedes procesar diferentes tipos de eventos
            $eventType = $payload['event_type'] ?? null;
            
            switch ($eventType) {
                case 'orders.notification':
                    // Nuevo pedido recibido
                    $this->handleNewOrder($payload);
                    break;
                    
                case 'orders.status_changed':
                    // Estado del pedido cambió
                    $this->handleOrderStatusChange($payload);
                    break;
                    
                default:
                    Log::info('Unknown webhook event type: ' . $eventType);
            }
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }
    
    /**
     * Manejar nuevo pedido
     */
    private function handleNewOrder($payload)
    {
        // Aquí puedes integrar con tu sistema de pedidos
        Log::info('New Uber Eats order received', $payload);
        
        // Ejemplo: guardar en base de datos, enviar notificación, etc.
    }
    
    /**
     * Manejar cambio de estado de pedido
     */
    private function handleOrderStatusChange($payload)
    {
        Log::info('Uber Eats order status changed', $payload);
        
        // Aquí puedes actualizar el estado en tu sistema
    }
    
    /**
     * Manejar callback de OAuth
     */
    public function handleOAuthCallback(Request $request)
    {
        try {
            $code = $request->get('code');
            $state = $request->get('state');
            
            if (!$code) {
                return response()->json(['error' => 'Authorization code not provided'], 400);
            }
            
            // Intercambiar el código por un access token usando Guzzle
            $client = new Client();
            $response = $client->post('https://login.uber.com/oauth/v2/token', [
                'json' => [
                    'client_id' => env('UBER_EATS_CLIENT_ID'),
                    'client_secret' => env('UBER_EATS_CLIENT_SECRET'),
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => 'https://posfagotto.cl/api/uber-eats/auth/callback'
                ]
            ]);
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                
                // Log para debugging
                Log::info('Uber OAuth Success', $data);
            
                return response()->json([
                    'success' => true,
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? null,
                    'expires_in' => $data['expires_in'],
                    'token_type' => $data['token_type'],
                    'message' => 'Guarda este access_token en tu archivo .env como UBER_EATS_ACCESS_TOKEN'
                ]);
            } else {
                $errorData = json_decode($response->getBody(), true);
                Log::error('Uber OAuth Error', $errorData);
                return response()->json(['error' => 'Failed to exchange code for token'], 400);
            }
                
        } catch (\Exception $e) {
            Log::error('OAuth callback error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error during OAuth'], 500);
        }
    }
}
