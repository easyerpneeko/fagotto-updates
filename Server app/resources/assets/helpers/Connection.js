import qs from "qs";
import store from '../store/users';

var headerAuth = {};

export default class Connection {

  static fillHeaders(token){
    headerAuth['authorization'] = 'Bearer ' + token;
  }

  static desfillHeaders(){
    headerAuth['authorization'] = '';
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
      case 500: error = 'Error interno del servidor'; if (store.state.app.production) return error; break;
    }

    if (response.data)
      error = (Connection.isJsonString(response.data)) ? JSON.parse(response.data) : response.data;


    return error;
  }

  static fetch(url, method = 'GET', data = null, headers = null, isFormData = false){


    if (isFormData && (method != 'POST' || method != 'post' || method != 'GET' || method != 'get' )) {
      data.append('_method',method);
      method = 'post';
    }

    if (!headers)
      headers = {
        //'Content-Type': (isFormData) ? 'multipart/form-data' : 'application/json',
        ...headerAuth
      };



    let myInit = { method, headers, mode: 'cors', cache: 'default' };

    if (data) myInit.body = (isFormData) ? data : qs.stringify(data);

    const myRequest = new Request(url, myInit);

    return fetch(myRequest).then(async res => {

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

    if (isFormData === null)
      isFormData = (formData instanceof FormData) ? true : false;


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
      console.log("-----------------------");
      console.log(response);
      if (response.ok)
        return { success: true, data: response.data };

      const errorResponse = Connection.errorMessage(response);

      let exitLogin = false;

      if (errorResponse.status
        && ( errorResponse.status == 'Token is Invalid' || errorResponse.status == 'Token is Expired' || errorResponse.status == 'Authorization Token not found' )
      ) { exitLogin = true; }

      return { success: false, data: errorResponse, exitLogin };

    } catch (e) {


      return { success: false, data: Connection.errorMessage({ data: e, status: 600 }) };
      /*return { success: false, data: Connection.errorMessage(e.response) };*/

    }

    return false;

  }

}
