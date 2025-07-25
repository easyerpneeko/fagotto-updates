<?php

namespace App\Http\Controllers;

use App\Services\UberEatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UberEatsController extends Controller
{
    private $uberEatsService;

    public function __construct()
    {
        $this->uberEatsService = new UberEatsService();
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        try {
            $result = $this->uberEatsService->testConnection();
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'timestamp' => now(),
                'token_preview' => $result['token_preview'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage(),
                'timestamp' => now()
            ], 500);
        }
    }

    /**
     * Get all orders for configured store
     */
    public function getOrders()
    {
        try {
            $storeId = env('UBER_EATS_STORE_ID');
            
            if (!$storeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Store ID not configured'
                ], 400);
            }

            $orders = $this->uberEatsService->getOrders($storeId);
            
            return response()->json([
                'success' => true,
                'data' => $orders,
                'store_id' => $storeId,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting Uber Eats orders: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific order details
     */
    public function getOrder($orderId)
    {
        try {
            $order = $this->uberEatsService->getOrder($orderId);
            
            return response()->json([
                'success' => true,
                'data' => $order,
                'order_id' => $orderId,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting Uber Eats order: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept an order
     */
    public function acceptOrder(Request $request, $orderId)
    {
        try {
            $pickupTime = $request->input('pickup_time');
            $externalId = $request->input('external_id');
            $acceptedBy = $request->input('accepted_by', 'Fagotto POS');

            $result = $this->uberEatsService->acceptOrder(
                $orderId,
                $pickupTime,
                $externalId,
                $acceptedBy
            );
            
            // Log the action
            Log::info('Uber Eats order accepted', [
                'order_id' => $orderId,
                'accepted_by' => $acceptedBy,
                'pickup_time' => $pickupTime
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Order accepted successfully',
                'order_id' => $orderId,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error accepting Uber Eats order: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deny an order
     */
    public function denyOrder(Request $request, $orderId)
    {
        try {
            $reasonInfo = $request->input('reason_info', 'Unable to fulfill order');
            $reasonType = $request->input('reason_type', 'RESTAURANT_TOO_BUSY');

            $result = $this->uberEatsService->denyOrder(
                $orderId,
                $reasonInfo,
                $reasonType
            );
            
            // Log the action
            Log::info('Uber Eats order denied', [
                'order_id' => $orderId,
                'reason' => $reasonInfo,
                'type' => $reasonType
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Order denied successfully',
                'order_id' => $orderId,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error denying Uber Eats order: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to deny order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark order as ready
     */
    public function markOrderReady($orderId)
    {
        try {
            $result = $this->uberEatsService->markOrderReady($orderId);
            
            // Log the action
            Log::info('Uber Eats order marked as ready', [
                'order_id' => $orderId
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Order marked as ready',
                'order_id' => $orderId,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking Uber Eats order as ready: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark order as ready: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order ready time
     */
    public function updateOrderReadyTime(Request $request, $orderId)
    {
        try {
            $readyTime = $request->input('ready_time');
            
            if (!$readyTime) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ready time is required'
                ], 400);
            }

            $result = $this->uberEatsService->updateOrderReadyTime($orderId, $readyTime);
            
            // Log the action
            Log::info('Uber Eats order ready time updated', [
                'order_id' => $orderId,
                'ready_time' => $readyTime
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Order ready time updated',
                'order_id' => $orderId,
                'ready_time' => $readyTime,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating Uber Eats order ready time: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ready time: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Uber Eats webhooks
     */
    public function handleWebhook(Request $request)
    {
        try {
            // Verify webhook signature (optional but recommended)
            $signature = $request->header('X-Uber-Signature');
            $payload = $request->getContent();
            
            // Log webhook received
            Log::info('Uber Eats webhook received', [
                'event_type' => $request->input('event_type'),
                'resource_id' => $request->input('meta.resource_id'),
                'store_id' => $request->input('meta.user_id'),
                'signature' => $signature
            ]);

            $eventType = $request->input('event_type');
            
            switch ($eventType) {
                case 'orders.create':
                    $this->handleNewOrder($request->all());
                    break;
                
                case 'orders.update':
                    $this->handleOrderUpdate($request->all());
                    break;
                
                case 'orders.state_changed':
                    $this->handleOrderStateChange($request->all());
                    break;
                
                default:
                    Log::info('Unhandled webhook event type: ' . $eventType);
            }
            
            return response()->json(['status' => 'ok'], 200);
        } catch (\Exception $e) {
            Log::error('Error handling Uber Eats webhook: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle new order webhook
     */
    private function handleNewOrder($data)
    {
        Log::info('New Uber Eats order received', $data);
        
        // Here you can add logic to:
        // - Store order in database
        // - Send notification to kitchen
        // - Update inventory
        // - Send push notification to staff
        
        // Example: Cache new order for real-time updates
        $orderId = $data['meta']['resource_id'] ?? null;
        if ($orderId) {
            Cache::put("uber_order_new_{$orderId}", $data, 300); // 5 minutes
        }
    }

    /**
     * Handle order update webhook
     */
    private function handleOrderUpdate($data)
    {
        Log::info('Uber Eats order updated', $data);
        
        // Handle order updates (status changes, modifications, etc.)
        $orderId = $data['meta']['resource_id'] ?? null;
        if ($orderId) {
            Cache::put("uber_order_update_{$orderId}", $data, 300); // 5 minutes
        }
    }

    /**
     * Handle order state change webhook
     */
    private function handleOrderStateChange($data)
    {
        Log::info('Uber Eats order state changed', $data);
        
        // Handle state changes (accepted, ready, picked up, delivered, etc.)
        $orderId = $data['meta']['resource_id'] ?? null;
        if ($orderId) {
            Cache::put("uber_order_state_{$orderId}", $data, 300); // 5 minutes
        }
    }

    /**
     * Get store menu
     */
    public function getMenu()
    {
        try {
            $storeId = env('UBER_EATS_STORE_ID');
            
            if (!$storeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Store ID not configured'
                ], 400);
            }

            $menu = $this->uberEatsService->getMenu($storeId);
            
            return response()->json([
                'success' => true,
                'data' => $menu,
                'store_id' => $storeId,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting Uber Eats menu: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get menu: ' . $e->getMessage()
            ], 500);
        }
    }
}
