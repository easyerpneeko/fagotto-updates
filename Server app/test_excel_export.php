<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Controllers_local\ReportsController;

// Simular una petición de Excel export
echo "🔧 Probando Excel Export directamente...\n\n";

try {
    // Simular parámetros de la petición
    $request = new Request([
        'start_date' => '2024-01-01',
        'end_date' => '2024-12-31',
        'payment_methods' => 'all'
    ]);
    
    echo "📋 Parámetros:\n";
    echo "- start_date: " . $request->start_date . "\n";
    echo "- end_date: " . $request->end_date . "\n";
    echo "- payment_methods: " . $request->payment_methods . "\n\n";
    
    // Crear instancia del controller
    $controller = new ReportsController();
    
    echo "📊 Llamando al método exportCustomExcel...\n";
    
    // Llamar al método
    $response = $controller->exportCustomExcel($request);
    
    echo "✅ Excel export ejecutado exitosamente!\n";
    echo "📄 Tipo de respuesta: " . get_class($response) . "\n";
    
    // Si es una respuesta de descarga, verificar headers
    if (method_exists($response, 'headers')) {
        echo "📁 Headers de respuesta:\n";
        foreach ($response->headers->all() as $key => $value) {
            echo "- $key: " . implode(', ', $value) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error en Excel export:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🏁 Prueba finalizada.\n";
