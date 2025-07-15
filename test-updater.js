const { autoUpdater } = require('electron-updater');

// Simulate checking for updates
console.log('Testing auto-updater configuration...');

// Enable logging
autoUpdater.logger = require('electron-log');
autoUpdater.logger.transports.file.level = 'info';

// Configure auto-updater
autoUpdater.setFeedURL({
  provider: "github",
  owner: "easyerpneeko",
  repo: "fagotto-updates",
  private: true,
  token: "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"
});

autoUpdater.on('checking-for-update', () => {
  console.log('Checking for update...');
});

autoUpdater.on('update-available', (info) => {
  console.log('Update available:', info);
});

autoUpdater.on('update-not-available', (info) => {
  console.log('Update not available:', info);
});

autoUpdater.on('error', (err) => {
  console.error('Error in auto-updater:', err);
});

autoUpdater.on('download-progress', (progressObj) => {
  let log_message = "Download speed: " + progressObj.bytesPerSecond;
  log_message = log_message + ' - Downloaded ' + progressObj.percent + '%';
  log_message = log_message + ' (' + progressObj.transferred + "/" + progressObj.total + ')';
  console.log(log_message);
});

autoUpdater.on('update-downloaded', (info) => {
  console.log('Update downloaded:', info);
});

// Check for updates
autoUpdater.checkForUpdates();
