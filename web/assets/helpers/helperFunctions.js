function activateLoader() {
    const loader = document.getElementById('loader');
    loader.classList.remove('hide');
    loader.classList.add('show');
    // Reiniciar el temporizador y mensajes
    if (window.loadStartTime) {
        window.loadStartTime = performance.now();
        window.loaderMessageIndex = 0;
    }
}
function desactivateLoader() {
    const loader = document.getElementById('loader');
    loader.classList.remove('show');
    loader.classList.add('hide');
    // Detener todos los intervalos
    if (window.loaderTimerInterval) {
        clearInterval(window.loaderTimerInterval);
    }
    if (window.loaderMessageInterval) {
        clearInterval(window.loaderMessageInterval);
    }
    // Mostrar tiempo final en consola
    if (window.loadStartTime) {
        const finalTime = ((performance.now() - window.loadStartTime) / 1000).toFixed(2);
        console.log(`✅ Dashboard cargado en ${finalTime}s`);
    }
}

function formatearMontoChile(monto) { 
    return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(monto); 
}