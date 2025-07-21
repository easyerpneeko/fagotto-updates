<template>
  <div v-if="showUpdateDialog" class="update-overlay">
    <div class="update-dialog">
      <!-- Logo Fagotto -->
      <div class="logo-header">
        <img src="~@/assets/logo.png" alt="Fagotto ERP" class="fagotto-logo" />
      </div>
      
      <div class="update-header">
        <h3>{{ updateTitle }}</h3>
        <p>{{ updateMessage }}</p>
      </div>
      
      <div class="update-content">
        <!-- verificamos si hay una actualización disponible desde la repo -->
        <div v-if="updateState === 'available'" class="update-available">
          <div class="update-icon">🚀</div>
          <p><strong>Nueva versión disponible: v{{ updateInfo.version }}</strong></p>
          <p class="release-name">{{ updateInfo.releaseName }}</p>
          <div class="release-notes" v-if="updateInfo.releaseNotes">
            <details>
              <summary>Ver notas de la versión</summary>
              <div class="notes-content">{{ updateInfo.releaseNotes }}</div>
            </details>
          </div>
          <div class="update-actions">
            <button @click="skipUpdate" class="btn btn-secondary">
              <span class="btn-icon">⏭️</span>
              Omitir
            </button>
            <button @click="startDownload" class="btn btn-primary">
              <span class="btn-icon">📥</span>
              Descargar Ahora
            </button>
          </div>
        </div>
        
        <!-- Descargar -->
        <div v-if="updateState === 'downloading'" class="update-downloading">
          <div class="update-icon downloading">📦</div>
          <p><strong>Descargando {{ downloadInfo.fileName }}...</strong></p>
          <div class="progress-container">
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: downloadProgress + '%' }">
                <span class="progress-sparkle">✨</span>
              </div>
            </div>
            <div class="progress-info">
              <span class="progress-percent">{{ downloadProgress }}%</span>
              <span class="progress-size">{{ formatBytes(downloadInfo.downloadedBytes || 0) }} / {{ formatBytes(downloadInfo.totalBytes || downloadInfo.fileSize || 0) }}</span>
            </div>
            <div class="progress-speed" v-if="downloadInfo.speed">
              <span class="speed-text">🚀 {{ formatSpeed(downloadInfo.speed) }}</span>
            </div>
          </div>
          <div class="download-animation">
            <div class="dots">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
        </div>
        
        <!-- Download Complete -->
        <div v-if="updateState === 'ready'" class="update-ready">
          <div class="update-icon success">✅</div>
          <p><strong>Descarga completada exitosamente</strong></p>
          <p>La actualización está lista para instalar. La aplicación se reiniciará automáticamente.</p>
          <div class="update-actions">
            <button @click="skipUpdate" class="btn btn-secondary">
              <span class="btn-icon">⏰</span>
              Instalar Después
            </button>
            <button @click="installUpdate" class="btn btn-primary btn-install">
              <span class="btn-icon">🔄</span>
              Instalar y Reiniciar
            </button>
          </div>
        </div>
        
        <!-- Error -->
        <div v-if="updateState === 'error'" class="update-error">
          <div class="update-icon error">❌</div>
          <p><strong>Error en la descarga</strong></p>
          <p class="error-message">{{ errorMessage }}</p>
          <div class="update-actions">
            <button @click="skipUpdate" class="btn btn-secondary">
              <span class="btn-icon">❌</span>
              Cerrar
            </button>
            <button @click="retryDownload" class="btn btn-primary">
              <span class="btn-icon">🔄</span>
              Reintentar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const { ipcRenderer } = require('electron');

export default {
  name: 'UpdateDialog',
  data() {
    return {
      showUpdateDialog: false,
      updateState: 'checking', // checking, available, downloading, ready, error
      updateInfo: {},
      downloadInfo: {},
      downloadProgress: 0,
      errorMessage: '',
      downloadedFilePath: ''
    };
  },
  computed: {
    updateTitle() {
      switch(this.updateState) {
        case 'available': return 'Actualización Disponible';
        case 'downloading': return 'Descargando Actualización';
        case 'ready': return 'Actualización Lista';
        case 'error': return 'Error de Descarga';
        default: return 'Buscando Actualizaciones';
      }
    },
    updateMessage() {
      switch(this.updateState) {
        case 'available': return 'Hay una nueva versión disponible para descargar';
        case 'downloading': return 'Por favor espera mientras se descarga la actualización';
        case 'ready': return 'La actualización se ha descargado correctamente';
        case 'error': return 'Ocurrió un error durante la descarga';
        default: return 'Verificando si hay actualizaciones disponibles...';
      }
    }
  },
  mounted() {
    this.setupUpdateListeners();
    // Auto-check for updates on component mount
    this.checkForUpdates();
  },
  methods: {
    // Custom update checker for private repos
    async checkForUpdates() {
      try {
        console.log('🔍 Checking for updates...');
        this.updateState = 'checking';
        
        const response = await fetch('https://api.github.com/repos/easyerpneeko/fagotto-updates/releases/latest', {
          headers: {
            'Authorization': 'token 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',
            'Accept': 'application/vnd.github.v3+json'
          }
        });
        
        if (!response.ok) {
          throw new Error(`Error: ${response.status}`);
        }
        
        const release = await response.json();
        const currentVersion = require('electron').remote.app.getVersion();
        
        console.log('📊 Current version:', currentVersion);
        console.log('🆕 Latest version:', release.tag_name);
        
        if (release.tag_name !== `v${currentVersion}`) {
          console.log('✅ Update available!');
          this.updateInfo = {
            version: release.tag_name.replace('v', ''),
            releaseNotes: release.body,
            downloadUrl: release.assets[0] ? release.assets[0].browser_download_url : null
          };
          this.updateState = 'available';
          this.showUpdateDialog = true;
        } else {
          console.log('✅ App is up to date');
          this.updateState = 'checking';
        }
        
      } catch (error) {
        console.error('❌ Error checking for updates:', error);
        this.errorMessage = error.message;
        this.updateState = 'error';
        this.showUpdateDialog = true;
      }
    },
    
    setupUpdateListeners() {
      // Update available
      ipcRenderer.on('update_available', (event, info) => {
        console.log('Update available:', info);
        this.updateInfo = info;
        this.updateState = 'available';
        this.showUpdateDialog = true;
      });
      
      // Download starting
      ipcRenderer.on('download_starting', (event, info) => {
        console.log('Download starting:', info);
        this.downloadInfo = info;
        this.updateState = 'downloading';
        this.downloadProgress = 0;
      });
      
      // Download progress
      ipcRenderer.on('download_progress', (event, progress) => {
        console.log('Download progress:', progress);
        this.downloadProgress = progress.percent || 0;
        this.downloadInfo.downloadedBytes = progress.downloadedBytes;
        this.downloadInfo.totalBytes = progress.totalBytes;
        this.downloadInfo.speed = progress.speed;
      });
      
      // Download completed
      ipcRenderer.on('download_completed', (event, info) => {
        console.log('Download completed:', info);
        this.downloadedFilePath = info.filePath;
        this.updateState = 'ready';
      });
      
      // Download error
      ipcRenderer.on('download_error', (event, error) => {
        console.log('Download error:', error);
        this.errorMessage = error.error;
        this.updateState = 'error';
      });
      
      // Update error
      ipcRenderer.on('update_error', (event, error) => {
        console.log('Update error:', error);
        this.errorMessage = error.error || error;
        this.updateState = 'error';
        this.showUpdateDialog = true;
      });
      
      // Download cancelled
      ipcRenderer.on('download_cancelled', () => {
        console.log('Download cancelled');
        this.updateState = 'available';
      });
      
      // Update not available
      ipcRenderer.on('update_not_available', (event, info) => {
        console.log('Update not available:', info);
        // Don't show dialog if no update available
        this.showUpdateDialog = false;
        this.updateState = 'checking';
      });
    },
    
    startDownload() {
      console.log('Starting download...');
      this.updateState = 'downloading';
      
      // Use the check_for_updates to trigger download since we disabled manual download
      ipcRenderer.send('check_for_updates');
    },
    
    skipUpdate() {
      console.log('Skipping update');
      this.showUpdateDialog = false;
      this.updateState = 'checking';
    },
    
    installUpdate() {
      console.log('Installing update:', this.downloadedFilePath);
      ipcRenderer.send('install_update', this.downloadedFilePath);
    },
    
    retryDownload() {
      console.log('Retrying download');
      this.errorMessage = '';
      this.updateState = 'available';
      // Force check for updates (this will reset state)
      ipcRenderer.send('force_check_updates');
    },
    
    formatBytes(bytes) {
      if (!bytes || bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
    
    formatSpeed(bytesPerSecond) {
      if (!bytesPerSecond || bytesPerSecond === 0) return '0 KB/s';
      const k = 1024;
      const sizes = ['B/s', 'KB/s', 'MB/s', 'GB/s'];
      const i = Math.floor(Math.log(bytesPerSecond) / Math.log(k));
      return parseFloat((bytesPerSecond / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

.update-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.9) 100%);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  backdrop-filter: blur(8px);
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.update-dialog {
  background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
  border-radius: 20px;
  padding: 0;
  max-width: 600px;
  width: 90%;
  box-shadow: 
    0 20px 40px rgba(0, 0, 0, 0.15),
    0 10px 20px rgba(0, 0, 0, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.9);
  animation: slideUp 0.4s ease-out;
  overflow: hidden;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

@keyframes slideUp {
  from { 
    opacity: 0; 
    transform: translateY(30px) scale(0.95); 
  }
  to { 
    opacity: 1; 
    transform: translateY(0) scale(1); 
  }
}

.logo-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 25px;
  text-align: center;
  color: white;
  position: relative;
  overflow: hidden;
}

.logo-header::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
  animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
  0%, 100% { transform: rotate(0deg); }
  50% { transform: rotate(180deg); }
}

.fagotto-logo {
  width: 160x;
  height: 64px;
  margin-bottom: 12px;
  position: relative;
  z-index: 1;
}

.app-title {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  position: relative;
  z-index: 1;
}

.update-header {
  padding: 30px 30px 20px 30px;
  text-align: center;
  background: white;
}

.update-header h3 {
  margin: 0 0 10px 0;
  color: #2c3e50;
  font-size: 22px;
  font-weight: 600;
}

.update-header p {
  margin: 0;
  color: #6c757d;
  font-size: 16px;
  line-height: 1.5;
}

.update-content {
  padding: 0 30px 30px 30px;
  background: white;
}

.update-icon {
  font-size: 48px;
  margin-bottom: 20px;
  display: block;
  text-align: center;
}

.update-icon.downloading {
  animation: bounce 2s infinite;
}

.update-icon.success {
  animation: pulse 2s infinite;
}

.update-icon.error {
  animation: shake 0.5s ease-in-out;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
  40% { transform: translateY(-10px); }
  60% { transform: translateY(-5px); }
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}

.release-name {
  font-size: 14px;
  color: #6c757d;
  margin-bottom: 15px;
}

.release-notes {
  margin: 15px 0;
  text-align: left;
}

.release-notes details {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 12px;
}

.release-notes summary {
  cursor: pointer;
  font-weight: 500;
  color: #495057;
  outline: none;
}

.notes-content {
  margin-top: 10px;
  font-size: 14px;
  line-height: 1.6;
  color: #6c757d;
  white-space: pre-wrap;
}

.progress-container {
  margin: 20px 0;
}

.progress-bar {
  width: 100%;
  height: 12px;
  background: linear-gradient(90deg, #e9ecef 0%, #f8f9fa 100%);
  border-radius: 6px;
  overflow: hidden;
  position: relative;
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #28a745 0%, #20c997 50%, #17a2b8 100%);
  transition: width 0.3s ease;
  position: relative;
  border-radius: 6px;
  box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
}

.progress-sparkle {
  position: absolute;
  right: -10px;
  top: 50%;
  transform: translateY(-50%);
  animation: sparkle 1.5s ease-in-out infinite;
}

@keyframes sparkle {
  0%, 100% { opacity: 0; transform: translateY(-50%) scale(0.8); }
  50% { opacity: 1; transform: translateY(-50%) scale(1.2); }
}

.progress-info {
  display: flex;
  justify-content: space-between;
  margin-top: 8px;
  font-size: 14px;
}

.progress-percent {
  font-weight: 600;
  color: #28a745;
}

.progress-size {
  color: #6c757d;
}

.progress-speed {
  text-align: center;
  margin-top: 5px;
}

.speed-text {
  font-size: 12px;
  color: #17a2b8;
  background: rgba(23, 162, 184, 0.1);
  padding: 4px 8px;
  border-radius: 12px;
}

.download-animation {
  text-align: center;
  margin-top: 15px;
}

.dots {
  display: inline-block;
}

.dots span {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #17a2b8;
  margin: 0 3px;
  animation: loading 1.4s infinite ease-in-out both;
}

.dots span:nth-child(1) { animation-delay: -0.32s; }
.dots span:nth-child(2) { animation-delay: -0.16s; }

@keyframes loading {
  0%, 80%, 100% { 
    transform: scale(0.8);
    opacity: 0.5;
  } 
  40% { 
    transform: scale(1.2);
    opacity: 1;
  }
}

.update-actions {
  display: flex;
  justify-content: space-between;
  gap: 15px;
  margin-top: 25px;
}

.btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  font-family: inherit;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  flex: 1;
}

.btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s ease;
}

.btn:hover::before {
  left: 100%;
}

.btn-primary {
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

.btn-primary:hover {
  background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 123, 255, 0.4);
}

.btn-secondary {
  background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.btn-secondary:hover {
  background: linear-gradient(135deg, #545b62 0%, #383d41 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(108, 117, 125, 0.4);
}

.btn-install {
  background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-install:hover {
  background: linear-gradient(135deg, #1e7e34 0%, #155724 100%);
  box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
}

.btn-icon {
  font-size: 16px;
}

.update-available, .update-downloading, .update-ready, .update-error {
  padding: 20px 0;
  text-align: center;
}

.update-available p, .update-downloading p, .update-ready p {
  margin: 10px 0;
  line-height: 1.6;
}

.update-error {
  color: #dc3545;
}

.update-ready {
  color: #28a745;
}

.error-message {
  background: #f8d7da;
  color: #721c24;
  padding: 12px;
  border-radius: 8px;
  margin: 15px 0;
  border-left: 4px solid #dc3545;
}

/* Responsive design */
@media (max-width: 480px) {
  .update-dialog {
    width: 95%;
    margin: 10px;
  }
  
  .logo-header {
    padding: 20px;
  }
  
  .fagotto-logo {
    width: 48px;
    height: 48px;
  }
  
  .app-title {
    font-size: 20px;
  }
  
  .update-header, .update-content {
    padding: 20px;
  }
  
  .update-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
    margin-bottom: 10px;
  }
}
</style>
