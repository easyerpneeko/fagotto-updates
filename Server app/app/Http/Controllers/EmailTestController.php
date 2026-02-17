<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PHPMailerService;

class EmailTestController extends Controller
{
    /**
     * Enviar email de prueba
     * 
     * Ruta: GET /test-email-send
     */
    public function testSend()
    {
        $resultado = PHPMailerService::quickSend(
            'notificaciones@fagotto.cl',
            'Email de Prueba - ' . date('Y-m-d H:i:s'),
            '
            <html>
            <body style="font-family: Arial;">
                <h1 style="color: #4CAF50;">✅ Sistema de Correos Funcionando</h1>
                <p>Este es un email de prueba enviado desde tu sistema ERP.</p>
                <p><strong>Fecha:</strong> ' . date('Y-m-d H:i:s') . '</p>
                <p><strong>Servidor:</strong> mail.fagotto.cl</p>
                <hr>
                <p><small>Sistema Fagotto - Correos automáticos</small></p>
            </body>
            </html>
            ',
            'Sistema ERP'
        );

        if ($resultado) {
            return response()->json([
                'success' => true,
                'message' => 'Email enviado exitosamente a notificaciones@fagotto.cl',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el email. Revisa los logs.'
            ], 500);
        }
    }

    /**
     * Enviar email personalizado
     * 
     * Ruta: POST /send-email
     * Body: { "to": "email@example.com", "subject": "Asunto", "message": "Mensaje" }
     */
    public function sendCustom(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        $mailer = new PHPMailerService();
        
        $html = '
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px; background: #f4f4f4;">
                <div style="background: white; padding: 30px; border-radius: 8px;">
                    <h2 style="color: #333;">' . $request->subject . '</h2>
                    <div style="color: #666;">
                        ' . nl2br(htmlspecialchars($request->message)) . '
                    </div>
                    <hr style="margin: 30px 0;">
                    <p style="color: #999; font-size: 12px;">
                        Este correo fue enviado desde el Sistema Fagotto<br>
                        ' . date('Y-m-d H:i:s') . '
                    </p>
                </div>
            </div>
        </body>
        </html>
        ';

        $resultado = $mailer->send(
            $request->to,
            $request->subject,
            $html,
            $request->name ?? ''
        );

        if ($resultado) {
            return response()->json([
                'success' => true,
                'message' => 'Email enviado a ' . $request->to
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el email'
            ], 500);
        }
    }

    /**
     * Enviar email con plantilla Blade
     * 
     * Ejemplo de uso interno
     */
    public function sendWithTemplate($orderId)
    {
        // Ejemplo: obtener orden
        // $order = Order::findOrFail($orderId);
        
        $mailer = new PHPMailerService();
        
        $resultado = $mailer->sendWithView(
            'cliente@ejemplo.com',
            'Orden de Reparación #' . $orderId,
            'client_orders.repair_phone_email', // Vista que ya existe
            ['order' => ['id' => $orderId]], // Datos para la vista
            'Cliente Ejemplo'
        );

        return response()->json(['sent' => $resultado]);
    }
}
