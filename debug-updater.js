// Código temporal para debug del auto-updater v1.1.0
// Agregar esto al renderer process (en una consola del dev tools)

// Forzar verificación manual de updates
ipcRenderer.send('check_for_updates');

// Escuchar eventos de update
ipcRenderer.on('update_available', (event, info) => {
    console.log('🎉 UPDATE AVAILABLE v1.1.0:', info);
    alert('¡Actualización disponible! Versión: ' + info.version);
});
});

ipcRenderer.on('update_error', (event, error) => {
    console.log('❌ UPDATE ERROR:', error);
});

ipcRenderer.on('update_progress', (event, progress) => {
    console.log('📥 DOWNLOADING:', progress.percent + '%');
});

ipcRenderer.on('update_downloaded', (event, info) => {
    console.log('✅ UPDATE DOWNLOADED:', info);
    const result = confirm('Actualización descargada. ¿Reiniciar ahora?');
    if (result) {
        ipcRenderer.send('restart_app');
    }
});
