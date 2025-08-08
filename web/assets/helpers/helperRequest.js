//console.log("conection.js")
const __fetch_contruct = async (param, parametros = {}, __fuction__) => {
    /**{
     *      alert (true false) //activa las alerta segundarias
     *      metho (get post put delete) //metodo de envio
     *      url (https url api) //url de direccion
     *
     * }
     * parametros ({title:"value",}) //parametros de envio
     * __fuction___ (function) //funcion de respuesta
     */

    let alert = false
    let dev = false
    let header = {
        //'Content-Type': "application/json",// 'multipart/form-data',
        //'Access-Control-Allow-Origin': '*',
        //'Access-Control-Allow-Methods': 'GET, POST, OPTIONS',
        //'Access-Control-Allow-Credentials': 'true',
        //'Access-Control-Allow-Headers': "Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"
    };//{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } // <--- aquí el token}

    let method = "POST"
    let url = "#";

    if (is_undefined(param) || is_undefined(param.url)) {
        alerta("error", "[ERROR] NO SE ENCONTRARON PARAMETROS ");
        console.log("[ERROR] fetch_construct => param {url:'https' , methods : 'post'},{title:'value'},function(){ code }}");
        return 0
    }
    if (!is_undefined(param.dev)) { dev = true }
    if (!is_undefined(param.alert)) { alert = true }
    if (!is_undefined(param.method)) { method = param.method }
    if (!is_undefined(param.url)) { url = param.url }
    if (is_undefined(parametros)) {
        alerta("error", "[ERROR] SE NECESITA UN VALOR PARAMETRO ({config},{param},function(){ code})");
        console.log("[ERROR] SE NECESITA UN VALOR PARAMETRO ({config},{param},function(){ code})")
        return -1
    }


    //tranformamos los parametros a FORMDATA y detectamos si hay un archivo inlcuido y lo enviamos
    let DevEntries = []
    let formData = new FormData();
    for (let key in parametros) { formData.append(key, parametros[key]); DevEntries[key] = parametros[key] };

    //controlmos los headers
    if (!is_undefined(param.header)) {
        _header = param.header
        header = { ..._header, ...header }
    }
    let requestOptions={};
    if (method == 'GET') {
        requestOptions = {
            method: method,
            mode: 'cors',
            headers: header,
            // body: formData
        };
    }
    else {
        requestOptions = {
            method: method,
            mode: 'cors',
            headers: header,
            body: formData
        };
    }

    if (alert) {
        // console.log("[WAITING] REVISANDO PARAMETROS");
        // console.log("[Request]", requestOptions)
        return -1
    }
    if (dev == true) {
        console.log("[ALERT] el modo prueva esta activado")
        console.log("[WAITING] INTENTANDO Hacer peticion")
        console.log("[Request]", { request: DevEntries, ...requestOptions }, "[END Request]")

    }
    $("#log").text(JSON.stringify(requestOptions))
    try {
        const res = await fetch(url, requestOptions)
        const data = await res.json()
        if ((typeof __fuction__ !== 'undefined') && (jQuery.isFunction(__fuction__))) { $(__fuction__(data)) } else { return -1 }
    } catch (error) {
        $("#log").text(JSON.stringify(error))
        console.log("[ERROR] fetch_construct => error", error)
        return -1;
    }

}
const __conection = __fetch_contruct; //otra forma de llamar a fecth construct

