<?php
/**
 * Script para ejecutar automáticamente el SQL de la tabla de descuentos
 */

// Configuración de la base de datos (ajusta según tu configuración)
$host = '127.0.0.1';
$dbname = 'fagottodb';
$username = 'root';
$password = '';

try {
    echo "Conectando a la base de datos...\n";
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Conectado a $dbname\n\n";

    // Verificar si la tabla ya existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'product_discounts_by_branch'");
    $exists = $stmt->fetch();

    if ($exists) {
        echo "⚠ La tabla 'product_discounts_by_branch' ya existe.\n";
        echo "¿Deseas recrearla? Esto eliminará todos los datos existentes. (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim($line) != 'y' && trim($line) != 'yes') {
            echo "Operación cancelada.\n";
            exit(0);
        }
        fclose($handle);
        
        echo "Eliminando tabla existente...\n";
        $pdo->exec("DROP TABLE product_discounts_by_branch");
        echo "✓ Tabla eliminada\n\n";
    }

    echo "Creando tabla 'product_discounts_by_branch'...\n";
    
    $sql = "CREATE TABLE `product_discounts_by_branch` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `application_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID de la sucursal/negocio',
  `product_id` BIGINT UNSIGNED NULL COMMENT 'ID del producto específico (NULL = todos)',
  `product_category` VARCHAR(255) NULL COMMENT 'Categoría de productos (ej: salsas)',
  `product_name_pattern` VARCHAR(255) NULL COMMENT 'Patrón de nombre (ej: %salsa%)',
  `discount_percentage` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Porcentaje de descuento (10.00 = 10%)',
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Monto fijo de descuento',
  `active` TINYINT(1) DEFAULT 1 COMMENT 'Si está activo o no',
  `start_date` DATE NULL COMMENT 'Fecha inicio de vigencia',
  `end_date` DATE NULL COMMENT 'Fecha fin de vigencia',
  `description` TEXT NULL COMMENT 'Descripción del descuento',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  
  INDEX `idx_application_id` (`application_id`),
  INDEX `idx_product_id` (`product_id`),
  INDEX `idx_active` (`active`),
  INDEX `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "✓ Tabla creada exitosamente\n\n";

    // Insertar un descuento de ejemplo (comentado, descomenta si quieres)
    /*
    echo "Insertando descuento de ejemplo...\n";
    $stmt = $pdo->prepare("INSERT INTO product_discounts_by_branch 
        (application_id, product_name_pattern, discount_percentage, active, description)
        VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([1, '%salsa%', 10.00, 1, 'Descuento de prueba 10% en salsas']);
    echo "✓ Descuento de ejemplo insertado (ID: " . $pdo->lastInsertId() . ")\n";
    */

    echo "\n✅ TABLA CREADA EXITOSAMENTE\n";
    echo "Ya puedes usar el sistema de descuentos en /admin/descuentos\n";

} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
