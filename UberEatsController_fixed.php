<?php

namespace App\Http\Controllers\Controllers_local;

use App\Services\UberEatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class UberEatsController extends Controller
{
    private $uberEatsService;

    public function __construct()
    {
        $this->uberEatsService = new UberEatsService();
    }

    /**
     * Test connection to Uber Eats API
     */
    public function getConnectionStatus()
    {
        try {
            $result = $this->uberEatsService->testConnection();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle OAuth callback from Uber Eats
     */
    public function handleOAuthCallback(Request $request)
    {
        try {
            $code = $request->get('code');
            $state = $request->get('state');
            
            if (!$code) {
                return response()->json(['error' => 'Authorization code not provided'], 400);
            }

            // Exchange code for access token
            $tokenData = $this->uberEatsService->exchangeCodeForToken($code);
            
            if ($tokenData) {
                // Store token securely
                Cache::put('uber_eats_access_token', $tokenData['access_token'], now()->addSeconds($tokenData['expires_in']));
                Cache::put('uber_eats_refresh_token', $tokenData['refresh_token'], now()->addDays(30));
                
                Log::info('Uber Eats OAuth successful', ['store_id' => $tokenData['store_id'] ?? 'unknown']);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully connected to Uber Eats',
                    'store_id' => $tokenData['store_id'] ?? null
                ]);
            } else {
                return response()->json(['error' => 'Failed to exchange code for token'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Uber Eats OAuth error: ' . $e->getMessage());
            return response()->json(['error' => 'OAuth failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get pending orders from Uber Eats
     */
    public function getPendingOrders()
    {
        try {
            $orders = $this->uberEatsService->getPendingOrders();
            return response()->json($orders);
        } catch (\Exception $e) {
            Log::error('Error getting pending orders: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get active orders from Uber Eats
     */
    public function getActiveOrders()
    {
        try {
            $orders = $this->uberEatsService->getActiveOrders();
            return response()->json($orders);
        } catch (\Exception $e) {
            Log::error('Error getting active orders: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Accept an order
     */
    public function acceptOrder(Request $request, $orderId)
    {
        try {
            $estimatedTime = $request->get('estimated_time', 15); // Default 15 minutes
            $result = $this->uberEatsService->acceptOrder($orderId, $estimatedTime);
            
            if ($result['success']) {
                Log::info('Order accepted', ['order_id' => $orderId, 'estimated_time' => $estimatedTime]);
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }
        } catch (\Exception $e) {
            Log::error('Error accepting order: ' . $e->getMessage(), ['order_id' => $orderId]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reject an order
     */
    public function rejectOrder(Request $request, $orderId)
    {
        try {
            $reason = $request->get('reason', 'out_of_item'); // Default reason
            $result = $this->uberEatsService->denyOrder($orderId, $reason);
            
            if ($result['success']) {
                Log::info('Order rejected', ['order_id' => $orderId, 'reason' => $reason]);
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }
        } catch (\Exception $e) {
            Log::error('Error rejecting order: ' . $e->getMessage(), ['order_id' => $orderId]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, $orderId)
    {
        try {
            $status = $request->get('status');
            $estimatedTime = $request->get('estimated_time');
            
            $result = $this->uberEatsService->updateOrderStatus($orderId, $status, $estimatedTime);
            
            if ($result['success']) {
                Log::info('Order status updated', ['order_id' => $orderId, 'status' => $status]);
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }
        } catch (\Exception $e) {
            Log::error('Error updating order status: ' . $e->getMessage(), ['order_id' => $orderId]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Request $request, $orderId)
    {
        try {
            $reason = $request->get('reason', 'restaurant_too_busy');
            $result = $this->uberEatsService->cancelOrder($orderId, $reason);
            
            if ($result['success']) {
                Log::info('Order cancelled', ['order_id' => $orderId, 'reason' => $reason]);
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }
        } catch (\Exception $e) {
            Log::error('Error cancelling order: ' . $e->getMessage(), ['order_id' => $orderId]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle webhook notifications from Uber Eats
     */
    public function webhook(Request $request)
    {
        try {
            $payload = $request->all();
            Log::info('Uber Eats webhook received', $payload);
            
            // Verify webhook signature if needed
            // $signature = $request->header('X-Uber-Signature');
            
            $eventType = $payload['event_type'] ?? 'unknown';
            
            switch ($eventType) {
                case 'orders.notification':
                    $this->handleOrderNotification($payload);
                    break;
                case 'orders.status_changed':
                    $this->handleOrderStatusChange($payload);
                    break;
                default:
                    Log::info('Unhandled webhook event type: ' . $eventType);
                    break;
            }
            
            return response()->json(['status' => 'received'], 200);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Get store configuration
     */
    public function getStoreConfig()
    {
        try {
            $config = $this->uberEatsService->getStoreConfig();
            return response()->json($config);
        } catch (\Exception $e) {
            Log::error('Error getting store config: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle order notification webhook
     */
    private function handleOrderNotification($payload)
    {
        $orderId = $payload['order_id'] ?? null;
        if ($orderId) {
            Log::info('New order notification', ['order_id' => $orderId]);
            // Aquí puedes agregar lógica para notificar a la interfaz de usuario
            // Por ejemplo, enviar a través de WebSocket o guardar en base de datos
        }
    }

    /**
     * Handle order status change webhook
     */
    private function handleOrderStatusChange($payload)
    {
        $orderId = $payload['order_id'] ?? null;
        $status = $payload['status'] ?? null;
        
        if ($orderId && $status) {
            Log::info('Order status changed', ['order_id' => $orderId, 'status' => $status]);
            // Aquí puedes agregar lógica para actualizar el estado en tu sistema
        }
    }
}
