
var configData = { data: null };
///NO BORRAR NINGUN CONSOLE.LOG DE ACA
const fs = require('fs');

/*const electron = require('electron');
import fetch from 'electron-fetch';*/
const fetch = require('node-fetch');

import Connection from './Connection.js';
import EchoHelper from './EchoHelper.js';
import BaseUrl from './baseUrl.js';

import store from '../store';

export default class ConfigHelper {

  static isJson(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
  }
  //traer el user del json de aplication.json
  static InitializeAplication(permFetch = null) {
    console.log('Initiliazate App in ConfigHelper.JS')
    ConfigHelper.readAppFile((err, data) => {

      if(err){
        console.log('Error al leer el JSON de la aplicacion.');
        return false;
      }

      var misCabeceras = {};
      var datos = JSON.parse(data);

      if (!datos || !datos.Serial) {
        console.log('Error al leer serial en JSON de la aplicacion.')
        return false;
      }

      //Configuro la Config interna del Helper con los datos actuales
      ConfigHelper.ConfigHandler(false, datos);
      /**/
      //Establezco los header temporal de la App Key con los datos del serial
      misCabeceras['App-Key'] = datos.Serial;

      let url = BaseUrl.getUrl('api/getApp');
      var miInit = { method: 'GET', headers: misCabeceras, mode: 'cors', cache: 'default' };

      console.log('...Actualizando JSON de aplicacion...')

      //alert(url);

      if (!permFetch) permFetch = fetch;

      permFetch(url,miInit).then(async function(res) {
        if (res.status == 200) {
          var response = await res.clone().json();
          //console.log('Respuesta ', response);
          ConfigHelper.writeAppFile(response);
          //Actualizo la config nueva del helper con nuevos datos
          ConfigHelper.ConfigHandler(false, response);
        }
        if (res.status == 404) {
          console.log('Codigo de aplicacion no encontrado u invalido.');
          fs.unlink('aplication.json',(err) => {
            if (err) return;
            console.log('Aplication.json eliminado correctamente');
          });
          fs.unlink('authorization.json',(err) => {
            if (err) return;
            console.log('Authorization.json eliminado correctamente');
          });
        }
      })
      .catch(function(error) {
        console.log('Error al hacer fet-ch de aplication.json');
        console.log(error);
        //fs.unlink('aplication.json');
      });

    });
  }

  static readAppFile(callback,name='aplication.json',) {
    return fs.readFile(name, 'utf-8', (err, data) => {
      return callback(err, data);
    });
  }

  static writeAppFile(dataToWrite) {
    ConfigHelper.writeFile(dataToWrite, 'aplication.json', (err) => {
      if(err){
        console.log('Se intento escribir el archivo de aplication.json, pero ocurrio un error');
        console.log(err);
        return;
      }
      console.log('Se ha escrito el archivo aplication.json exitosamente.');
    });
  }

  static writeFile(dataToWrite, name = 'aplication.json', cb = null) {

    const strToSave = JSON.stringify(dataToWrite);

    if (name === 'aplication.json') {
      if (!ConfigHelper.isJson(strToSave)) {
        console.log('Se intento escribir un JSON erroneo en el aplication.JSON, la accion fue abortada.');
        return false;
      }
    }

    if (!cb) return fs.writeFile(name, strToSave, () => {
      console.log('Se ha escrito el archivo ' + name);
    });
    return fs.writeFile(name, JSON.stringify(dataToWrite), cb);

  }

  static async ConfigReadFile(error = false) {
    var file = await fs.readFile('aplication.json', 'utf-8');
    if(!file){ return null; }
    //console.log('READING FILE CONFIG:');
    //console.log(file);
    return JSON.parse(data);
  }

  static async ConfigSet(error = false) {

    var configFile = await ConfigHelper.ConfigReadFile(true);
    if (!configFile) return false;

    configData.data = configFile;

    return true;

  }

  static ConfigHandler(error = false, jsonData = null, internal = true) {

    var inStr = (internal) ? 'interno' : 'externo';

    if (!jsonData) {
      console.log('Ejecutado ConfigHandler sin JSON. ' + inStr);
      fs.readFile('aplication.json', 'utf-8', (err, data) => {
        if(err){
          // this.$awn.alert(err);
          console.log('Error al leer JSON');
          return false;
        }
        var datosJSON = JSON.parse(data);
        configData.data = datosJSON;
        //Establezco el FillAppHelper con los datos del serial
        Connection.fillAppHeader(datosJSON.Serial);
        EchoHelper.fillAppHeader(datosJSON.Serial);
        return true;
      });
    }else{
      console.log('Ejecutado ConfigHandler con JSON. ' + inStr);
      configData.data = jsonData;
      //Establezco el FillAppHelper con los datos del serial
      Connection.fillAppHeader(jsonData.Serial);
      EchoHelper.fillAppHeader(jsonData.Serial);
    }

    return true;

  }

  static Config(error = false) {
    if(!configData || !configData.data){ return null; }
    return configData.data;
  }

  static ConfModule(keyname,appConfig = null) {

    if (!appConfig)
      appConfig = ConfigHelper.Config();

    var modules = appConfig.Modules;
    /*console.log('MODULOS');
    console.log(modules);return false;*/

    var module = modules.find((item) => { return item.key == keyname; });

    if (!module) module = null;

    return module;

  }

  static ConfSubmodule(module, keyname) {
    if (!module || !module.sub) {
      // Handle the case where `module` or `module.sub` is null or undefined
      console.error("Module or submodule is null/undefined");
      return null; // Or return a default value or throw an error
    }  

    var submodules = module.sub;

    var submodule = submodules.find((item) => { return item.key == keyname; });

    if (!submodule) submodule = null;

    return submodule;

  }

  static ConfSetting(module, keyname) {
    var settings = module.settings;

    var setting = settings.find((item) => { return item.key == keyname; });

    if (!setting) setting = null;

    if (!setting || typeof setting['active'] === 'undefined') return setting;

    return setting['active'];

  }

  static ConfArrModule(strArray,module) {

    var arrayLenght = strArray.length;

    if (strArray[0] == 'submodulo' || strArray[0] == 'submodules' || strArray[0] == 'sub' || strArray[0] == 'modulo' || strArray[0] == 'modules' || strArray[0] == 'submodulos') strArray[0] = 'submodule';
    if (strArray[0] == 'ajuste' || strArray[0] == 'ajustes' || strArray[0] == 'setting') strArray[0] = 'settings';

    switch (strArray[0]) {
      case 'submodule':
        if (arrayLenght <= 1) return module['sub'];
        var submodule = ConfigHelper.ConfSubmodule(module, strArray[1]);
        if (arrayLenght <= 2) return submodule;
        return ConfigHelper.ConfArrModule(ConfigHelper.substr_array(strArray, 2),submodule);
      break;
      case 'settings':
        if (arrayLenght <= 1) return module['settings'];
        if(module){
          var setting = ConfigHelper.ConfSetting(module, strArray[1]);
          if (arrayLenght <= 2) return setting;
          return setting;
        }
        return false;
      break;
    }

    return false;

  }

   static ConfEnv(keyname, appConfig = null) {


        if (!appConfig)
          appConfig = ConfigHelper.Config();

        var envs = appConfig.Entorno;

        if (!envs[keyname]) return null;
        if (!envs[keyname]['value']) return null;
        var envt = envs[keyname]['value'];

        if (!envt) envt = null;

        return envt;

  }

  static ConfStr(str = null, config = null) {

    if (!str) return false;
    var strArray = str.split('.');
    var arrayLenght = strArray.length;

    if (!config) config = ConfigHelper.Config(true);

    if (!config) return null;


    if (strArray[0] == 'modulo' || strArray[0] == 'modules' || strArray[0] == 'modulos') strArray[0] = 'module';
    if (strArray[0] == 'settings' || strArray[0] == 'enverioments') strArray[0] = 'envs';
    if (strArray[0] == 'nombre') strArray[0] = 'name';

    switch (strArray[0]) {
      case 'module':
        if (arrayLenght <= 1) return false;
        var module = ConfigHelper.ConfModule(strArray[1],config);
        if (arrayLenght <= 2) return module;

        return ConfigHelper.ConfArrModule(ConfigHelper.substr_array(strArray, 2),module);
      break;
      case 'envs':
        if (arrayLenght <= 1) return false;
        var env = ConfigHelper.ConfEnv(strArray[1], config);
        if (arrayLenght <= 2) return env;
        return env;
        case 'name':

          var config = ConfigHelper.Config();

          if (!config) return null;
          return config.name_public;
        break;
      break;
    }

    return false;

  }

  static substr_array(array, count = 1)
  {
      for (var i=0; i < count; i++)
        if (typeof array[0] !== 'undefined') array.shift(0);
      return array;
  }

  static HavePermission(permissionNeed = null, config = null) {

    if (!store) {
      console.log('Error al leer store en helper de have-permission.');
      return false;
    }

    if (!store.state) {
      console.log('Error al leer store.state en helper de have-permission.');
      return false;
    }

    if (!store.state.main) {
      console.log('Error al leer store.state.main en helper de have-permission.');
      return false;
    }

    if (!store.state.main.user) {
      console.log('Error al leer store.state.main.user en helper de have-permission.');
      return false;
    }

    if (!store.state.main.user.role) {
      console.log('Error al leer store.state.main.user.role en helper de have-permission.');
      return false;
    }

    const userRoleID = store.state.main.user.role;

    if (!config) config = ConfigHelper.Config(true);
    if (!config) return null;
    if (!config.TypeUsers) return null;
    const userRole = config.TypeUsers.find((type) => type.id === userRoleID);

    const permissions = (typeof userRole.permission === 'string' && userRole.permission !== null) ? JSON.parse(userRole.permission) : userRole.permission;

    //Si es null entonces es que tiene todos los permisos
    if (permissions === null || permissions === 'null') return true;

    //Si es vacio es que no tiene permisos
    if (permissions.length <= 0) return false;

    for (var permission of permissions)
      if (permission === permissionNeed) return true;

    return false;

  }

}
