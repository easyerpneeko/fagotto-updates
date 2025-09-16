<?php
// Script para crear la tabla TurnosCaja usando la misma conexión que la aplicación

// Cargar Laravel
require_once __DIR__ . '/Server app/vendor/autoload.php';
$app = require_once __DIR__ . '/Server app/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    echo "🔍 Verificando conexión a la base de datos...\n";
    
    // Verificar la conexión actual
    $database = DB::connection('mysql_local')->getDatabaseName();
    echo "📂 Base de datos actual: " . $database . "\n";
    
    // Verificar si la tabla ya existe
    if (Schema::connection('mysql_local')->hasTable('TurnosCaja')) {
        echo "⚠️  La tabla TurnosCaja ya existe. Eliminándola...\n";
        Schema::connection('mysql_local')->dropIfExists('TurnosCaja');
    }
    
    // Crear la tabla TurnosCaja
    echo "🔨 Creando tabla TurnosCaja...\n";
    
    Schema::connection('mysql_local')->create('TurnosCaja', function (Blueprint $table) {
        $table->id();
        $table->boolean('turno_abierto')->default(1);
        $table->timestamp('turno_abierto_en')->useCurrent();
        $table->timestamp('turno_cerrado_en')->nullable();
        $table->integer('usuario_id');
        $table->decimal('monto_inicial', 10, 2)->default(0.00);
        $table->decimal('monto_final', 10, 2)->default(0.00);
        $table->decimal('total_ventas', 10, 2)->default(0.00);
        $table->decimal('total_contado', 10, 2)->default(0.00);
        $table->decimal('total_tarjeta', 10, 2)->default(0.00);
        $table->decimal('total_transferencia', 10, 2)->default(0.00);
        $table->decimal('total_cheque', 10, 2)->default(0.00);
        $table->decimal('total_credito', 10, 2)->default(0.00);
        $table->decimal('total_uber_eats', 10, 2)->default(0.00);
        $table->decimal('total_otros_medios', 10, 2)->default(0.00);
        $table->decimal('total_general', 10, 2)->default(0.00);
        $table->decimal('diferencia_general', 10, 2)->default(0.00);
        $table->text('observaciones')->nullable();
        $table->timestamps();
        
        // Índices
        $table->index('turno_abierto', 'idx_turno_estado');
        $table->index('usuario_id', 'idx_usuario');
        $table->index('turno_abierto_en', 'idx_fecha_apertura');
        $table->index('turno_cerrado_en', 'idx_fecha_cierre');
    });
    
    echo "✅ Tabla TurnosCaja creada exitosamente!\n";
    
    // Verificar que la tabla se creó
    if (Schema::connection('mysql_local')->hasTable('TurnosCaja')) {
        echo "✅ Verificación: La tabla TurnosCaja existe en la base de datos.\n";
        
        // Mostrar las columnas
        $columns = Schema::connection('mysql_local')->getColumnListing('TurnosCaja');
        echo "📋 Columnas creadas: " . implode(', ', $columns) . "\n";
    } else {
        echo "❌ Error: La tabla no se creó correctamente.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error al crear la tabla: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " línea " . $e->getLine() . "\n";
}

echo "\n🏁 Script finalizado.\n";
?>