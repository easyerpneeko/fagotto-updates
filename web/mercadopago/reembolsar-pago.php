<?php
/**
 * Reembolsar un pago aprobado
 */

require_once __DIR__ . '/config.php';

$accessToken = MP_ACCESS_TOKEN;
$paymentId = $argv[1] ?? null;
$amount = $argv[2] ?? null; // Opcional: para reembolso parcial

if (!$paymentId) {
    die("\nUso: php reembolsar-pago.php [PAYMENT_ID] [MONTO_OPCIONAL]\n\n" .
        "Ejemplos:\n" .
        "  php reembolsar-pago.php 123456789 (reembolso total)\n" .
        "  php reembolsar-pago.php 123456789 500 (reembolso parcial de $500)\n\n");
}

echo "\n💰 REEMBOLSAR PAGO\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "Payment ID: {$paymentId}\n";

if ($amount) {
    echo "Tipo: Reembolso PARCIAL\n";
    echo "Monto a reembolsar: \${$amount} CLP\n\n";
} else {
    echo "Tipo: Reembolso TOTAL\n\n";
}

$url = "https://api.mercadopago.com/v1/payments/{$paymentId}/refunds";

$data = null;
if ($amount) {
    $data = json_encode(['amount' => (float)$amount]);
}

echo "📡 Enviando solicitud de reembolso...\n";
echo "URL: {$url}\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);

if ($data) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
}

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n\n";

if ($httpCode === 200 || $httpCode === 201) {
    echo "✅ ¡Reembolso procesado exitosamente!\n\n";
    
    $result = json_decode($response, true);
    
    if ($result) {
        echo "╔═══════════════════════════════════════════════════════════════╗\n";
        echo "║                    REEMBOLSO EXITOSO                          ║\n";
        echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
        
        echo "🆔 Refund ID: " . ($result['id'] ?? 'N/A') . "\n";
        echo "💰 Monto reembolsado: $" . ($result['amount'] ?? 'N/A') . " CLP\n";
        echo "📊 Estado: " . ($result['status'] ?? 'N/A') . "\n";
        echo "📅 Fecha: " . ($result['date_created'] ?? 'N/A') . "\n\n";
        
        echo "ℹ️  El dinero será devuelto a la tarjeta del cliente\n";
        echo "⏱️  Tiempo estimado: 5-10 días hábiles\n\n";
        
        echo "JSON Completo:\n";
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    }
    
} else {
    echo "❌ Error al procesar reembolso\n\n";
    echo "Respuesta:\n";
    echo $response . "\n\n";
    
    $error = json_decode($response, true);
    if ($error) {
        echo "JSON decodificado:\n";
        echo json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        
        if (isset($error['message'])) {
            echo "💡 Posibles causas:\n";
            echo "   - El pago no existe o no está aprobado\n";
            echo "   - El pago ya fue reembolsado\n";
            echo "   - El monto a reembolsar es mayor al disponible\n";
            echo "   - Han pasado más de X días desde el pago\n\n";
        }
    }
}
