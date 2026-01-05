// Helper para tracking de versión de la aplicación
// Este módulo envía periódicamente la versión de la app al servidor
// para que puedas monitorear qué versión está usando cada negocio

const { ipcRenderer } = require('electron');

class VersionTracker {
  constructor() {
    this.trackingInterval = null;
    this.TRACKING_INTERVAL_MS = 5 * 60 * 1000; // 5 minutos
  }

  /**
   * Inicia el tracking automático de versión
   * Envía la versión actual cada 5 minutos al servidor
   */
  startTracking() {
    // Enviar inmediatamente al iniciar
    this.sendVersionPing();

    // Enviar cada 5 minutos
    this.trackingInterval = setInterval(() => {
      this.sendVersionPing();
    }, this.TRACKING_INTERVAL_MS);

    console.log('📊 Version tracking iniciado - enviando cada 5 minutos');
  }

  /**
   * Detiene el tracking automático
   */
  stopTracking() {
    if (this.trackingInterval) {
      clearInterval(this.trackingInterval);
      this.trackingInterval = null;
      console.log('🛑 Version tracking detenido');
    }
  }

  /**
   * Envía un ping con la versión actual al servidor
   * Lee el serial del archivo aplication.json (sistema Electron)
   */
  sendVersionPing() {
    try {
      const fs = require('fs');
      
      // Leer el archivo aplication.json para obtener el serial
      if (!fs.existsSync('aplication.json')) {
        console.log('⚠️ No existe aplication.json, no se puede trackear versión');
        return;
      }
      
      const data = fs.readFileSync('aplication.json', 'utf-8');
      const appData = JSON.parse(data);
      
      if (!appData || !appData.Serial) {
        console.log('⚠️ No hay Serial en aplication.json, no se puede trackear versión');
        return;
      }

      // Enviar al main process para que haga la petición
      ipcRenderer.send('track_version', { serial: appData.Serial });
      
      console.log('📡 Version ping enviado - Serial:', appData.Serial.substring(0, 10) + '...');
      
    } catch (error) {
      console.error('❌ Error enviando version ping:', error);
    }
  }

  /**
   * Envía un ping manual (útil para testing)
   */
  sendManualPing() {
    console.log('🔄 Enviando ping manual de versión...');
    this.sendVersionPing();
  }
}

// Crear instancia singleton
const versionTracker = new VersionTracker();

// Exponer globalmente para debugging
window.versionTracker = versionTracker;

export default versionTracker;
