<?php
/**
 * Verificar estado del pago y opciones disponibles
 */

require_once __DIR__ . '/config.php';

$accessToken = MP_ACCESS_TOKEN;
$orderId = $argv[1] ?? null;

if (!$orderId) {
    die("\nUso: php verificar-opciones-pago.php [ORDER_ID]\n\n");
}

echo "\n📊 VERIFICAR OPCIONES DEL PAGO\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Consultar estado actual
$url = "https://api.mercadopago.com/v1/orders/{$orderId}";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die("❌ Error al consultar el pago (HTTP {$httpCode})\n{$response}\n\n");
}

$data = json_decode($response, true);
$status = $data['status'] ?? 'unknown';

echo "🆔 Order ID: {$orderId}\n";
echo "📊 Estado actual: {$status}\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "📖 OPCIONES SEGÚN EL ESTADO:\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

switch ($status) {
    case 'created':
    case 'at_terminal':
        echo "⏳ PAGO PENDIENTE (Esperando en el terminal)\n\n";
        echo "✅ OPCIONES DISPONIBLES:\n\n";
        echo "1️⃣ CANCELAR EN EL TERMINAL FÍSICO:\n";
        echo "   - Presionar botón X o Cancelar\n";
        echo "   - El pago se marcará como 'cancelled'\n";
        echo "   - Se puede enviar uno nuevo inmediatamente\n\n";
        
        echo "2️⃣ ESPERAR TIMEOUT (5-10 minutos):\n";
        echo "   - El pago expira automáticamente\n";
        echo "   - Estado cambia a 'cancelled' o 'expired'\n\n";
        
        echo "3️⃣ COMPLETAR EL PAGO:\n";
        echo "   - Pasar la tarjeta en el terminal\n";
        echo "   - Estado cambia a 'approved' o 'rejected'\n\n";
        
        echo "❌ NO DISPONIBLE VÍA API:\n";
        echo "   - DELETE /payment-intents/{id} → 403 en Chile\n";
        echo "   - PUT /orders/{id} → No soportado\n\n";
        break;
        
    case 'approved':
        echo "✅ PAGO APROBADO\n\n";
        echo "💡 OPCIONES:\n\n";
        echo "1️⃣ REEMBOLSO TOTAL:\n";
        $paymentId = $data['transactions']['payments'][0]['id'] ?? null;
        if ($paymentId) {
            echo "   POST https://api.mercadopago.com/v1/payments/{$paymentId}/refunds\n";
            echo "   Authorization: Bearer {$accessToken}\n";
            echo "   Body: (vacío para reembolso total)\n\n";
        }
        
        echo "2️⃣ REEMBOLSO PARCIAL:\n";
        echo "   POST https://api.mercadopago.com/v1/payments/{$paymentId}/refunds\n";
        echo "   Body: {\"amount\": 500}\n\n";
        
        echo "3️⃣ NUEVO PAGO:\n";
        echo "   - Enviar un nuevo cobro al terminal\n";
        echo "   - El anterior queda aprobado\n\n";
        break;
        
    case 'rejected':
        echo "❌ PAGO RECHAZADO\n\n";
        echo "✅ OPCIONES:\n\n";
        echo "1️⃣ ENVIAR NUEVO PAGO:\n";
        echo "   - php enviar-pago.php [MONTO]\n";
        echo "   - El cliente puede intentar con otra tarjeta\n\n";
        
        echo "2️⃣ VERIFICAR MOTIVO DEL RECHAZO:\n";
        $statusDetail = $data['transactions']['payments'][0]['status_detail'] ?? 'N/A';
        echo "   Status Detail: {$statusDetail}\n";
        echo "   - cc_rejected_insufficient_amount (fondos insuficientes)\n";
        echo "   - cc_rejected_bad_filled_card_number (número inválido)\n";
        echo "   - cc_rejected_bad_filled_security_code (CVV inválido)\n";
        echo "   - cc_rejected_call_for_authorize (banco rechazó)\n\n";
        break;
        
    case 'cancelled':
    case 'canceled':
    case 'expired':
        echo "🚫 PAGO CANCELADO/EXPIRADO\n\n";
        $statusDetail = $data['transactions']['payments'][0]['status_detail'] ?? 'N/A';
        echo "Detalle: {$statusDetail}\n";
        echo "   - cancel_by_terminal (cancelado en terminal)\n";
        echo "   - timeout (expiró por inactividad)\n\n";
        
        echo "✅ OPCIONES:\n\n";
        echo "1️⃣ ENVIAR NUEVO PAGO:\n";
        echo "   - php enviar-pago.php [MONTO]\n";
        echo "   - El terminal está libre\n\n";
        break;
        
    default:
        echo "❓ Estado desconocido: {$status}\n\n";
        break;
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "🔄 RESUMEN - CUANDO EL CLIENTE SE EQUIVOCA:\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "ANTES DE PASAR LA TARJETA:\n";
echo "→ Cancelar en terminal físico (botón X)\n";
echo "→ Enviar nuevo pago con monto correcto\n\n";

echo "DESPUÉS DE PASAR LA TARJETA (APROBADO):\n";
echo "→ Hacer reembolso vía API\n";
echo "→ Enviar nuevo pago con monto correcto\n\n";

echo "DESPUÉS DE PASAR LA TARJETA (RECHAZADO):\n";
echo "→ Enviar nuevo pago (terminal ya libre)\n";
echo "→ Cliente puede usar otra tarjeta\n\n";

// Mostrar JSON completo
echo "═══════════════════════════════════════════════════════════════\n";
echo "📄 RESPUESTA COMPLETA:\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
