<?php
/**
 * Endpoint de prueba directo para verificar stock
 * Acceder vía: http://tu-dominio.com/Server%20app/test_stock_direct.php
 */

// Cargar Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Probar conexión a easyerp_master
    $productos = DB::connection('easyerp_master')
        ->table('pedidofinal_precios')
        ->select('id', 'producto as name', 'stock', 'unidad_medida', 'precio', 'categoria')
        ->whereNotNull('stock')
        ->orderBy('producto', 'asc')
        ->limit(5)
        ->get();
    
    echo json_encode([
        'success' => true,
        'count' => $productos->count(),
        'productos' => $productos
    ], JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
