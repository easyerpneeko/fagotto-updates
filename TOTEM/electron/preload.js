const { contextBridge, ipcRenderer } = require('electron');

// Exponer APIs seguras al contexto del renderizador
contextBridge.exposeInMainWorld('electronAPI', {
  // Información de la aplicación
  getAppInfo: () => ipcRenderer.invoke('get-app-info'),
  
  // Controles de ventana
  quitApp: () => ipcRenderer.invoke('quit-app'),
  minimizeApp: () => ipcRenderer.invoke('minimize-app'),
  toggleFullscreen: () => ipcRenderer.invoke('toggle-fullscreen'),
  
  // Información del sistema
  platform: process.platform,
  
  // Notificaciones
  onNotification: (callback) => {
    ipcRenderer.on('notification', (event, data) => callback(data));
  }
});

// Log de inicio
console.log('🔒 Preload script cargado - contextBridge habilitado');
