// PASO 1: Verificar versión actual
// Pega esto en la consola de DevTools (F12) de tu aplicación

console.log('🔍 VERSIÓN ACTUAL:', require('electron').remote.app.getVersion());
console.log('🔍 NOMBRE APP:', require('electron').remote.app.getName());

// PASO 2: Verificar configuración del auto-updater
console.log('🔍 CONFIGURACIÓN AUTO-UPDATER:');
