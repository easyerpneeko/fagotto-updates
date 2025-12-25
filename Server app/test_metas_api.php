<?php
/**
 * Script de prueba para verificar que la tabla metas_locales funciona
 * Acceder a: https://posfagotto.cl/test_metas_api.php?mes=12&anio=2025
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    $mes = $_GET['mes'] ?? date('n');
    $anio = $_GET['anio'] ?? date('Y');
    
    $pdo = DB::connection()->getPdo();
    $stmt = $pdo->prepare("SELECT * FROM metas_locales WHERE mes = ? AND anio = ?");
    $stmt->execute([$mes, $anio]);
    $metas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $metas,
        'mes' => $mes,
        'anio' => $anio,
        'count' => count($metas)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ]);
}
