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

    return fetch(url, myInit).then(async res => {

        try {
          return { ok: res.ok, data: await res.clone().json(), status:res.status };
        } catch (e) {
          return { ok: false, data: await res.clone().text(), status:res.status };
        }

    })
    .catch(function(error) {
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

  static async request(method, url, formData = null, headers = null, isFormData = null ) {

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

    try {
      const response = await Connection.fetch( url , method , formData, headers, isFormData );

      console.log("Response", response);

      if (response.ok)//response.status
        return { success: true, data: response.data, status };



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

      return { success: false, data: errorResponse, exitLogin, aplicacionBloqueada, aplicacionVencida, status };

    } catch (e) {


      return { success: false, data: Connection.errorMessage({ data: e, status: 600 }) };
      /*return { success: false, data: Connection.errorMessage(e.response) };*/

    }

    return false;

  }

}
