// Importaciones
const printer = require("pdf-to-printer");
const fs = require('fs');
const exec = require('child_process').exec;


// Helpers
import ConfigHelper from './ConfigHelper.js';
//import IMNodePrinter from './IMNodePrinter.js';

// Constantes
const optionsDefault = /*{ win32: ['-print-settings "paper=statement"'] }*/'';
const fileNameDefault = './sell.pdf';

import NodePdfPrinter from 'node-pdf-printer'
import path from 'path'

var { BrowserWindow, dialog } = require('electron').remote;
const PDFWindow = require('electron-pdf-window')
const app = require('electron').remote.app
//To get the app handle for app.getAppPath().
import { rootPath } from 'electron-root-path';

export default class {

  // Funcion para descarga un xlsx
  static async downloadExcel(data) {
    const options = { defaultPath: app.getPath('downloads') + '/documento.xlsx' };
    // Path del archivo
    let path = await dialog.showSaveDialog(null, options);

    fs.writeFile(path.filePath, data, 'base64', (err) => {
      // En caso de error!
      if(err) { console.log(err); return false; }

      // Si todo sale bien
      console.log('[Printer] XLSX Creado con exito!');
    });
  }

  // Funcion para imprimir un PDF en base 64
  static printBase64(data, options = null) {

    const filename = fileNameDefault;
    // Creamos el archivo
    fs.writeFile(filename, data, 'base64', (err) => {

      // En caso de error!
      if(err) { console.log(err); return false; }

      // Si todo sale bien
      console.log('[Printer] PDF Creado con exito! ', filename);

      // Llamamos al imprimir!
      return this.print(filename, options);

    });

  }

  // Funcion para imprimir
  static print(filename, options = null) {

    // Obtener metodo
    const method = this.getMethod();
    // Obtener opciones
    options = this.getOptions(options);

    console.log('[Printer] Configuracion: ', options);
    console.log('[Printer] Metodo: ', method);

    try {

      switch (method) {
          /**/

          case 'printto.exe':

            //https://www.7-pdf.com/products/pdf-printer/documentation/command-line-printing-to-pdf
            console.log('[Printer] To the Printer: ', 'print.bat ' + options);

            // Ejecutar comando
            exec('print.bat ' + options, { windowsHide: true },
               (e) => { if (e) { throw '[Printer] Print to Error: ' + e; } else {
                  this.deleteFile(filename);
                } }
            );

          break;

          case 'PDFtoPrinter.exe-native':

            const strPDF = 'PDFtoPrinter.exe ' + filename + ' ' + options;
            console.log('[Printer] To the Printer: ', strPDF);

            // Ejecutar comando
            exec(strPDF, { windowsHide: true },
               (e) => { if (e) { throw '[Printer] PtP-Native Error: ' + e; } else {
                  this.deleteFile(filename);
                } }
            );

          break;

          case 'PDFtoPrinter.exe-native-old':

            const strPDFOld = 'PDFtoPrinter.exe ' + filename + ' ' + options;
            console.log('[Printer] To the Printer: ', strPDF);

            // Ejecutar comando/**/
            exec(strPDFOld, { windowsHide: true },
               (e) => { if (e) throw '[Printer] PtP-Native Error: ' + e; }
            );

          break;

        case 'PDFtoPrinter.exe':

          const array = [path.resolve(filename)];
          NodePdfPrinter.printFiles(array);

        break;

        case 'node-printer-imagemagick-emf-pdf':

          // Impresion de PDF
          //# ImageMagick Node Printer Helper
          //IMNodePrinter.PDFPrint(filename, options);

        break;

        default: //Default

          printer.print(filename, options)
            .then(() => {

              this.deleteFile(filename);
              return true;

            })
            .catch(error => {
              console.log('[Printer] Error: ');
              console.log(error);
            });

        break;

      }


    } catch (error) {

      console.log('[Printer] Error: ', error);
      return false;

    }

  }

  // Metodo para borrar archivo
  static deleteFile(filename) {
    fs.unlink(filename,(error) => {
      if (error) {
        console.log('[Printer] Error al borrar archivo: ',error);
      }else{
        console.log('[Printer] Archivo borrado con exito!.');
      }
    });
  }

  // Es JSON ?
  static isJson(str) {
      try {
          JSON.parse(str);
      } catch (e) {
          return false;
      }
      return true;
  }

  // Obtener opciones
  static getOptions(options = null) {
    if (!options) {
      options = optionsDefault;
      var optionsPrinterBackend = ConfigHelper.ConfStr('enverioments.settings_printer');

      console.log('[Meta-Printer] Backend Options:',optionsPrinterBackend);

      // Si el String es un JSON, se transforma a JSON
      if (this.isJson(optionsPrinterBackend))
        optionsPrinterBackend = JSON.parse(optionsPrinterBackend);

      if (optionsPrinterBackend) options = optionsPrinterBackend;
    }
    return options;
  }

  // Obtener metodo de impresion
  static getMethod(method = null) {
    if (!method) {
      method = 'default';
      const methodBackend = ConfigHelper.ConfStr('enverioments.settings_printer_method');

      console.log('[Meta-Printer] Backend Method:',methodBackend);
      if (methodBackend) method = methodBackend;
    }
    return method;
  }


  static viewBase64(data) {

    const filename = app.getAppPath()+'\sell.pdf';

    //const filename = 'sell.pdf';
    const location = path.join(filename);

    // Creamos el archivo
    fs.writeFile(location, data, 'base64', (err) => {

      // En caso de error!
      if(err) { console.log(err); return false; }

      // Si todo sale bien
      console.log('[Visualizer PDF] PDF Creado con exito! ', location);

      // Llamamos al visualizador!
      this.visualize(location);
      return true;
/**/
    });

  }

  // Previsualizar archivo PDF Base 64
  static async visualize(location) {

    try {

      const win = new BrowserWindow({ width: 800, height: 600 });
      await PDFWindow.addSupport(win);
      await win.loadURL('file://'+location)
      console.log('file://'+location);
      //this.deleteFile(location);
      return true;

    } catch (error) {

      console.log('[PDF Visualizer] Error: ', error);
      return false;

    }

  }

}
