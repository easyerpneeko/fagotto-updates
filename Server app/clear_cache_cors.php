<?php
/**
 * Script para limpiar cache después de activar CORS
 * Ejecutar accediendo a: https://posfagotto.cl/clear_cache_cors.php
 */

echo "<h2>Limpiando Cache del Servidor...</h2>";

// Cambiar al directorio del proyecto
chdir(__DIR__);

echo "<p><strong>1. Config Cache:</strong> ";
exec('php artisan config:clear 2>&1', $output1, $return1);
echo $return1 === 0 ? "✅ OK" : "❌ Error";
echo "<br><code>" . implode('<br>', $output1) . "</code></p>";

echo "<p><strong>2. Route Cache:</strong> ";
exec('php artisan route:clear 2>&1', $output2, $return2);
echo $return2 === 0 ? "✅ OK" : "❌ Error";
echo "<br><code>" . implode('<br>', $output2) . "</code></p>";

echo "<p><strong>3. Application Cache:</strong> ";
exec('php artisan cache:clear 2>&1', $output3, $return3);
echo $return3 === 0 ? "✅ OK" : "❌ Error";
echo "<br><code>" . implode('<br>', $output3) . "</code></p>";

echo "<p><strong>4. View Cache:</strong> ";
exec('php artisan view:clear 2>&1', $output4, $return4);
echo $return4 === 0 ? "✅ OK" : "❌ Error";
echo "<br><code>" . implode('<br>', $output4) . "</code></p>";

echo "<h3 style='color: green;'>✅ Cache limpiado exitosamente</h3>";
echo "<p>El middleware CORS ya debería estar activo. Recarga tu página de metas.</p>";
echo "<p><a href='/' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Volver al Inicio</a></p>";
?>
