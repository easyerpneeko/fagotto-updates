import { app, BrowserWindow, ipcMain, Menu } from 'electron';
const { autoUpdater } = require('electron-updater');
const fs = require('fs');
const path = require('path');
const os = require('os');
const https = require('https');

import ConfigHelper from '../renderer/helpers/ConfigHelper.js';
import Connection from '../renderer/helpers/Connection.js';
import BaseUrl from '../renderer/helpers/baseUrl.js';

/**
 * Set `__static` path to static files in production
 * https://simulatedgreg.gitbooks.io/electron-vue/content/en/using-static-assets.html
 */
if (process.env.NODE_ENV !== 'development') {
  global.__static = require('path').join(__dirname, '/static').replace(/\\/g, '\\\\')
}

// Enable logging for auto-updater
autoUpdater.logger = require('electron-log');
autoUpdater.logger.transports.file.level = 'info';
console.log('App starting...');

let mainWindow;
let appInitialized = false;
/**/
const winURL = process.env.NODE_ENV === 'development'
  ? `http://localhost:9080`
  : `file://${__dirname}/index.html`
async function createMainWindow() {/**/

  console.log('Create Main Window Function')

  /**Initial window options */
  mainWindow = new BrowserWindow({
    height: 563,
    useContentSize: true,
    width: 1000,
    title: 'Fagotto Chile',
    webPreferences: { nodeIntegration: true, contextIsolation: false, enableRemoteModule: true }
  })

  // Limpiar caché al crear la ventana
  console.log('🧹 Limpiando caché al iniciar...');
  await mainWindow.webContents.session.clearCache();
  await mainWindow.webContents.session.clearStorageData({
    storages: ['appcache', 'serviceworkers', 'cachestorage']
  });
  console.log('✅ Caché limpiado');

  mainWindow.loadURL(winURL)

  mainWindow.on('closed', () => {
    mainWindow = null
  })

  // Set The Menu to the Main Window
  createApplicationMenu();
  // mainWindow.setMenuBarVisibility(false)


}

function createApplicationMenu() {
  const template = [
    {
      label: 'Archivo',
      submenu: [
        {
          label: 'Crear Pedido',
          accelerator: 'CmdOrCtrl+N',
          click: () => {
            if (mainWindow) {
              mainWindow.webContents.send('navigate-to', 'crear-pedido');
            }
          }
        },
        {
          label: 'Totem',
          accelerator: 'CmdOrCtrl+T',
          click: () => {
            if (mainWindow) {
              // Cargar la página del kiosko
              const totemURL = process.env.NODE_ENV === 'development'
                ? `http://localhost:9080/pages/kiosko_menu.html`
                : `file://${__dirname}/pages/kiosko_menu.html`;
              mainWindow.loadURL(totemURL);
            }
          }
        },
        { type: 'separator' },
        {
          label: 'Salir',
          accelerator: 'CmdOrCtrl+Q',
          click: () => {
            app.quit();
          }
        }
      ]
    },
    {
      label: 'Editar',
      submenu: [
        { label: 'Deshacer', accelerator: 'CmdOrCtrl+Z', role: 'undo' },
        { label: 'Rehacer', accelerator: 'Shift+CmdOrCtrl+Z', role: 'redo' },
        { type: 'separator' },
        { label: 'Cortar', accelerator: 'CmdOrCtrl+X', role: 'cut' },
        { label: 'Copiar', accelerator: 'CmdOrCtrl+C', role: 'copy' },
        { label: 'Pegar', accelerator: 'CmdOrCtrl+V', role: 'paste' }
      ]
    },
    {
      label: 'Ver',
      submenu: [
        { label: 'Recargar', accelerator: 'CmdOrCtrl+R', role: 'reload' },
        { label: 'Pantalla Completa', accelerator: 'F11', role: 'togglefullscreen' }
      ]
    },
    {
      label: 'Ayuda',
      submenu: [
        {
          label: 'Acerca de',
          click: () => {
            if (mainWindow) {
              mainWindow.webContents.send('show-about');
            }
          }
        }
      ]
    }
  ];

  const menu = Menu.buildFromTemplate(template);
  Menu.setApplicationMenu(menu);
}

function initApp() {

  /*fetch('https://randomuser.me/api/')
  .then(async (res) => {
      console.log('aaa')
      var response = await res.clone().json();
      console.log(response)
  });*/
  console.log('App Init');
  
  appInitialized = true;
  ConfigHelper.InitializeAplication();
  createMainWindow();
}

// Read GitHub token from environment variable or file
let githubToken = process.env.GH_TOKEN || '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';

console.log('🔍 Looking for GitHub token...');
console.log('🔑 Token from environment:', process.env.GH_TOKEN ? 'Present' : 'Missing');

try {
  const tokenFilePath = path.resolve(__dirname, '..', '..', 'gh_token.json');
  if (fs.existsSync(tokenFilePath)) {
    const configFile = fs.readFileSync(tokenFilePath, 'utf8');
    const config = JSON.parse(configFile);
    if (config.token) {
      githubToken = config.token;
      console.log('✅ GitHub token loaded from file');
    }
  }
} catch (error) {
  console.error('❌ Error loading token from file:', error.message);
}

console.log('🔑 Final token configured:', githubToken ? 'Present' : 'Missing');

// Custom fetch function for binary downloads
function customFetchBinary(url, options = {}) {
  return new Promise((resolve, reject) => {
    const urlObj = new URL(url);
    
    // Add authorization header if downloading from GitHub
    const headers = options.headers || {};
    if (urlObj.hostname.includes('github')) {
      if (githubToken) {
        headers['Authorization'] = `token ${githubToken}`;
        headers['Accept'] = 'application/octet-stream';
        console.log('🔐 Adding GitHub auth to binary download for:', urlObj.hostname);
      }
    }
    
    console.log('📥 Requesting:', url);
    console.log('🔑 Headers:', Object.keys(headers));
    
    const requestOptions = {
      hostname: urlObj.hostname,
      path: urlObj.pathname + urlObj.search,
      method: options.method || 'GET',
      headers: headers
    };

    const req = https.request(requestOptions, (res) => {
      console.log('📨 Response status:', res.statusCode, 'from', urlObj.hostname);
      
      // Handle redirects - preserve auth headers
      if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
        console.log('📍 Redirect to:', res.headers.location);
        // Preserve headers for redirect
        const redirectOptions = { ...options, headers: headers };
        customFetchBinary(res.headers.location, redirectOptions).then(resolve).catch(reject);
        return;
      }
      
      // Create response object compatible with fetch API
      const response = {
        ok: res.statusCode >= 200 && res.statusCode < 300,
        status: res.statusCode,
        statusCode: res.statusCode,  // Also provide statusCode for compatibility
        statusText: res.statusMessage,
        statusMessage: res.statusMessage,  // Also provide statusMessage for compatibility
        headers: res.headers,
        data: null  // Will be set when data is received
      };
      
      if (!response.ok) {
        // For non-success responses, just return the response object
        resolve(response);
        return;
      }
      
      const chunks = [];
      
      res.on('data', (chunk) => {
        chunks.push(chunk);
      });
      
      res.on('end', () => {
        const buffer = Buffer.concat(chunks);
        response.data = buffer;
        response.buffer = () => Promise.resolve(buffer);
        response.arrayBuffer = () => Promise.resolve(buffer);
        resolve(response);
      });
    });
    
    req.on('error', (error) => {
      reject(error);
    });
    
    req.end();
  });
}

// Custom fetch function using https module
function customFetch(url, options = {}) {
  return new Promise((resolve, reject) => {
    const urlObj = new URL(url);
    const requestOptions = {
      hostname: urlObj.hostname,
      path: urlObj.pathname + urlObj.search,
      method: options.method || 'GET',
      headers: options.headers || {}
    };

    const req = https.request(requestOptions, (res) => {
      let data = '';
      
      res.on('data', (chunk) => {
        data += chunk;
      });
      
      res.on('end', () => {
        const response = {
          ok: res.statusCode >= 200 && res.statusCode < 300,
          status: res.statusCode,
          statusCode: res.statusCode,
          statusText: res.statusMessage,
          statusMessage: res.statusMessage,
          headers: res.headers,
          data: data,
          text: () => Promise.resolve(data),
          json: () => Promise.resolve(JSON.parse(data))
        };
        resolve(response);
      });
    });
    
    req.on('error', (error) => {
      reject(error);
    });
    
    req.end();
  });
}

// Configure auto-updater
console.log('Configuring auto-updater for private repo...');

// DISABLE standard auto-updater for private repos - use custom handler only
// autoUpdater.setFeedURL({
//   provider: "github",
//   owner: "easyerpneeko",
//   repo: "fagotto-updates",
//   private: true,
//   token: githubToken,
// });

console.log('✅ Using CUSTOM auto-updater for private repo');
console.log('🔑 Token configured:', githubToken ? 'Present' : 'Missing');

// DISABLE standard auto-updater for private repos
// autoUpdater.checkForUpdatesAndNotify(); 

app.on('ready', () => {
  initApp();
  
  // Use ONLY custom update checker for private repos
  console.log('🔄 Using custom update checker for private repo...');
  console.log('NODE_ENV:', process.env.NODE_ENV);
  setTimeout(() => {
    handlePrivateRepoUpdate();
  }, 3000); // Wait 3 seconds after app startup
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }/**/
});

app.on('activate', () => {
  if (!appInitialized) {
    initApp();
  }else if (mainWindow === null) {
    createMainWindow();
  }
});

ipcMain.on('app_version', (event) => {
  console.log('app version in index.js: ',app.getVersion());
  event.sender.send('app_version', { version: app.getVersion() });
});

// Handle version tracking ping from renderer
ipcMain.on('track_version', async (event, data) => {
  try {
    const { serial } = data;
    if (!serial) {
      console.log('⚠️ No serial provided for version tracking');
      return;
    }

    const version = app.getVersion();
    const systemInfo = JSON.stringify({
      platform: os.platform(),
      arch: os.arch(),
      release: os.release(),
      hostname: os.hostname()
    });

    console.log('📊 Tracking version:', version, 'for serial:', serial);

    // Send to backend - usando la ruta de Laravel
    const trackUrl = 'https://posfagotto.cl/api/track-version';
    
    https.request(trackUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'App-Key': serial
      }
    }, (res) => {
      let responseData = '';
      res.on('data', (chunk) => {
        responseData += chunk;
      });
      res.on('end', () => {
        if (res.statusCode === 200) {
          console.log('✅ Version tracked successfully:', responseData);
        } else {
          console.log('⚠️ Version tracking response:', res.statusCode, responseData);
        }
      });
    }).on('error', (error) => {
      console.error('❌ Error tracking version:', error.message);
    }).end(JSON.stringify({
      version: version,
      system_info: systemInfo
    }));

  } catch (error) {
    console.error('❌ Error in track_version handler:', error);
  }
});

// Handle update download request from renderer (DISABLED - using automatic download instead)
// ipcMain.on('download_update', async (event, data) => {
//   try {
//     console.log('📥 Download update requested:', data);
//     
//     // Get latest release info (no auth needed for public repos)
//     const response = await customFetch('https://api.github.com/repos/easyerpneeko/fagotto-updates/releases/latest', {
//       headers: {
//         'Accept': 'application/vnd.github.v3+json'
//       }
//     });
//     
//     if (!response.ok) {
//       throw new Error(`Error: ${response.status}`);
//     }
//     
//     const release = await response.json();
//     
//     // Find the .exe file
//     const exeAsset = release.assets.find(asset => 
//       asset.name.endsWith('.exe') && 
//       asset.name.includes('fagotto-erp-app-setup')
//     );
//     
//     if (!exeAsset) {
//       throw new Error('No se encontró el archivo .exe');
//     }
//     
//     console.log('📊 Manual download - Asset info:', {
//       id: exeAsset.id,
//       name: exeAsset.name,
//       size: exeAsset.size,
//       url: exeAsset.url
//     });
//     
//     // Start download with authenticated request
//     await downloadFileWithAuth(exeAsset, release.tag_name.replace('v', ''));
//     
//   } catch (error) {
//     console.error('❌ Error in download_update handler:', error);
//     event.sender.send('download_error', { error: error.message });
//   }
// });

// Download file with authentication and progress
async function downloadFileWithAuth(asset, version) {
  const maxRetries = 3;
  let retryCount = 0;
  
  while (retryCount < maxRetries) {
    try {
      console.log(`📥 Downloading file with auth (attempt ${retryCount + 1}/${maxRetries}):`, asset.name);
      console.log('📊 Asset info:', {
        id: asset.id,
        name: asset.name,
        size: asset.size,
        url: asset.url,
        browser_download_url: asset.browser_download_url
      });
      
      // Send download starting event
      if (mainWindow) {
        mainWindow.webContents.send('download_starting', {
          fileName: asset.name,
          fileSize: asset.size,
          version: version
        });
      }
      
      // Use API URL (asset.url) instead of browser_download_url for private repos
      // This allows authentication via token
      const downloadUrl = asset.url;
      
      console.log('📥 Downloading from API with auth:', downloadUrl);
      console.log('🔑 Using token:', githubToken ? (githubToken.substring(0, 10) + '...') : 'NONE');
      
      const response = await customFetchBinary(downloadUrl);
      
      console.log('📨 Final response status:', response.status);
      
      if (!response.ok) {
        console.error('❌ Download failed!');
        console.error('   Status:', response.status, response.statusText);
        console.error('   Headers:', JSON.stringify(response.headers, null, 2));
        console.error('   URL:', downloadUrl);
        console.error('   Token present:', !!githubToken);
        throw new Error(`Error al descargar: ${response.status} - ${response.statusText}`);
      }
      
      // Create downloads folder if it doesn't exist
      const downloadsPath = path.join(os.homedir(), 'Downloads');
      const filePath = path.join(downloadsPath, asset.name);
      
      console.log('📏 Total bytes from asset:', asset.size);
      
      // Download with progress tracking
      await downloadWithProgress(response, filePath, asset.size);
      
      // Send completion event
      if (mainWindow) {
        mainWindow.webContents.send('download_completed', {
          filePath: filePath,
          version: version
        });
      }
      
      // Auto-open the installer
      console.log('🚀 Opening installer:', filePath);
      require('electron').shell.openPath(filePath);
      
      return; // Success, exit retry loop
      
    } catch (error) {
      console.error(`❌ Error downloading file (attempt ${retryCount + 1}):`, error);
      retryCount++;
      
      if (retryCount < maxRetries) {
        console.log(`🔄 Retrying in 2 seconds... (${retryCount}/${maxRetries})`);
        await new Promise(resolve => setTimeout(resolve, 2000));
      } else {
        console.error('❌ Max retries reached, giving up');
        if (mainWindow) {
          mainWindow.webContents.send('download_error', { error: error.message });
        }
      }
    }
  }
}

// Download with progress tracking
async function downloadWithProgress(response, filePath, totalSize) {
  return new Promise(async (resolve, reject) => {
    try {
      const writer = fs.createWriteStream(filePath);
      
      // Get buffer from response data
      const buffer = response.data;
      const totalBytes = totalSize || buffer.length;
      
      // Simulate progress for better UX since we download the entire buffer at once
      const chunkSize = Math.max(1024 * 1024, Math.floor(buffer.length / 20)); // 1MB chunks or 20 steps
      let downloadedSize = 0;
      
      const startTime = Date.now();
      
      // Send initial progress
      if (mainWindow) {
        mainWindow.webContents.send('download_progress', {
          percent: 0,
          downloadedBytes: 0,
          totalBytes: totalBytes,
          speed: 0
        });
      }
      
      // Write buffer in chunks to simulate progress
      for (let offset = 0; offset < buffer.length; offset += chunkSize) {
        const chunk = buffer.slice(offset, Math.min(offset + chunkSize, buffer.length));
        writer.write(chunk);
        downloadedSize += chunk.length;
        
        // Calculate progress
        const percent = totalBytes > 0 ? Math.round((downloadedSize / totalBytes) * 100) : 0;
        const elapsed = Date.now() - startTime;
        const speed = elapsed > 0 ? Math.round((downloadedSize * 1000) / elapsed) : 0; // bytes per second
        
        // Send progress update
        if (mainWindow) {
          mainWindow.webContents.send('download_progress', {
            percent: Math.min(percent, 100),
            downloadedBytes: downloadedSize,
            totalBytes: totalBytes,
            speed: speed
          });
        }
        
        console.log(`📊 Progress: ${Math.min(percent, 100)}% (${downloadedSize}/${totalBytes} bytes) - Speed: ${Math.round(speed / 1024)}KB/s`);
        
        // Small delay to make progress visible
        if (offset + chunkSize < buffer.length) {
          await new Promise(resolve => setTimeout(resolve, 50));
        }
      }
      
      writer.end();
      
      // Send final progress
      if (mainWindow) {
        mainWindow.webContents.send('download_progress', {
          percent: 100,
          downloadedBytes: totalBytes,
          totalBytes: totalBytes,
          speed: 0
        });
      }
      
      console.log('✅ Download completed successfully');
      resolve();
      
    } catch (error) {
      console.error('❌ Error in downloadWithProgress:', error);
      reject(error);
    }
  });
}

// Auto-updater event handlers
autoUpdater.on('checking-for-update', () => {
  console.log('🔍 Checking for update...');
  console.log('Current version:', app.getVersion());
  console.log('Feed URL:', autoUpdater.getFeedURL());
});

autoUpdater.on('update-available', (info) => {
  console.log('✅ Update available:', info.version);
  console.log('Update info:', JSON.stringify(info, null, 2));
  
  // Custom update handler for private repos
  handlePrivateRepoUpdate(info);
});

autoUpdater.on('update-not-available', (info) => {
  console.log('❌ Update not available. Current version:', app.getVersion());
  console.log('Remote version:', info ? info.version : 'unknown');
  console.log('Update info:', JSON.stringify(info, null, 2));
});

autoUpdater.on('error', (err) => {
  console.error('🚨 Standard auto-updater error (expected for private repos):', err.message);
  
  // Always use custom private repo handler for any standard updater error
  console.log('🔄 Fallback to custom private repo handler...');
  handlePrivateRepoUpdate();
});

// Track download state to prevent multiple downloads
let isDownloading = false;
let downloadStartTime = null;

// Custom update handler for private repositories
async function handlePrivateRepoUpdate(updateInfo = null) {
  try {
    console.log('🔍 handlePrivateRepoUpdate called');
    console.log(' GitHub token:', githubToken ? 'Present' : 'Missing');
    
    // Reset download state when checking for updates
    if (!isDownloading) {
      console.log('🔄 Resetting download state for new check');
    }
    
    // Check for updates in public repo
    const response = await customFetch('https://api.github.com/repos/easyerpneeko/fagotto-updates/releases/latest', {
      headers: {
        'Accept': 'application/vnd.github.v3+json',
        'User-Agent': 'fagotto-erp-app'
      }
    });
    
    if (!response.ok) {
      console.error('❌ API Response error:', response.status, response.statusText);
      throw new Error(`Error al obtener release: ${response.status} - ${response.statusText}`);
    }
    
    const release = JSON.parse(response.data);
    const currentVersion = app.getVersion();
    
    console.log('📊 Current version:', currentVersion);
    console.log('🆕 Latest version:', release.tag_name);
    
    if (release.tag_name !== `v${currentVersion}`) {
      console.log('✅ Update available from private repo:', release.tag_name);
      
      // Send update available event to renderer
      if (mainWindow) {
        mainWindow.webContents.send('update_available', {
          version: release.tag_name.replace('v', ''),
          releaseNotes: release.body,
          releaseName: release.name,
          releaseDate: release.published_at
        });
      }
      
      // Start automatic download only if not already downloading
      if (!isDownloading) {
        startAutomaticDownload(release);
      } else {
        console.log('⏳ Download already in progress, skipping...');
      }
    } else {
      console.log('✅ Ya tienes la última versión');
      if (mainWindow) {
        mainWindow.webContents.send('update_not_available', {
          currentVersion: currentVersion,
          latestVersion: release.tag_name.replace('v', '')
        });
      }
    }
  } catch (error) {
    console.error('❌ Error checking private repo updates:', error);
    if (mainWindow) {
      mainWindow.webContents.send('update_error', {
        error: `Error verificando actualizaciones: ${error.message}`
      });
    }
  }
}

// Download update with progress for private repos
async function startAutomaticDownload(release) {
  if (isDownloading) {
    console.log('⏳ Download already in progress, skipping...');
    return;
  }
  
  isDownloading = true;
  downloadStartTime = Date.now();
  
  const maxRetries = 3;
  let retryCount = 0;
  
  while (retryCount < maxRetries) {
    try {
      console.log(`📥 Starting automatic download (attempt ${retryCount + 1}/${maxRetries})...`);
      
      // Find the .exe file
      const exeAsset = release.assets.find(asset => 
        asset.name.endsWith('.exe') && 
        asset.name.includes('fagotto-erp-app-setup')
      );
      
      if (!exeAsset) {
        throw new Error('No se encontró el archivo .exe');
      }
      
      console.log('📊 Found asset:', exeAsset.name, 'Size:', exeAsset.size);
      
      // Show download starting message
      if (mainWindow) {
        mainWindow.webContents.send('download_starting', {
          fileName: exeAsset.name,
          fileSize: exeAsset.size,
          version: release.tag_name.replace('v', '')
        });
        
        // Send initial progress
        mainWindow.webContents.send('download_progress', {
          percent: 0,
          downloadedBytes: 0,
          totalBytes: exeAsset.size,
          speed: 0
        });
      }
      
      // For public repos, use direct download URL
      const downloadUrl = exeAsset.browser_download_url;
      console.log('📥 Downloading from public repo:', downloadUrl);
      
      const response = await customFetchBinary(downloadUrl);
      
      if (!response.ok) {
        console.error('❌ Download response:', response.status, response.statusText);
        console.error('❌ Response headers:', response.headers);
        console.error('❌ Download URL tried:', downloadUrl);
        throw new Error(`Error al descargar: ${response.status}`);
      }
      
      // Save file to temp directory
      const tempDir = os.tmpdir();
      const filePath = path.join(tempDir, exeAsset.name);
      
      // Download with progress tracking
      await downloadWithProgress(response, filePath, exeAsset.size);
      
      console.log('✅ Download completed:', filePath);
      
      // Send download completed event
      if (mainWindow) {
        mainWindow.webContents.send('download_completed', {
          filePath: filePath,
          fileName: exeAsset.name,
          version: release.tag_name.replace('v', '')
        });
      }
      
      // Reset download state
      isDownloading = false;
      downloadStartTime = null;
      
      return; // Success, exit retry loop
      
    } catch (error) {
      console.error(`❌ Error downloading update (attempt ${retryCount + 1}):`, error);
      retryCount++;
      
      if (retryCount < maxRetries) {
        console.log(`🔄 Retrying in 2 seconds... (${retryCount}/${maxRetries})`);
        await new Promise(resolve => setTimeout(resolve, 2000));
      } else {
        console.error('❌ Max retries reached, giving up');
        
        // Reset download state on failure
        isDownloading = false;
        downloadStartTime = null;
        
        if (mainWindow) {
          mainWindow.webContents.send('download_error', {
            error: error.message
          });
        }
      }
    }
  }
}

autoUpdater.on('download-progress', (progressObj) => {
  let log_message = "Download speed: " + progressObj.bytesPerSecond;
  log_message = log_message + ' - Downloaded ' + progressObj.percent + '%';
  log_message = log_message + ' (' + progressObj.transferred + "/" + progressObj.total + ')';
  console.log('📥 ' + log_message);
  
  if (mainWindow) {
    mainWindow.webContents.send('update_progress', progressObj);
  }
});

autoUpdater.on('update-downloaded', (info) => {
  console.log('✅ Update downloaded:', info.version);
  console.log('Downloaded info:', JSON.stringify(info, null, 2));
  mainWindow.webContents.send('update_downloaded', info);
});

// IPC handlers
ipcMain.on('restart_app', () => {
  console.log('Restart app requested');
  autoUpdater.quitAndInstall();
});

ipcMain.on('check_for_updates', () => {
  console.log('Manual update check requested - using custom handler');
  handlePrivateRepoUpdate();
});

ipcMain.on('install_update', (event, filePath) => {
  console.log('Install update requested:', filePath);
  
  // Execute the installer
  const { spawn } = require('child_process');
  
  try {
    // Close the current app and run installer
    const installer = spawn(filePath, [], { 
      detached: true,
      stdio: 'ignore'
    });
    
    installer.unref();
    
    // Close the app after a short delay
    setTimeout(() => {
      app.quit();
    }, 1000);
    
  } catch (error) {
    console.error('Error installing update:', error);
    event.reply('install_error', error.message);
  }
});

// Cancel download handler
ipcMain.on('cancel_download', () => {
  console.log('🛑 Download cancelled by user');
  isDownloading = false;
  downloadStartTime = null;
  
  if (mainWindow) {
    mainWindow.webContents.send('download_cancelled');
  }
});

// Force check for updates (reset state)
ipcMain.on('force_check_updates', () => {
  console.log('🔄 Force check for updates requested - resetting state');
  isDownloading = false;
  downloadStartTime = null;
  handlePrivateRepoUpdate();
});