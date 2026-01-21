<?php

namespace App\Http\Controllers\Controllers_local;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

class WhatsAppController extends Controller
{
    /**
     * Enviar mensaje de WhatsApp mediante Twilio
     * POST /api/local/send-whatsapp
     */
    public function sendWhatsApp(Request $request)
    {
        try {
            // Verificar que Twilio esté instalado
            if (!class_exists('\\Twilio\\Rest\\Client')) {
                \Log::error('❌ Twilio SDK no está instalado. Ejecuta: composer require twilio/sdk');
                return response()->json([
                    'success' => false,
                    'message' => 'Twilio SDK no está instalado en el servidor'
                ], 500);
            }

            $validatedData = $request->validate([
                'to' => 'required|string',
                'message' => 'required|string',
                'pedido_id' => 'nullable|integer'
            ]);

            \Log::info('📱 Iniciando envío de WhatsApp', [
                'to' => $validatedData['to'],
                'pedido_id' => $validatedData['pedido_id'] ?? null
            ]);

            // Credenciales de Twilio
            $sid = 'AC6bc40244fd40581717f54f24b32c7f74';
            $token = '5d704d4eba111718bc4c501a858d948b';
            $twilioWhatsApp = 'whatsapp:+18582953672';

            // Limpiar número de destino (quitar espacios, guiones, etc)
            $toNumber = preg_replace('/[^0-9+]/', '', $validatedData['to']);
            
            // Si no empieza con +, agregar +56 (Chile)
            if (substr($toNumber, 0, 1) !== '+') {
                $toNumber = '+56' . $toNumber;
            }
            
            // Formato WhatsApp
            $toWhatsApp = 'whatsapp:' . $toNumber;

            // Inicializar cliente Twilio
            $twilio = new Client($sid, $token);

            // Enviar mensaje
            $message = $twilio->messages->create(
                $toWhatsApp,
                [
                    'from' => $twilioWhatsApp,
                    'body' => $validatedData['message']
                ]
            );

            \Log::info('✅ WhatsApp enviado via Twilio', [
                'to' => $toWhatsApp,
                'message_sid' => $message->sid,
                'pedido_id' => $validatedData['pedido_id'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp enviado correctamente',
                'data' => [
                    'message_sid' => $message->sid,
                    'status' => $message->status,
                    'to' => $toNumber
                ]
            ], 200);

        } catch (\Twilio\Exceptions\RestException $e) {
            \Log::error('❌ Error Twilio:', [
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error de Twilio: ' . $e->getMessage(),
                'code' => $e->getCode()
            ], 500);

        } catch (\Exception $e) {
            \Log::error('❌ Error enviando WhatsApp:', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar WhatsApp: ' . $e->getMessage()
            ], 500);
        }
    }
}
