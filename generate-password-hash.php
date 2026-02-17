<?php
/**
 * Script para generar hash bcrypt de contraseñas usando Laravel
 * Uso: php generate-password-hash.php <password>
 */

$password = $argv[1] ?? null;

if (!$password) {
    echo "\n❌ Error: Debes proporcionar una contraseña\n";
    echo "Uso: php generate-password-hash.php <password>\n\n";
    echo "Ejemplo: php generate-password-hash.php fagotto2026\n\n";
    exit(1);
}

// Generar hash usando bcrypt (PASSWORD_BCRYPT es el algoritmo usado por Laravel)
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

echo "\n🔐 Generando hash bcrypt...\n\n";
echo "Contraseña: {$password}\n";
echo "Hash: {$hash}\n";
echo "\n✅ Este hash es compatible con Laravel/PHP bcrypt.\n\n";
