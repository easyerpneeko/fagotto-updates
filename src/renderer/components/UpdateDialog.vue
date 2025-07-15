<template>
  <div v-if="showUpdateDialog" class="update-overlay">
    <div class="update-dialog">
      <div class="update-header">
        <h3>{{ updateTitle }}</h3>
        <p>{{ updateMessage }}</p>
      </div>
      
      <div class="update-content">
        <!-- Update Available -->
        <div v-if="updateState === 'available'" class="update-available">
          <p><strong>Nueva versión disponible: {{ updateInfo.version }}</strong></p>
          <p>{{ updateInfo.releaseName }}</p>
          <div class="update-actions">
            <button @click="skipUpdate" class="btn-secondary">Omitir</button>
            <button @click="startDownload" class="btn-primary">Descargar Ahora</button>
          </div>
        </div>
        
        <!-- Downloading -->
        <div v-if="updateState === 'downloading'" class="update-downloading">
          <p><strong>Descargando {{ downloadInfo.fileName }}...</strong></p>
          <div class="progress-bar">
            <div class="progress-fill" :style="{ width: downloadProgress + '%' }"></div>
          </div>
          <p class="progress-text">{{ downloadProgress }}% - {{ formatBytes(downloadInfo.transferred) }} / {{ formatBytes(downloadInfo.total) }}</p>
        </div>
        
        <!-- Download Complete -->
        <div v-if="updateState === 'ready'" class="update-ready">
          <p><strong>✅ Descarga completada</strong></p>
          <p>La actualización está lista para instalar</p>
          <div class="update-actions">
            <button @click="skipUpdate" class="btn-secondary">Instalar Después</button>
            <button @click="installUpdate" class="btn-primary">Instalar y Reiniciar</button>
          </div>
        </div>
        
        <!-- Error -->
        <div v-if="updateState === 'error'" class="update-error">
          <p><strong>❌ Error en la descarga</strong></p>
          <p>{{ errorMessage }}</p>
          <div class="update-actions">
            <button @click="skipUpdate" class="btn-secondary">Cerrar</button>
            <button @click="retryDownload" class="btn-primary">Reintentar</button>
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
        this.downloadProgress = progress.percent;
        this.downloadInfo.transferred = progress.transferred;
        this.downloadInfo.total = progress.total;
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
        this.errorMessage = error;
        this.updateState = 'error';
        this.showUpdateDialog = true;
      });
    },
    
    startDownload() {
      console.log('Starting download...');
      this.updateState = 'downloading';
      
      // Request download through main process with proper authentication
      ipcRenderer.send('download_update', {
        version: this.updateInfo.version,
        downloadUrl: this.updateInfo.downloadUrl
      });
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
      // Try download again
      this.startDownload();
    },
    
    formatBytes(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
  }
};
</script>

<style scoped>
.update-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.update-dialog {
  background: white;
  border-radius: 8px;
  padding: 20px;
  max-width: 500px;
  width: 90%;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.update-header {
  margin-bottom: 20px;
  text-align: center;
}

.update-header h3 {
  margin: 0 0 10px 0;
  color: #333;
}

.update-header p {
  margin: 0;
  color: #666;
}

.update-content {
  text-align: center;
}

.progress-bar {
  width: 100%;
  height: 20px;
  background-color: #f0f0f0;
  border-radius: 10px;
  overflow: hidden;
  margin: 10px 0;
}

.progress-fill {
  height: 100%;
  background-color: #4CAF50;
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 14px;
  color: #666;
  margin: 10px 0;
}

.update-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
}

.btn-primary {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.btn-primary:hover {
  background-color: #0056b3;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.btn-secondary:hover {
  background-color: #545b62;
}

.update-available, .update-downloading, .update-ready, .update-error {
  padding: 10px 0;
}

.update-error {
  color: #dc3545;
}

.update-ready {
  color: #28a745;
}
</style>
