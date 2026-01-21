<?php

namespace App\Http\Controllers\Controllers_local;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class FlowController extends Controller
{
    // Credenciales Flow
    private $apiKey = '77F1B4FF-B86C-4960-8206-6B51B5LD813D';
    private $secretKey = '3cf5929dcfc742bfceb1c1100ad2a0fa75d0d1e5';
    private $apiUrl = 'https://www.flow.cl/api'; // Producción
    // Para sandbox: 'https://sandbox.flow.cl/api'

    /**
     * Firmar parámetros con secretKey (requerido por Flow)
     */
    private function sign($params)
    {
        // Ordenar parámetros alfabéticamente
        ksort($params);
        
        // Concatenar en formato key=value
        $string = '';
        foreach ($params as $key => $value) {
            $string .= $key . $value;
        }
        
        // Firmar con HMAC SHA256
        return hash_hmac('sha256', $string, $this->secretKey);
    }

    /**
     * Crear una orden de pago en Flow
     * POST /api/local/flow/create-payment
     */
    public function createPayment(Request $request)
    {
        try {
            // Validación
            $validatedData = $request->validate([
                'amount' => 'required|numeric|min:100',
                'subject' => 'required|string',
                'email' => 'required|email',
                'pedido_id' => 'required|integer',
                'payment_id' => 'required|integer'
            ]);

            \Log::info('💳 Creando pago Flow', [
                'amount' => $validatedData['amount'],
                'subject' => $validatedData['subject'],
                'pedido_id' => $validatedData['pedido_id']
            ]);

            // Preparar parámetros del pago
            $params = [
                'apiKey' => $this->apiKey,
                'commerceOrder' => 'ORDER-' . $validatedData['pedido_id'] . '-' . $validatedData['payment_id'],
                'subject' => $validatedData['subject'],
                'currency' => 'CLP',
                'amount' => (int) $validatedData['amount'],
                'email' => $validatedData['email'],
                'urlConfirmation' => url('/api/local/flow/confirm'),
                'urlReturn' => url('/api/local/flow/return')
            ];

            // Agregar firma
            $params['s'] = $this->sign($params);

            // Hacer petición a Flow API usando Guzzle (Laravel 5.x)
            $client = new Client();
            $response = $client->post($this->apiUrl . '/payment/create', [
                'form_params' => $params,
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();

            if ($statusCode !== 200) {
                \Log::error('❌ Error API Flow', [
                    'status' => $statusCode,
                    'body' => $body
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear pago en Flow: ' . $body
                ], 500);
            }

            $flowResponse = json_decode($body, true);

            \Log::info('✅ Pago Flow creado', [
                'flow_token' => $flowResponse['token'] ?? null,
                'flow_url' => $flowResponse['url'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pago Flow creado correctamente',
                'data' => [
                    'token' => $flowResponse['token'] ?? null,
                    'url' => $flowResponse['url'] ?? null,
                    'flow_order' => $flowResponse['flowOrder'] ?? null
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('❌ Error creando pago Flow:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear pago Flow: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook de confirmación de pago de Flow
     * POST /api/local/flow/confirm
     */
    public function confirmPayment(Request $request)
    {
        try {
            \Log::info('🔔 Webhook Flow confirm recibido', $request->all());

            $token = $request->input('token');

            if (!$token) {
                \Log::error('❌ Token no recibido en webhook Flow');
                return response()->json(['error' => 'Token no recibido'], 400);
            }

            // Obtener el estado del pago
            $params = [
                'apiKey' => $this->apiKey,
                'token' => $token
            ];
            
            $params['s'] = $this->sign($params);

            // Usar Guzzle para obtener el estado del pago
            $client = new Client();
            $response = $client->get($this->apiUrl . '/payment/getStatus', [
                'query' => $params,
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            
            if ($statusCode !== 200) {
                \Log::error('❌ Error obteniendo estado Flow', [
                    'status' => $statusCode,
                    'body' => (string) $response->getBody()
                ]);
                return response()->json(['error' => 'Error al obtener estado'], 500);
            }

            $paymentData = json_decode((string) $response->getBody(), true);

            \Log::info('📥 Estado del pago Flow:', $paymentData);

            // Extraer información del pago
            $status = $paymentData['status'] ?? null; // 1=Pendiente, 2=Pagado, 3=Rechazado, 4=Anulado
            $commerceOrder = $paymentData['commerceOrder'] ?? null;
            $amount = $paymentData['amount'] ?? 0;
            $paymentDate = $paymentData['paymentDate'] ?? null;

            // Actualizar el pedido en la base de datos si está pagado
            $message = 'pendiente (flow)';
            if ($status == 2 && $commerceOrder) {
                // Extraer pedido_id del commerceOrder (formato: ORDER-123-456)
                preg_match('/ORDER-(\d+)-(\d+)/', $commerceOrder, $matches);
                $pedidoId = $matches[1] ?? null;
                $paymentId = $matches[2] ?? null;

                if ($pedidoId && $paymentId) {
                    // Actualizar el payment en la BD
                    \DB::table('payments')
                        ->where('id', $paymentId)
                        ->update([
                            'status' => 'pagado',
                            'payment_date' => $paymentDate ? date('Y-m-d H:i:s', strtotime($paymentDate)) : now(),
                            'payment_method' => 'flow',
                            'flow_token' => $token,
                            'flow_order' => $paymentData['flowOrder'] ?? null,
                            'updated_at' => now()
                        ]);

                    // Actualizar el request (pedido)
                    \DB::table('requests')
                        ->where('id', $pedidoId)
                        ->update([
                            'status_payment' => 'pagado',
                            'payment_status' => 'paid',
                            'updated_at' => now()
                        ]);

                    \Log::info('✅ Pago Flow confirmado y BD actualizada', [
                        'pedido_id' => $pedidoId,
                        'payment_id' => $paymentId,
                        'amount' => $amount
                    ]);
                    
                    $message = 'aprobado (flow)';
                }
            }

            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            \Log::error('❌ Error en webhook Flow confirm:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Página de retorno después del pago
     * GET /api/local/flow/return
     */
    public function returnPayment(Request $request)
    {
        try {
            $token = $request->input('token');

            \Log::info('🔙 Usuario retornó de Flow', ['token' => $token]);

            if (!$token) {
                return '<html><body style="font-family: Arial; text-align: center; padding: 50px;">
                    <h1 style="color: #dc3545;">❌ Error</h1>
                    <p>Token no recibido.</p>
                    <p><a href="/" style="color: #007bff;">Volver al inicio</a></p>
                </body></html>';
            }

            // Obtener el estado del pago
            $params = [
                'apiKey' => $this->apiKey,
                'token' => $token
            ];
            
            $params['s'] = $this->sign($params);

            // Usar Guzzle para obtener el estado del pago
            $client = new Client();
            $response = $client->get($this->apiUrl . '/payment/getStatus', [
                'query' => $params,
                'http_errors' => false
            ]);

            $statusCode = $response->getStatusCode();
            
            if ($statusCode !== 200) {
                return '<html><body style="font-family: Arial; text-align: center; padding: 50px;">
                    <h1 style="color: #dc3545;">❌ Error</h1>
                    <p>Error al verificar el pago.</p>
                    <p><a href="/" style="color: #007bff;">Volver al inicio</a></p>
                </body></html>';
            }

            $paymentData = json_decode((string) $response->getBody(), true);
            $status = $paymentData['status'] ?? null;

            // Redirigir según el estado
            if ($status == 2) {
                // Pago exitoso - Mostrar página de éxito
                return '<html><body style="font-family: Arial; text-align: center; padding: 50px;">
                    <h1 style="color: #28a745;">✅ ¡Pago Exitoso!</h1>
                    <p>Tu pago ha sido procesado correctamente.</p>
                    <p>Número de orden: ' . ($paymentData['commerceOrder'] ?? 'N/A') . '</p>
                    <p>Monto: $' . number_format($paymentData['amount'] ?? 0, 0, ',', '.') . ' CLP</p>
                    <p><a href="https://www.fagotto.cl" style="color: #007bff;">Volver al inicio</a></p>
                </body></html>';
            } else {
                // Pago rechazado o pendiente
                return '<html><body style="font-family: Arial; text-align: center; padding: 50px;">
                    <h1 style="color: #dc3545;">❌ Pago No Completado</h1>
                    <p>El pago no pudo ser procesado.</p>
                    <p><a href="/" style="color: #007bff;">Volver al inicio</a></p>
                </body></html>';
            }

        } catch (\Exception $e) {
            \Log::error('❌ Error en return Flow:', [
                'error' => $e->getMessage()
            ]);

            return '<html><body style="font-family: Arial; text-align: center; padding: 50px;">
                <h1 style="color: #dc3545;">❌ Error</h1>
                <p>Ocurrió un error al verificar el pago.</p>
                <p><a href="/" style="color: #007bff;">Volver al inicio</a></p>
            </body></html>';
        }
    }
}
