import qs from "qs";

var headerAuth = {};

//import fetch from 'electron-fetch';
//const fetch = require('node-fetch');
const Request = require('node-fetch').Request;
//console.log(Request)

export default class Connection {

  static fillHeaders(token){
    headerAuth['authorization'] = 'Bearer ' + token;
  }

  static desfillHeaders(){
    headerAuth['authorization'] = '';
  }

  static fillAppHeader(token){
    console.log('Datos de Serial establecidos en App-Key ', token)
    headerAuth['App-Key'] = token;
  }
  static desFillAppHeader(){
    headerAuth['App-Key'] = '';
  }

  static isJson(item) {
    item = typeof item !== "string" ? JSON.stringify(item) : item;

    try { item = JSON.parse(item); } catch (e) { return false; }

    if (typeof item === "object" && item !== null) return true;

    return false;
  }

  static isJsonString(item) {
    try {
      JSON.parse(item);
      return true;
    } catch (e) {
      return false;
    }
    return true;
  }

  static errorMessage(response){
    let error = 'Error';

    switch (response.status) {
      case 500: error = 'Error interno del servidor'; break;
    }

    if (response.data)
      error = (Connection.isJsonString(response.data)) ? JSON.parse(response.data) : response.data;


    return error;
  }

  static fetch(url, method = 'GET', data = null, headers = null, isFormData = false){

    console.log('Connection.Fetch TO', url);

    //isFormData = true;

    if (isFormData && (method != 'POST' || method != 'post' || method != 'GET' || method != 'get' )) {
      data.append('_method',method);
      method = 'post';
    }

    if (!headers) {
      headers = {};
    }
    
    // 🔧 IMPORTANTE: Para FormData, NO establecer Content-Type manualmente
    // El navegador lo establece automáticamente con el boundary correcto
    if (!isFormData && !headers['Content-Type']) {
      headers['Content-Type'] = 'application/json';
    }
    
    headers = { ...headers, ...headerAuth };



    let myInit = { method, headers, mode: 'cors', cache: 'default' };

    if (data) myInit.body = (isFormData) ? data : JSON.stringify(data);

    // 🔍 DEBUG: Ver exactamente qué se envía
    if (url.includes('stock-negocio') && method === 'post') {
      console.log('🔍 MYINIT COMPLETO:', myInit);
      console.log('🔍 HEADERS:', headers);
      console.log('🔍 BODY:', myInit.body);
    }

    //const myRequest = new Request(url, myInit);
    //console.log(myInit);
    /*myInit = {
      method: 'POST',
      body: {
        username: 'asdasd'
      },
      headers
    };*/

    // 🌐 TIMEOUT: Para internet lento, agregar timeout de 30 segundos
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 segundos
    myInit.signal = controller.signal;

    return fetch(url, myInit)
      .then(async res => {
        clearTimeout(timeoutId); // Limpiar timeout si responde a tiempo
        
        try {
          return { ok: res.ok, data: await res.clone().json(), status:res.status };
        } catch (e) {
          return { ok: false, data: await res.clone().text(), status:res.status };
        }
      })
      .catch(function(error) {
        clearTimeout(timeoutId);
        
        // 🌐 Detectar tipo de error de red
        if (error.name === 'AbortError') {
          console.error('⏰ TIMEOUT: La conexión tardó más de 30 segundos');
          throw 'Timeout: La conexión está muy lenta. Por favor verifica tu internet.';
        } else if (error.message.includes('fetch') || error.message.includes('network')) {
          console.error('🌐 ERROR DE RED:', error.message);
          throw 'Error de conexión. Verifica tu internet y vuelve a intentar.';
        }
        
        throw error.message;
      });

  }

  static async requestArray(method, url, token = null){
    console.log('Connection. Request Array To', url)
    let data = [];

    try {

      const request = await Connection.fetch(url, method);

      if (!request || !request.status || request.status != 200) return [];

      return request.data;

    } catch (e) {

      return [];

    }

  }

  static async request(method, url, formData = null, headers = null, isFormData = null, retries = 2 ) {

    // console.log('Connection. Request TO', url);

    /*formData = {
      name: 'adasd'
    };*/

    if (isFormData === null)
      isFormData = (formData instanceof FormData) ? true : false;


    //console.log('FORM Data', formData);
    //console.log('HEADER AUTH>>>>>>>>>>>>>>>>>>', Object.assign(headerAuth))

    if (!headers)
        headers = {
            //'Content-Type': (isFormData) ? 'multipart/form-data' : 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            //'x-requested-with': 'XMLHttpRequest',
            //'Access-Control-Allow-Origin': 'origin-list',
            //'Access-Control-Allow-Headers': 'X-Requested-With, x-requested-with, access-control-allow-headers, Access-Control-Allow-Headers',
            ...headerAuth
        }

    // 🔄 REINTENTOS: Para conexiones lentas, intentar hasta 3 veces
    for (let attempt = 0; attempt <= retries; attempt++) {
      try {
        if (attempt > 0) {
          console.log(`🔄 Reintento ${attempt}/${retries} para ${url}`);
          // Esperar antes de reintentar (backoff exponencial)
          await new Promise(resolve => setTimeout(resolve, 1000 * attempt));
        }
        
        const response = await Connection.fetch( url , method , formData, headers, isFormData );

        console.log("Response", response);

        if (response.ok)//response.status
          return { success: true, data: response.data, status: response.status };



        const errorResponse = Connection.errorMessage(response);

        let exitLogin = false;
        let aplicacionVencida = false;
        let aplicacionBloqueada = false;

        if (errorResponse.status
          && ( errorResponse.status == 'Token is Invalid' || errorResponse.status == 'Token is Expired' || errorResponse.status == 'Authorization Token not found' )
        ) { exitLogin = true; console.log("CONNECTION: TOKEN DE AUTORIZACION NO ENCONTRADO O EXPIRADO. "); }

        switch (response.status) {
          case 423://bloqueado
            aplicacionBloqueada = true;
          break;
          case 498://expirado
            aplicacionVencida = true;
          break;
        }

        return { success: false, data: errorResponse, exitLogin, aplicacionBloqueada, aplicacionVencida, status: response.status };

      } catch (e) {
        console.error(`❌ Error en intento ${attempt + 1}:`, e);
        
        // Si es el último intento, devolver el error
        if (attempt === retries) {
          // 🌐 Identificar tipo de error para mejor feedback
          let errorMessage = e;
          let isNetworkError = false;
          
          if (typeof e === 'string' && (e.includes('Timeout') || e.includes('conexión'))) {
            isNetworkError = true;
          }
          
          return { 
            success: false, 
            data: Connection.errorMessage({ data: e, status: 600 }), 
            isNetworkError,
            status: 600 
          };
        }
        
        // Si NO es el último intento, continuar con el siguiente
        continue;
      }
    }

    return false;

  }

}
