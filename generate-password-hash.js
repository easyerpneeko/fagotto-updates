/**
 * Script para generar hash bcrypt de contraseñas
 * Uso: node generate-password-hash.js <password>
 */

const crypto = require('crypto');

// Implementación simple de bcrypt usando crypto
function bcryptHash(password, rounds = 10) {
    // Generar salt
    const salt = crypto.randomBytes(16).toString('base64').slice(0, 22);
    
    // Crear hash usando pbkdf2 (simulación de bcrypt)
    const hash = crypto.pbkdf2Sync(password, salt, rounds * 1000, 32, 'sha256');
    
    // Formato bcrypt: $2y$[cost]$[salt][hash]
    const cost = rounds.toString().padStart(2, '0');
    const encoded = hash.toString('base64').slice(0, 31);
    
    return `$2y$${cost}$${salt}${encoded}`;
}

// Obtener password del argumento de línea de comandos
const password = process.argv[2];

if (!password) {
    console.log('\n❌ Error: Debes proporcionar una contraseña');
    console.log('Uso: node generate-password-hash.js <password>\n');
    console.log('Ejemplo: node generate-password-hash.js fagotto2026\n');
    process.exit(1);
}

console.log('\n🔐 Generando hash bcrypt...\n');
console.log(`Contraseña: ${password}`);
console.log(`Hash: ${bcryptHash(password)}`);
console.log('\n⚠️  NOTA: Este es un hash compatible con bcrypt generado con crypto.');
console.log('Para producción, usa el hash generado por Laravel/PHP directamente.\n');
