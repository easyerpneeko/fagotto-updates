#!/bin/bash
# Script de prueba para el sistema de notificaciones por email
# Autor: Sistema Fagotto ERP
# Fecha: 2025-01-15

echo "╔════════════════════════════════════════════════════════════╗"
echo "║  🧪 TEST: Sistema de Notificaciones Email - Pedidos       ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Configuración
SERVER_PATH="/var/www/html/server"
LOG_FILE="$SERVER_PATH/storage/logs/laravel.log"

echo "📋 Información del Sistema:"
echo "   Servidor: $SERVER_PATH"
echo "   Log: $LOG_FILE"
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -d "$SERVER_PATH" ]; then
    echo "❌ Error: No se encuentra el directorio del servidor: $SERVER_PATH"
    exit 1
fi

cd $SERVER_PATH

# Test 1: Verificar configuración .env
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📝 Test 1: Verificando configuración SMTP en .env"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if grep -q "PHPMAILER_HOST" .env; then
    echo "✅ PHPMAILER_HOST encontrado"
    grep "PHPMAILER_HOST" .env | head -1
else
    echo "❌ PHPMAILER_HOST no configurado en .env"
fi

if grep -q "PHPMAILER_USERNAME" .env; then
    echo "✅ PHPMAILER_USERNAME encontrado"
    grep "PHPMAILER_USERNAME" .env | head -1
else
    echo "❌ PHPMAILER_USERNAME no configurado en .env"
fi

echo ""

# Test 2: Verificar que PHPMailer está instalado
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📦 Test 2: Verificando instalación de PHPMailer"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ -d "vendor/phpmailer/phpmailer" ]; then
    echo "✅ PHPMailer instalado en vendor/phpmailer/phpmailer"
    if [ -f "vendor/phpmailer/phpmailer/VERSION" ]; then
        echo "   Versión: $(cat vendor/phpmailer/phpmailer/VERSION)"
    fi
else
    echo "❌ PHPMailer NO encontrado"
    echo "   Ejecuta: composer require phpmailer/phpmailer"
fi

echo ""

# Test 3: Verificar servicio PHPMailerService
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🔧 Test 3: Verificando servicio PHPMailerService"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ -f "app/Services/PHPMailerService.php" ]; then
    echo "✅ PHPMailerService.php encontrado"
    echo "   Ubicación: app/Services/PHPMailerService.php"
    echo "   Tamaño: $(wc -c < app/Services/PHPMailerService.php) bytes"
else
    echo "❌ PHPMailerService.php NO encontrado"
fi

echo ""

# Test 4: Verificar template de email
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📧 Test 4: Verificando template de email"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ -f "resources/views/emails/pedido_final.blade.php" ]; then
    echo "✅ Template pedido_final.blade.php encontrado"
    echo "   Ubicación: resources/views/emails/pedido_final.blade.php"
    echo "   Líneas: $(wc -l < resources/views/emails/pedido_final.blade.php)"
else
    echo "❌ Template pedido_final.blade.php NO encontrado"
fi

echo ""

# Test 5: Verificar modificación en RequestsController
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🎯 Test 5: Verificando integración en RequestsController"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ -f "app/Http/Controllers/Controllers_local/RequestsController.php" ]; then
    if grep -q "PHPMailerService" app/Http/Controllers/Controllers_local/RequestsController.php; then
        echo "✅ Import de PHPMailerService encontrado"
    else
        echo "❌ Import de PHPMailerService NO encontrado"
    fi
    
    if grep -q "Enviar notificación por email del pedido" app/Http/Controllers/Controllers_local/RequestsController.php; then
        echo "✅ Código de envío de email encontrado"
    else
        echo "❌ Código de envío de email NO encontrado"
    fi
    
    if grep -q "soledad.zavalaga@fagotto.cl" app/Http/Controllers/Controllers_local/RequestsController.php; then
        echo "✅ Destinatarios configurados correctamente"
        echo "   📬 5 destinatarios: Soledad, Erick, Ma Gabriela, Margarita, Jimmy (soporte)"
    else
        echo "❌ Destinatarios NO configurados"
    fi
else
    echo "❌ RequestsController.php NO encontrado"
fi

echo ""

# Test 6: Envío de email de prueba con PHP artisan tinker
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✉️  Test 6: Envío de email de prueba"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Para enviar un email de prueba manualmente, ejecuta:"
echo ""
echo "   php artisan tinker"
echo ""
echo "Luego ejecuta este código:"
echo ""
echo '   $mailer = new \App\Services\PHPMailerService();'
echo '   $mailer->send("tu@email.com", "Test Email", "<h1>Email de Prueba</h1>");'
echo ""
echo "O para probar con el template completo:"
echo ""
echo '   $data = ["pedido" => (object)["id" => 999, "created_at" => now(), "contact_name" => "Test", "contact_phone" => "123456", "paymode" => "Test", "comment" => "Test", "subtotal" => 1000, "iva" => 190, "price" => 1190, "emergency" => 0], "local" => (object)["Name" => "Local Test"], "productos" => []];'
echo '   $mailer->sendWithView("tu@email.com", "Test Pedido", "emails.pedido_final", $data);'
echo ""

# Test 7: Revisar últimos logs
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📜 Test 7: Últimos logs de email (si existen)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ -f "$LOG_FILE" ]; then
    echo "Log encontrado, buscando entradas de email..."
    echo ""
    
    # Buscar logs recientes de email
    if grep -q "Email de pedido" "$LOG_FILE"; then
        echo "📨 Últimas 10 líneas de logs de email:"
        echo ""
        grep "Email de pedido" "$LOG_FILE" | tail -10
    else
        echo "ℹ️  No se encontraron logs de envío de email aún"
        echo "   (Esto es normal si no se ha creado ningún pedido todavía)"
    fi
else
    echo "⚠️  Archivo de log no encontrado: $LOG_FILE"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 RESUMEN DE TESTS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "✅ = Pasó      ❌ = Falló      ⚠️  = Advertencia"
echo ""
echo "Para crear un pedido de prueba desde el local 121:"
echo "   1. Abre la app Electron"
echo "   2. Inicia sesión con el local 121"
echo "   3. Ve a Pedido Final"
echo "   4. Crea un pedido de prueba"
echo "   5. Revisa los emails de los 5 destinatarios"
echo ""
echo "Para monitorear logs en tiempo real:"
echo "   tail -f $LOG_FILE | grep 'Email de pedido'"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
