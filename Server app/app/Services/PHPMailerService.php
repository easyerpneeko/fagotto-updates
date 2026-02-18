<?php namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class PHPMailerService
{
    private $mailer;
    private $config;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->config = [
            'host'       => env('PHPMAILER_HOST', 'smtp.gmail.com'),
            'port'       => env('PHPMAILER_PORT', 587),
            'username'   => env('PHPMAILER_USERNAME', ''),
            'password'   => env('PHPMAILER_PASSWORD', ''),
            'from_email' => env('PHPMAILER_FROM_EMAIL', 'noreply@example.com'),
            'from_name'  => env('PHPMAILER_FROM_NAME', 'Sistema'),
            'encryption' => env('PHPMAILER_ENCRYPTION', 'tls'), // tls o ssl
            'debug'      => env('PHPMAILER_DEBUG', false),
        ];

        $this->configureSMTP();
    }

    /**
     * Configurar el servidor SMTP
     */
    private function configureSMTP()
    {
        try {
            // Configuración del servidor
            $this->mailer->isSMTP();
            $this->mailer->Host       = $this->config['host'];
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $this->config['username'];
            $this->mailer->Password   = $this->config['password'];
            $this->mailer->Port       = $this->config['port'];

            // Encriptación
            if (strtolower($this->config['encryption']) === 'ssl') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            // Debug (solo si está habilitado)
            if ($this->config['debug']) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }

            // Configuración general
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            
        } catch (Exception $e) {
            Log::error('PHPMailer Configuration Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enviar un email simple
     * 
     * @param string $to Email del destinatario
     * @param string $subject Asunto del correo
     * @param string $body Cuerpo del mensaje (HTML)
     * @param string $toName Nombre del destinatario (opcional)
     * @param array $attachments Archivos adjuntos (opcional)
     * @return bool
     */
    public function send($to, $subject, $body, $toName = '', $attachments = [])
    {
        try {
            // 🔧 FIX: Limpiar destinatarios anteriores para evitar acumulación
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Destinatario
            $this->mailer->addAddress($to, $toName);

            // Asunto y cuerpo
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->AltBody = strip_tags($body); // Versión texto plano

            // Agregar archivos adjuntos
            foreach ($attachments as $file) {
                if (is_array($file)) {
                    // ['path' => '/ruta/archivo.pdf', 'name' => 'documento.pdf']
                    $this->mailer->addAttachment($file['path'], $file['name'] ?? '');
                } else {
                    // Solo la ruta del archivo
                    $this->mailer->addAttachment($file);
                }
            }

            // Enviar
            $result = $this->mailer->send();
            
            Log::info('Email enviado exitosamente a: ' . $to);
            
            return $result;

        } catch (Exception $e) {
            Log::error('PHPMailer Send Error: ' . $this->mailer->ErrorInfo);
            Log::error('Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar email usando una vista Blade
     * 
     * @param string $to Email del destinatario
     * @param string $subject Asunto del correo
     * @param string $view Nombre de la vista blade
     * @param array $data Datos para la vista
     * @param string $toName Nombre del destinatario (opcional)
     * @param array $attachments Archivos adjuntos (opcional)
     * @return bool
     */
    public function sendWithView($to, $subject, $view, $data = [], $toName = '', $attachments = [])
    {
        try {
            // Renderizar la vista Blade
            $body = view($view, $data)->render();
            
            return $this->send($to, $subject, $body, $toName, $attachments);
            
        } catch (\Exception $e) {
            Log::error('Error al renderizar vista de email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar email con vista Blade a múltiples destinatarios (UN SOLO email)
     * 
     * @param array $recipients Array de emails
     * @param string $subject Asunto del correo
     * @param string $view Nombre de la vista blade
     * @param array $data Datos para la vista
     * @param array $attachments Archivos adjuntos (opcional)
     * @return bool
     */
    public function sendWithViewToMultiple($recipients, $subject, $view, $data = [], $attachments = [])
    {
        try {
            // Renderizar la vista Blade
            $body = view($view, $data)->render();
            
            return $this->sendToMultiple($recipients, $subject, $body, $attachments);
            
        } catch (\Exception $e) {
            Log::error('Error al renderizar vista de email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar email a múltiples destinatarios (opción eficiente - UN SOLO email)
     * Envía un solo correo con todos los destinatarios en BCC
     * 
     * @param array $recipients Array de emails ['email@example.com', 'otro@example.com']
     * @param string $subject Asunto del correo
     * @param string $body Cuerpo del mensaje (HTML)
     * @param array $attachments Archivos adjuntos (opcional)
     * @return bool
     */
    public function sendToMultiple($recipients, $subject, $body, $attachments = [])
    {
        try {
            // Limpiar destinatarios anteriores
            $this->mailer->clearAddresses();
            $this->mailer->clearBCCs();
            $this->mailer->clearAttachments();
            
            // Agregar todos los destinatarios en BCC (copia oculta)
            // Así cada uno recibe el correo sin ver los otros destinatarios
            foreach ($recipients as $email) {
                $this->mailer->addBCC($email);
            }

            // Asunto y cuerpo
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->AltBody = strip_tags($body);

            // Agregar archivos adjuntos
            foreach ($attachments as $file) {
                if (is_array($file)) {
                    $this->mailer->addAttachment($file['path'], $file['name'] ?? '');
                } else {
                    $this->mailer->addAttachment($file);
                }
            }

            // Enviar UN SOLO correo a todos
            $result = $this->mailer->send();
            
            Log::info('✅ Email enviado a ' . count($recipients) . ' destinatarios: ' . implode(', ', $recipients));
            
            return $result;

        } catch (Exception $e) {
            Log::error('❌ Error al enviar email múltiple: ' . $this->mailer->ErrorInfo);
            Log::error('Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar email a múltiples destinatarios (opción individual)
     * Envía un correo separado a cada destinatario
     * 
     * @param array $recipients Array de emails ['email@example.com', 'otro@example.com']
     * @param string $subject Asunto del correo
     * @param string $body Cuerpo del mensaje (HTML)
     * @param array $attachments Archivos adjuntos (opcional)
     * @return array Resultados ['success' => [], 'failed' => []]
     */
    public function sendBulk($recipients, $subject, $body, $attachments = [])
    {
        $results = [
            'success' => [],
            'failed' => []
        ];

        foreach ($recipients as $email) {
            if ($this->send($email, $subject, $body, '', $attachments)) {
                $results['success'][] = $email;
            } else {
                $results['failed'][] = $email;
            }
        }

        return $results;
    }

    /**
     * Método estático para uso rápido
     * 
     * @param string $to Email del destinatario
     * @param string $subject Asunto
     * @param string $body Cuerpo HTML
     * @param string $toName Nombre destinatario (opcional)
     * @return bool
     */
    public static function quickSend($to, $subject, $body, $toName = '')
    {
        $mailer = new self();
        return $mailer->send($to, $subject, $body, $toName);
    }

    /**
     * Obtener configuración actual (sin password)
     * 
     * @return array
     */
    public function getConfig()
    {
        $config = $this->config;
        $config['password'] = '***'; // Ocultar password
        return $config;
    }
}
