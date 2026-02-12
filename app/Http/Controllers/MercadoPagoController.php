<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\CurrentApp;

class MercadoPagoController extends Controller
{
    private $mercadoPagoUrl = 'https://fagottoerp.cl/mercadopago';
    
    /**
     * Obtener configuración de MercadoPago
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfig(Request $request)
    {
        try {
            $appId = CurrentApp::App()->id;
            
            // Solo Las Condes (app_id 116) tiene MercadoPago configurado
            if ($appId != 116) {
                return response()->json([
                    'ok' => false,
                    'message' => 'MercadoPago no está configurado para este negocio'
                ], 403);
            }
            
            return response()->json([
                'ok' => true,
                'data' => [
                    'enabled' => true,
                    'app_id' => $appId,
                    'terminal' => 'NEWLAND_N950__N950NCC302980807', // Terminal 2 del .env
                    'base_url' => $this->mercadoPagoUrl
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo configuración de MercadoPago: ' . $e->getMessage());
            
            return response()->json([
                'ok' => false,
                'message' => 'Error al obtener la configuración: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Crear pago en MercadoPago Point
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPayment(Request $request)
    {
        try {
            $appId = CurrentApp::App()->id;
            
            // Validar que sea Las Condes
            if ($appId != 116) {
                return response()->json([
                    'ok' => false,
                    'message' => 'MercadoPago no está configurado para este negocio'
                ], 403);
            }
            
            // Validar datos requeridos
            $request->validate([
                'amount' => 'required|numeric|min:100',
                'description' => 'required|string',
                'external_reference' => 'required|string',
                'products' => 'required|array',
                'payment_type' => 'required|string|in:credit,debit'
            ]);
            
            $amount = (int) $request->input('amount');
            $description = $request->input('description');
            $externalReference = $request->input('external_reference');
            $products = $request->input('products');
            $paymentType = $request->input('payment_type'); // 'credit' o 'debit'
            
            Log::info('💳 Creando pago en MercadoPago', [
                'app_id' => $appId,
                'amount' => $amount,
                'description' => $description,
                'external_reference' => $externalReference,
                'payment_type' => $paymentType
            ]);
            
            // Llamar al archivo API PHP de MercadoPago para enviar el pago
            $url = $this->mercadoPagoUrl . '/api-enviar-pago.php';
            
            $postData = [
                'monto' => $amount,
                'descripcion' => $description,
                'referencia' => $externalReference,
                'productos' => json_encode($products),
                'app_id' => $appId,
                'payment_type' => $paymentType // 'credit' o 'debit'
            ];
            
            // Enviar como JSON
            $jsonPayload = json_encode($postData);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonPayload)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                Log::error('Error en curl al enviar pago: ' . $curlError);
                throw new \Exception('Error de conexión con MercadoPago: ' . $curlError);
            }
            
            if ($httpCode !== 200) {
                Log::error('Error HTTP al enviar pago: ' . $httpCode);
                Log::error('Response: ' . $response);
                throw new \Exception('Error al crear el pago en MercadoPago (HTTP ' . $httpCode . ')');
            }
            
            // Parsear respuesta
            $responseData = json_decode($response, true);
            
            if (!$responseData) {
                Log::error('Respuesta inválida de MercadoPago: ' . $response);
                throw new \Exception('Respuesta inválida de MercadoPago');
            }
            
            // Verificar si hay error
            if (isset($responseData['error'])) {
                throw new \Exception($responseData['message'] ?? 'Error al crear el pago');
            }
            
            if (!isset($responseData['order_id'])) {
                Log::error('Respuesta sin order_id: ' . $response);
                throw new \Exception('Respuesta sin order_id');
            }
            
            Log::info('✅ Pago enviado a MercadoPago exitosamente', [
                'order_id' => $responseData['order_id'],
                'status' => $responseData['status'] ?? 'unknown'
            ]);
            
            return response()->json([
                'ok' => true,
                'data' => [
                    'order_id' => $responseData['order_id'],
                    'status' => $responseData['status'] ?? 'pending',
                    'external_reference' => $externalReference,
                    'amount' => $amount
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error creando pago en MercadoPago: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'ok' => false,
                'message' => 'Error al crear el pago: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Consultar estado del pago
     * 
     * @param string $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentStatus($orderId)
    {
        try {
            $appId = CurrentApp::App()->id;
            
            // Validar que sea Las Condes
            if ($appId != 116) {
                return response()->json([
                    'ok' => false,
                    'message' => 'MercadoPago no está configurado para este negocio'
                ], 403);
            }
            
            Log::info('🔍 Consultando estado del pago', [
                'order_id' => $orderId,
                'app_id' => $appId
            ]);
            
            // Llamar al archivo API PHP de consulta de estado
            $url = $this->mercadoPagoUrl . '/api-consultar-estado.php?order_id=' . urlencode($orderId);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                Log::error('Error en curl al consultar estado: ' . $curlError);
                throw new \Exception('Error de conexión con MercadoPago: ' . $curlError);
            }
            
            if ($httpCode !== 200) {
                Log::error('Error HTTP al consultar estado: ' . $httpCode);
                throw new \Exception('Error al consultar el estado del pago (HTTP ' . $httpCode . ')');
            }
            
            // Parsear respuesta
            $responseData = json_decode($response, true);
            
            if (!$responseData) {
                Log::error('Respuesta inválida al consultar estado: ' . $response);
                throw new \Exception('Respuesta inválida de MercadoPago');
            }
            
            // Verificar si hay error
            if (isset($responseData['error'])) {
                throw new \Exception($responseData['message'] ?? 'Error al consultar estado');
            }
            
            if (!isset($responseData['status'])) {
                Log::error('Respuesta sin status: ' . $response);
                throw new \Exception('Respuesta sin estado del pago');
            }
            
            $status = $responseData['status'];
            
            Log::info('📡 Estado del pago recibido', [
                'order_id' => $orderId,
                'status' => $status
            ]);
            
            return response()->json([
                'ok' => true,
                'data' => [
                    'order_id' => $orderId,
                    'status' => $status,
                    'payment_data' => $responseData['payment_data'] ?? null
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error consultando estado del pago: ' . $e->getMessage());
            
            return response()->json([
                'ok' => false,
                'message' => 'Error al consultar el estado: ' . $e->getMessage()
            ], 500);
        }
    }
}
