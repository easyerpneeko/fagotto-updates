<?php

namespace App\Http\Controllers\Controllers_local;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UberEatsController extends Controller
{
    // Store UUID de prueba para Fagotto Terminal TurBus
    private $testStoreUuid = 'e244a540-4071-56ac-875d-c0fc02aed530';
    
    public function getConfig(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'store_uuid' => '',
                'client_id' => '',
                'client_secret' => '',
                'configured' => false,
                'test_store_uuid' => $this->testStoreUuid // Para mostrar el UUID de prueba
            ]
        ]);
    }
    
    public function saveConfig(Request $request)
    {
        // Solo guardamos la configuración temporalmente para la prueba
        $storeUuid = $request->input('store_uuid');
        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');
        
        return response()->json([
            'success' => true,
            'message' => 'Configuración guardada temporalmente',
            'data' => [
                'store_uuid' => $storeUuid,
                'configured' => !empty($storeUuid)
            ]
        ]);
    }
    
    public function testConnection(Request $request)
    {
        $storeUuid = $request->input('store_uuid');
        
        if (empty($storeUuid)) {
            return response()->json([
                'success' => false,
                'message' => 'Debes ingresar un Store UUID'
            ]);
        }
        
        // Simulamos una prueba de conexión
        if ($storeUuid === $this->testStoreUuid) {
            return response()->json([
                'success' => true,
                'message' => '¡Conexión exitosa! Store UUID válido de Fagotto Terminal TurBus',
                'data' => [
                    'store_name' => 'Fagotto - Terminal TurBus',
                    'store_uuid' => $storeUuid,
                    'status' => 'connected',
                    'test_mode' => true
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Store UUID no reconocido. Usa el UUID de prueba: ' . $this->testStoreUuid
            ]);
        }
    }
    
    public function getOrders(Request $request)
    {
        // Simulamos algunos pedidos de prueba
        $orders = [
            [
                'id' => 'order_123',
                'display_id' => 'UE-001',
                'status' => 'created',
                'customer_name' => 'Juan Pérez',
                'customer_phone' => null,
                'total_amount' => 15990,
                'items' => [
                    [
                        'name' => 'Pasta Bolognesa',
                        'quantity' => 1,
                        'price' => 8990
                    ],
                    [
                        'name' => 'Bebida Cola',
                        'quantity' => 2,
                        'price' => 3500
                    ]
                ],
                'created_at' => now()->subMinutes(5),
                'preparation_time' => 15
            ],
            [
                'id' => 'order_124',
                'display_id' => 'UE-002',
                'status' => 'accepted',
                'customer_name' => 'María González',
                'customer_phone' => null,
                'total_amount' => 22500,
                'items' => [
                    [
                        'name' => 'Pizza Margherita',
                        'quantity' => 1,
                        'price' => 12500
                    ],
                    [
                        'name' => 'Pasta Carbonara',
                        'quantity' => 1,
                        'price' => 10000
                    ]
                ],
                'created_at' => now()->subMinutes(15),
                'preparation_time' => 20
            ]
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Pedidos de prueba obtenidos',
            'data' => $orders,
            'count' => count($orders)
        ]);
    }
}
