const { app, BrowserWindow, Menu, Tray, ipcMain, dialog } = require('electron');
const path = require('path');
const { spawn } = require('child_process');
const findFreePort = require('find-free-port');

let mainWindow;
let phpProcess;
let phpPort = 8080;
let tray;

// Configuración de la aplicación
const APP_CONFIG = {
  name: 'TOTEM Fagotto',
  version: '1.0.0',
  defaultPort: 8080
};

// Ruta al ejecutable de PHP (portable)
const getPhpPath = () => {
  if (process.platform === 'win32') {
    return path.join(process.resourcesPath, 'php', 'php.exe');
  }
  // Para Mac/Linux usar PHP del sistema
  return 'php';
};

// Función para iniciar servidor PHP
async function startPhpServer() {
  try {
    // Buscar puerto libre
    const [freePort] = await findFreePort(APP_CONFIG.defaultPort);
    phpPort = freePort;

    const phpPath = getPhpPath();
    const documentRoot = app.isPackaged 
      ? process.resourcesPath 
      : __dirname.replace('electron', '');

    console.log('🚀 Iniciando servidor PHP...');
    console.log('📁 Document Root:', documentRoot);
    console.log('🔌 Puerto:', phpPort);

    phpProcess = spawn(phpPath, [
      '-S',
      `localhost:${phpPort}`,
      '-t',
      documentRoot
    ]);

    phpProcess.stdout.on('data', (data) => {
      console.log(`[PHP] ${data}`);
    });

    phpProcess.stderr.on('data', (data) => {
      console.error(`[PHP Error] ${data}`);
    });

    phpProcess.on('close', (code) => {
      console.log(`Servidor PHP cerrado con código ${code}`);
    });

    // Esperar un momento para que el servidor inicie
    await new Promise(resolve => setTimeout(resolve, 1000));
    console.log('✅ Servidor PHP iniciado correctamente');
    
    return true;
  } catch (error) {
    console.error('❌ Error al iniciar servidor PHP:', error);
    dialog.showErrorBox(
      'Error al iniciar servidor',
      `No se pudo iniciar el servidor PHP:\n\n${error.message}\n\nVerifica que PHP esté instalado correctamente.`
    );
    return false;
  }
}

// Función para detener servidor PHP
function stopPhpServer() {
  if (phpProcess) {
    console.log('🛑 Deteniendo servidor PHP...');
    phpProcess.kill();
    phpProcess = null;
  }
}

// Crear ventana principal
function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1920,
    height: 1080,
    minWidth: 1024,
    minHeight: 768,
    fullscreen: true,
    frame: false, // Sin barra de título para look tipo kiosko
    autoHideMenuBar: true,
    backgroundColor: '#f5f5f5',
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      preload: path.join(__dirname, 'preload.js')
    },
    icon: path.join(__dirname, 'assets', 'icon.png')
  });

  // Cargar la aplicación
  const url = `http://localhost:${phpPort}/totem.html`;
  console.log('🌐 Cargando URL:', url);
  
  mainWindow.loadURL(url);

  // Recargar si hay error de conexión
  mainWindow.webContents.on('did-fail-load', () => {
    setTimeout(() => {
      mainWindow.loadURL(url);
    }, 1000);
  });

  // DevTools en desarrollo
  if (!app.isPackaged) {
    mainWindow.webContents.openDevTools();
  }

  mainWindow.on('closed', () => {
    mainWindow = null;
  });

  // Pantalla de carga mientras inicia PHP
  mainWindow.webContents.on('did-finish-load', () => {
    mainWindow.webContents.executeJavaScript(`
      console.log('✅ TOTEM Fagotto iniciado correctamente');
      console.log('🔌 Puerto PHP: ${phpPort}');
    `);
  });
}

// Crear tray icon
function createTray() {
  const iconPath = path.join(__dirname, 'assets', 'tray-icon.png');
  tray = new Tray(iconPath);

  const contextMenu = Menu.buildFromTemplate([
    {
      label: 'TOTEM Fagotto',
      enabled: false
    },
    { type: 'separator' },
    {
      label: '🏠 Mostrar Ventana',
      click: () => {
        if (mainWindow) {
          mainWindow.show();
          mainWindow.focus();
        }
      }
    },
    {
      label: '🔄 Recargar',
      click: () => {
        if (mainWindow) {
          mainWindow.reload();
        }
      }
    },
    { type: 'separator' },
    {
      label: '🔧 Configuración',
      click: () => {
        const configUrl = `http://localhost:${phpPort}/config.php`;
        require('electron').shell.openExternal(configUrl);
      }
    },
    {
      label: '📊 Ver Terminal',
      click: () => {
        const terminalUrl = `http://localhost:${phpPort}/ver-mi-terminal.php`;
        require('electron').shell.openExternal(terminalUrl);
      }
    },
    { type: 'separator' },
    {
      label: '🔍 DevTools',
      visible: !app.isPackaged,
      click: () => {
        if (mainWindow) {
          mainWindow.webContents.toggleDevTools();
        }
      }
    },
    { type: 'separator' },
    {
      label: '❌ Salir',
      click: () => {
        app.quit();
      }
    }
  ]);

  tray.setToolTip('TOTEM Fagotto - Sistema de Pagos');
  tray.setContextMenu(contextMenu);

  tray.on('click', () => {
    if (mainWindow) {
      mainWindow.isVisible() ? mainWindow.hide() : mainWindow.show();
    }
  });
}

// Eventos IPC
ipcMain.handle('get-app-info', () => {
  return {
    name: APP_CONFIG.name,
    version: APP_CONFIG.version,
    phpPort: phpPort
  };
});

ipcMain.handle('quit-app', () => {
  app.quit();
});

ipcMain.handle('minimize-app', () => {
  if (mainWindow) {
    mainWindow.minimize();
  }
});

ipcMain.handle('toggle-fullscreen', () => {
  if (mainWindow) {
    mainWindow.setFullScreen(!mainWindow.isFullScreen());
  }
});

// Inicialización de la app
app.whenReady().then(async () => {
  console.log('🚀 Iniciando TOTEM Fagotto...');
  
  // Iniciar servidor PHP
  const phpStarted = await startPhpServer();
  
  if (phpStarted) {
    // Crear ventana principal
    createWindow();
    
    // Crear tray icon
    createTray();
    
    console.log('✅ Aplicación iniciada correctamente');
  } else {
    app.quit();
  }
});

// Comportamiento en macOS
app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('activate', () => {
  if (BrowserWindow.getAllWindows().length === 0) {
    createWindow();
  }
});

// Limpieza al cerrar
app.on('before-quit', () => {
  console.log('👋 Cerrando TOTEM Fagotto...');
  stopPhpServer();
});

app.on('will-quit', () => {
  stopPhpServer();
});

// Manejo de errores
process.on('uncaughtException', (error) => {
  console.error('❌ Error no capturado:', error);
});
