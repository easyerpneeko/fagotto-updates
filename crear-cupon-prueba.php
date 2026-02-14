<?php
/**
 * Script para crear cupón de prueba en la base de datos
 * Ejecutar: php crear-cupon-prueba.php
 */

// Configuración de base de datos desde .env
$host = 'posfagotto.cl';
$dbname = ''; // El usuario debe completar esto
$username = ''; // El usuario debe completar esto
$password = ''; // El usuario debe completar esto

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conectado a la base de datos\n\n";
    
    // Verificar si la tabla existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'cupones'");
    if ($stmt->rowCount() === 0) {
        echo "❌ La tabla 'cupones' no existe. Debes crearla primero.\n";
        exit(1);
    }
    
    // Generar código de cupón simple (3 dígitos)
    $codigo = str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
    
    // Verificar que el código no exista
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cupones WHERE codigo = ?");
    $stmt->execute([$codigo]);
    if ($stmt->fetchColumn() > 0) {
        echo "⚠️ El código {$codigo} ya existe. Ejecuta de nuevo para generar otro.\n";
        exit(0);
    }
    
    // Insertar cupón de prueba
    $sql = "INSERT INTO cupones (
        codigo,
        descripcion,
        tipo_descuento,
        valor_descuento,
        activo,
        usado,
        usos_maximos,
        usos_actuales,
        fecha_inicio,
        fecha_expiracion,
        created_at,
        updated_at
    ) VALUES (
        :codigo,
        '2x1 en Pastas - Prueba',
        'porcentaje',
        100,
        1,
        0,
        1,
        0,
        NOW(),
        DATE_ADD(NOW(), INTERVAL 30 DAY),
        NOW(),
        NOW()
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':codigo' => $codigo]);
    
    echo "🎫 ¡Cupón de prueba creado exitosamente!\n\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "   CÓDIGO DEL CUPÓN: {$codigo}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "📝 Detalles:\n";
    echo "   - Descripción: 2x1 en Pastas - Prueba\n";
    echo "   - Tipo: 100% descuento (gratis)\n";
    echo "   - Usos máximos: 1\n";
    echo "   - Válido hasta: " . date('d/m/Y', strtotime('+30 days')) . "\n\n";
    echo "✅ Úsalo en el sistema para probar la funcionalidad 2x1\n\n";
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "\n⚠️ Debes configurar las credenciales de BD en este archivo:\n";
    echo "   - \$host (ya configurado: posfagotto.cl)\n";
    echo "   - \$dbname\n";
    echo "   - \$username\n";
    echo "   - \$password\n";
    exit(1);
}
