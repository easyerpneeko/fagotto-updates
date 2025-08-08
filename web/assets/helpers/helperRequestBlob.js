const __fetch_contruct_blob = async (param, parametros = {}, __fuction__) => {
    let alert = false;
    let dev = false;
    let header = {};
    let method = "POST";
    let url = "#";

    if (is_undefined(param) || is_undefined(param.url)) {
        alerta("error", "[ERROR] NO SE ENCONTRARON PARAMETROS ");
        console.log("[ERROR] fetch_construct => param {url:'https' , methods : 'post'},{title:'value'},function(){ code }}");
        return 0;
    }
    if (!is_undefined(param.dev)) { dev = true; }
    if (!is_undefined(param.alert)) { alert = true; }
    if (!is_undefined(param.method)) { method = param.method; }
    if (!is_undefined(param.url)) { url = param.url; }
    if (is_undefined(parametros)) {
        alerta("error", "[ERROR] SE NECESITA UN VALOR PARAMETRO ({config},{param},function(){ code})");
        console.log("[ERROR] SE NECESITA UN VALOR PARAMETRO ({config},{param},function(){ code})");
        return -1;
    }

    let DevEntries = [];
    let formData = new FormData();
    for (let key in parametros) { formData.append(key, parametros[key]); DevEntries[key] = parametros[key]; }

    if (!is_undefined(param.header)) {
        _header = param.header;
        header = { ..._header, ...header };
    }

    let requestOptions = {};
    if (method === 'GET') {
        requestOptions = {
            method: method,
            mode: 'cors',
            headers: header
        };
    } else {
        requestOptions = {
            method: method,
            mode: 'cors',
            headers: header,
            body: formData
        };
    }

    if (alert) {
        return -1;
    }

    if (dev === true) {
        console.log("[Request]", { request: DevEntries, ...requestOptions });
    }

    $("#log").text(JSON.stringify(requestOptions));
    try {
        const res = await fetch(url, requestOptions);
        const contentType = res.headers.get('content-type');
        let data;

        if (contentType && contentType.includes('application/json')) {
            data = await res.json();
        } else {
            data = await res.blob();
        }

        if ((typeof __fuction__ !== 'undefined') && (jQuery.isFunction(__fuction__))) {
            $(__fuction__(data));
        } else {
            return -1;
        }
    } catch (error) {
        $("#log").text(JSON.stringify(error));
        console.log("[ERROR] fetch_construct => error", error);
        return -1;
    }
};

const __conection_blob = __fetch_contruct_blob;


