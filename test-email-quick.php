<?php
/**
 * Script de prueba rápida para PHPMailer
 * Ejecutar desde línea de comandos: php test-email-quick.php
 * 
 * Este script prueba el envío de email básico sin necesidad de Laravel
 * Útil para debugging de configuración SMTP
 */

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  📧 Test Rápido de PHPMailer - Sistema Fagotto            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Verificar que estamos en el directorio correcto
if (!file_exists('vendor/autoload.php')) {
    echo "❌ Error: No se encuentra vendor/autoload.php\n";
    echo "   Asegúrate de ejecutar este script desde /var/www/html/server\n";
    exit(1);
}

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "✅ PHPMailer cargado correctamente\n\n";

// Configuración (puedes modificar estos valores para probar)
$config = [
    'host' => 'mail.fagotto.cl',
    'port' => 465,
    'username' => 'notificaciones@fagotto.cl',
    'password' => '', // IMPORTANTE: Configurar password aquí para prueba
    'from_email' => 'notificaciones@fagotto.cl',
    'from_name' => 'Sistema Fagotto ERP Test',
    'encryption' => 'ssl',
];

// Email de prueba (CAMBIA ESTO por tu email)
$test_email = 'tu@email.com'; // ⚠️ CAMBIAR AQUÍ

echo "📋 Configuración:\n";
echo "   Host: {$config['host']}\n";
echo "   Port: {$config['port']}\n";
echo "   Username: {$config['username']}\n";
echo "   From: {$config['from_email']}\n";
echo "   To: {$test_email}\n";
echo "   Encryption: {$config['encryption']}\n\n";

// Verificar que se configuró el email de destino
if ($test_email === 'tu@email.com') {
    echo "⚠️  ADVERTENCIA: Necesitas cambiar \$test_email en la línea 28 del script\n";
    echo "   Edita el archivo y cambia 'tu@email.com' por tu email real\n\n";
}

// Verificar que se configuró la contraseña
if (empty($config['password'])) {
    echo "⚠️  ADVERTENCIA: Necesitas configurar la contraseña SMTP\n";
    echo "   Edita el archivo y agrega la contraseña en la línea 21\n\n";
    echo "   O mejor aún, usa las variables de entorno del .env:\n";
    echo "   Password debería estar en: .env como PHPMAILER_PASSWORD\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🚀 Iniciando prueba de envío...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    $mail = new PHPMailer(true);
    
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->Port = $config['port'];
    
    if (strtolower($config['encryption']) === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }
    
    // Debug detallado
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo "   🔍 DEBUG: $str\n";
    };
    
    echo "📡 Conectando al servidor SMTP...\n";
    
    // Configuración del email
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($test_email, 'Usuario de Prueba');
    
    // Contenido
    $mail->isHTML(true);
    $mail->Subject = '✅ Test de Email - Sistema Fagotto ERP';
    $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
                .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🧪 Email de Prueba</h1>
                    <p>Sistema de Notificaciones Fagotto ERP</p>
                </div>
                <div class="content">
                    <div class="success">
                        <strong>✅ ¡Email enviado exitosamente!</strong>
                    </div>
                    
                    <h2>📋 Detalles de la Prueba</h2>
                    <ul>
                        <li><strong>Servidor SMTP:</strong> ' . $config['host'] . '</li>
                        <li><strong>Puerto:</strong> ' . $config['port'] . '</li>
                        <li><strong>Encriptación:</strong> ' . strtoupper($config['encryption']) . '</li>
                        <li><strong>Fecha/Hora:</strong> ' . date('d/m/Y H:i:s') . '</li>
                    </ul>
                    
                    <p>Si estás viendo este email, significa que:</p>
                    <ul>
                        <li>✅ PHPMailer está correctamente instalado</li>
                        <li>✅ La configuración SMTP es correcta</li>
                        <li>✅ El servidor puede enviar emails</li>
                        <li>✅ El sistema de notificaciones está operativo</li>
                    </ul>
                    
                    <p><strong>🎉 ¡Todo listo para enviar notificaciones de pedidos!</strong></p>
                </div>
                <div class="footer">
                    <p>Sistema Interno Fagotto ERP - Email Automático</p>
                    <p>Test realizado: ' . date('d/m/Y H:i:s') . '</p>
                </div>
            </div>
        </body>
        </html>
    ';
    
    $mail->AltBody = 'Email de prueba del sistema Fagotto ERP. Si ves esto, el email fue enviado correctamente.';
    
    echo "\n📤 Enviando email de prueba...\n\n";
    
    $mail->send();
    
    echo "\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ ¡EMAIL ENVIADO EXITOSAMENTE!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "📬 Revisa la bandeja de entrada de: {$test_email}\n";
    echo "   (Si no aparece, revisa la carpeta de SPAM)\n\n";
    echo "🎉 El sistema de notificaciones por email está funcionando correctamente\n\n";
    
} catch (Exception $e) {
    echo "\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "❌ ERROR AL ENVIAR EMAIL\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "Error: {$mail->ErrorInfo}\n";
    echo "Excepción: {$e->getMessage()}\n\n";
    
    echo "🔧 Posibles soluciones:\n";
    echo "   1. Verificar que la contraseña SMTP sea correcta\n";
    echo "   2. Comprobar que el puerto {$config['port']} esté abierto en el firewall\n";
    echo "   3. Verificar que el servidor SMTP ({$config['host']}) sea accesible\n";
    echo "   4. Para debugging detallado, revisa los logs arriba\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Fin del test\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
