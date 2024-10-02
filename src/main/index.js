import { app, BrowserWindow, ipcMain } from 'electron';
// const { autoUpdater } = require('electron-updater');
import { autoUpdater } from "electron-updater"

import ConfigHelper from '../renderer/helpers/ConfigHelper.js';
import Connection from '../renderer/helpers/Connection.js';
import BaseUrl from '../renderer/helpers/baseUrl.js';
// const fs = require('fs');
import fs from 'fs';
import path from 'path';
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

// autoUpdater.logger = log;
// autoUpdater.logger.transports.file.level = 'info';
// log.info('App starting...');

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

// const tokenFilePath = path.resolve(__dirname, '..', '..', 'gh_token.json');
// const configFile = fs.readFileSync(tokenFilePath, 'utf8');
// const config = JSON.parse(configFile);

// console.log('GH_TOKEN',config.githubToken);

autoUpdater.setFeedURL({
  provider: "github",
  owner: "orlandodaniel",
  repo: "FRONT-PROJECT-VUE-DEV",
  private: true,
  token: "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"
});

app.on('ready', ()=>{
  initApp()
  console.log('Cheking updates...');
  autoUpdater.checkForUpdates();
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

autoUpdater.on('update-available', () => {
  console.log('update available in index.js');
  mainWindow.webContents.send('update_available');
});

autoUpdater.on('update-downloaded', () => {
  console.log('update downloaded in index.js');
  mainWindow.webContents.send('update_downloaded');
});

ipcMain.on('restart_app', () => {
  console.log('Restart app');
  autoUpdater.quitAndInstall();
});

autoUpdater.on('error', (err) => {
  mainWindow.webContents.send('Error in auto-updater. ' + err);
})