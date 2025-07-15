import { app, BrowserWindow, ipcMain } from 'electron';
// const { autoUpdater } = require('electron-updater');
import { autoUpdater } from "electron-updater"

import ConfigHelper from '../renderer/helpers/ConfigHelper.js';
import Connection from '../renderer/helpers/Connection.js';
import BaseUrl from '../renderer/helpers/baseUrl.js';
// const fs = require('fs');
import fs from 'fs';
import path from 'path';
import os from 'os';
//const fetch = require('electron-fetch');
import fetch from 'electron-fetch';
import { log } from 'console';

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
    webPreferences: { nodeIntegration: true, contextIsolation: false, enableRemoteModule: true }
  })

  mainWindow.loadURL(winURL)

  mainWindow.on('closed', () => {
    mainWindow = null
  })

  // Set The Menu to the Main Window
  mainWindow.setMenuBarVisibility(false)


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
  ConfigHelper.InitializeAplication(fetch);
  createMainWindow();
}

// Read GitHub token from file
const tokenFilePath = path.resolve(__dirname, '..', '..', 'gh_token.json');
let githubToken = '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'; // Hardcoded for now

console.log('🔍 Looking for token file at:', tokenFilePath);
console.log('🔍 File exists:', fs.existsSync(tokenFilePath));

try {
  const configFile = fs.readFileSync(tokenFilePath, 'utf8');
  console.log('📄 Config file content:', configFile);
  const config = JSON.parse(configFile);
  githubToken = config.token;
  console.log('✅ GitHub token loaded successfully from file');
} catch (error) {
  console.error('❌ Error loading GitHub token from file, using hardcoded:', error.message);
  console.log('🔑 Using hardcoded token:', githubToken ? 'Present' : 'Missing');
}

// Configure auto-updater
console.log('Standard auto-updater DISABLED - using custom handler');

// Standard auto-updater disabled for private repos
// autoUpdater.setFeedURL({
//   provider: "github",
//   owner: "easyerpneeko",
//   repo: "fagotto-updates",
//   private: true,
//   token: githubToken,
// });

console.log('Standard auto-updater DISABLED');
// console.log('Feed URL:', autoUpdater.getFeedURL());

// Set update check interval (check every 5 minutes)
// autoUpdater.checkForUpdatesAndNotify(); // Disabled for private repos

app.on('ready', () => {
  initApp();
  
  // Check for updates using custom handler (always check)
  console.log('🔄 Starting custom update check for private repo...');
  console.log('NODE_ENV:', process.env.NODE_ENV);
  console.log('App is ready, scheduling update check...');
  setTimeout(() => {
    console.log('⏰ Timeout reached, calling handlePrivateRepoUpdate...');
    handlePrivateRepoUpdate();
  }, 3000); // Wait 3 seconds before checking for updates
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

// Handle update download request from renderer
ipcMain.on('download_update', async (event, data) => {
  try {
    console.log('📥 Download update requested:', data);
    
    // Get latest release info
    const response = await fetch('https://api.github.com/repos/easyerpneeko/fagotto-updates/releases/latest', {
      headers: {
        'Authorization': `token ${githubToken}`,
        'Accept': 'application/vnd.github.v3+json'
      }
    });
    
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    
    const release = await response.json();
    
    // Find the .exe file
    const exeAsset = release.assets.find(asset => 
      asset.name.endsWith('.exe') && 
      asset.name.includes('fagotto-erp-app-setup')
    );
    
    if (!exeAsset) {
      throw new Error('No se encontró el archivo .exe');
    }
    
    console.log('📊 Manual download - Asset info:', {
      id: exeAsset.id,
      name: exeAsset.name,
      size: exeAsset.size,
      url: exeAsset.url
    });
    
    // Start download with authenticated request
    await downloadFileWithAuth(exeAsset, release.tag_name.replace('v', ''));
    
  } catch (error) {
    console.error('❌ Error in download_update handler:', error);
    event.sender.send('download_error', { error: error.message });
  }
});

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
      
      // Try browser_download_url first for private repos
      let downloadUrl = asset.browser_download_url;
      let headers = {
        'Authorization': `token ${githubToken}`,
        'Accept': 'application/octet-stream'
      };
      
      console.log('📥 Trying browser_download_url:', downloadUrl);
      
      let response = await fetch(downloadUrl, { headers });
      
      // If browser_download_url fails, try assets API
      if (!response.ok) {
        console.log('❌ browser_download_url failed, trying assets API...');
        downloadUrl = `https://api.github.com/repos/easyerpneeko/fagotto-updates/releases/assets/${asset.id}`;
        console.log('📥 Trying assets API URL:', downloadUrl);
        
        response = await fetch(downloadUrl, { headers });
      }
      
      if (!response.ok) {
        console.error('❌ Download response:', response.status, response.statusText);
        console.error('❌ Response headers:', Array.from(response.headers.entries()));
        throw new Error(`Download failed: ${response.status}`);
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
  return new Promise((resolve, reject) => {
    const writer = fs.createWriteStream(filePath);
    let downloadedSize = 0;
    
    response.body.on('data', (chunk) => {
      downloadedSize += chunk.length;
      writer.write(chunk);
      
      // Calculate and send progress
      const percent = totalSize > 0 ? Math.round((downloadedSize / totalSize) * 100) : 0;
      
      if (mainWindow) {
        mainWindow.webContents.send('download_progress', {
          percent: percent,
          downloadedBytes: downloadedSize,
          totalBytes: totalSize
        });
      }
      
      console.log(`📊 Progress: ${percent}% (${downloadedSize}/${totalSize} bytes)`);
    });
    
    response.body.on('end', () => {
      writer.end();
      console.log('✅ Download completed successfully');
      resolve();
    });
    
    response.body.on('error', (error) => {
      writer.destroy();
      reject(error);
    });
    
    writer.on('error', (error) => {
      reject(error);
    });
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
  console.error('🚨 Error in auto-updater:', err);
  console.error('Error details:', JSON.stringify(err, Object.getOwnPropertyNames(err), 2));
  
  // Skip standard auto-updater errors for private repos
  if (err.message && err.message.includes('404')) {
    console.log('🔄 404 Error detected - Using custom private repo handler...');
    handlePrivateRepoUpdate();
  } else {
    // Send error to renderer for other types of errors
    if (mainWindow) {
      mainWindow.webContents.send('update_error', err.message);
    }
  }
});

// Custom update handler for private repositories
async function handlePrivateRepoUpdate(updateInfo = null) {
  try {
    console.log('🔍 handlePrivateRepoUpdate called');
    console.log('📂 Current directory:', process.cwd());
    console.log('🔑 GitHub token:', githubToken ? 'Present' : 'Missing');
    
    const response = await fetch('https://api.github.com/repos/easyerpneeko/fagotto-updates/releases/latest', {
      headers: {
        'Authorization': `token ${githubToken}`,
        'Accept': 'application/vnd.github.v3+json'
      }
    });
    
    if (!response.ok) {
      throw new Error(`Error al obtener release: ${response.status}`);
    }
    
    const release = await response.json();
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
      
      // Start automatic download
      startAutomaticDownload(release);
    } else {
      console.log('✅ Ya tienes la última versión');
    }
  } catch (error) {
    console.error('❌ Error checking private repo updates:', error);
  }
}

// Download update with progress for private repos
async function startAutomaticDownload(release) {
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
      }
      
      // Try browser_download_url first
      let downloadUrl = exeAsset.browser_download_url;
      let headers = {
        'Authorization': `token ${githubToken}`,
        'Accept': 'application/octet-stream'
      };
      
      console.log('📥 Trying browser_download_url:', downloadUrl);
      
      let response = await fetch(downloadUrl, { headers });
      
      // If browser_download_url fails, try assets API
      if (!response.ok) {
        console.log('❌ browser_download_url failed, trying assets API...');
        downloadUrl = `https://api.github.com/repos/easyerpneeko/fagotto-updates/releases/assets/${exeAsset.id}`;
        console.log('📥 Trying assets API URL:', downloadUrl);
        
        response = await fetch(downloadUrl, { headers });
      }
      
      if (!response.ok) {
        console.error('❌ Download response:', response.status, response.statusText);
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
      
      return; // Success, exit retry loop
      
    } catch (error) {
      console.error(`❌ Error downloading update (attempt ${retryCount + 1}):`, error);
      retryCount++;
      
      if (retryCount < maxRetries) {
        console.log(`🔄 Retrying in 2 seconds... (${retryCount}/${maxRetries})`);
        await new Promise(resolve => setTimeout(resolve, 2000));
      } else {
        console.error('❌ Max retries reached, giving up');
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