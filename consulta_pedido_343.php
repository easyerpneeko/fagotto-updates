<?php
/*
 * Script para consultar el pedido 343 en la base de datos
 * erd_app_fagotto_merced_65eaf8c2b8c16
 */

// Configuración de la base de datos
$host = 'localhost';
$database = 'erd_app_fagotto_merced_65eaf8c2b8c16';
$username = 'root'; // Ajustar según tu configuración
$password = ''; // Ajustar según tu configuración

try {
    // Crear conexión PDO
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CONSULTA PEDIDO #343 ===\n";
    echo "Base de datos: $database\n\n";
    
    // Consultar el pedido específico en la tabla requests
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE id = 343");
    $stmt->execute();
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($request) {
        echo "✅ PEDIDO ENCONTRADO:\n";
        echo "==================\n";
        
        foreach ($request as $campo => $valor) {
            if ($campo === 'products') {
                echo "$campo: " . json_encode(json_decode($valor), JSON_PRETTY_PRINT) . "\n";
            } elseif ($campo === 'created_at' || $campo === 'updated_at') {
                echo "$campo: $valor\n";
            } else {
                echo "$campo: $valor\n";
            }
        }
        
        echo "\n=== DETALLES DEL PEDIDO ===\n";
        
        // Decodificar productos si existen
        if (!empty($request['products'])) {
            $productos = json_decode($request['products'], true);
            if ($productos) {
                echo "\n📦 PRODUCTOS EN EL PEDIDO:\n";
                foreach ($productos as $index => $producto) {
                    echo "  Producto " . ($index + 1) . ":\n";
                    foreach ($producto as $key => $value) {
                        echo "    $key: $value\n";
                    }
                    echo "\n";
                }
            }
        }
        
        // Buscar información relacionada del payment
        echo "\n💰 INFORMACIÓN DE PAGO:\n";
        $stmt_payment = $pdo->prepare("SELECT * FROM payments WHERE request_id = 343");
        $stmt_payment->execute();
        $payment = $stmt_payment->fetch(PDO::FETCH_ASSOC);
        
        if ($payment) {
            foreach ($payment as $campo => $valor) {
                echo "$campo: $valor\n";
            }
        } else {
            echo "No se encontró información de pago para este pedido.\n";
        }
        
        // Verificar si existe una venta relacionada
        echo "\n🧾 VERIFICAR VENTA RELACIONADA:\n";
        $stmt_sell = $pdo->prepare("
            SELECT * FROM sells 
            WHERE comment LIKE '%343%' 
            OR comment LIKE '%pedido%343%'
            OR JSON_EXTRACT(products, '$[*].request_id') = '343'
            ORDER BY created_at DESC
        ");
        $stmt_sell->execute();
        $sells = $stmt_sell->fetchAll(PDO::FETCH_ASSOC);
        
        if ($sells) {
            echo "Ventas relacionadas encontradas:\n";
            foreach ($sells as $sell) {
                echo "  Venta ID: {$sell['id']}\n";
                echo "  Total: {$sell['total']}\n";
                echo "  Fecha: {$sell['created_at']}\n";
                echo "  Comentario: {$sell['comment']}\n";
                echo "  ---\n";
            }
        } else {
            echo "No se encontraron ventas directamente relacionadas.\n";
        }
        
    } else {
        echo "❌ No se encontró el pedido #343 en la tabla requests\n";
        
        // Verificar si existe en otras tablas relacionadas
        echo "\n🔍 Buscando en otras tablas...\n";
        
        // Buscar en requests_reposteria
        $stmt_repo = $pdo->prepare("SELECT * FROM requests_reposteria WHERE id = 343");
        $stmt_repo->execute();
        $request_repo = $stmt_repo->fetch(PDO::FETCH_ASSOC);
        
        if ($request_repo) {
            echo "✅ Encontrado en requests_reposteria:\n";
            foreach ($request_repo as $campo => $valor) {
                echo "$campo: $valor\n";
            }
        }
        
        // Buscar en orders
        $stmt_order = $pdo->prepare("SELECT * FROM orders WHERE id = 343");
        $stmt_order->execute();
        $order = $stmt_order->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            echo "✅ Encontrado en orders:\n";
            foreach ($order as $campo => $valor) {
                echo "$campo: $valor\n";
            }
        }
    }
    
    // Mostrar estructura de tablas relacionadas
    echo "\n📋 ESTRUCTURA DE TABLAS RELACIONADAS:\n";
    echo "=====================================\n";
    
    $tables = ['requests', 'payments', 'sells', 'orders'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->prepare("DESCRIBE $table");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "\nTabla: $table\n";
            echo str_repeat("-", strlen($table) + 7) . "\n";
            foreach ($columns as $column) {
                echo "  {$column['Field']} ({$column['Type']})\n";
            }
        } catch (Exception $e) {
            echo "\nTabla $table: No existe o no accesible\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "\nVerifica:\n";
    echo "1. Que MySQL esté ejecutándose\n";
    echo "2. Que las credenciales sean correctas\n";
    echo "3. Que la base de datos '$database' exista\n";
}
?>
