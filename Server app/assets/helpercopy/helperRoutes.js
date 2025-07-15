//acceder a raiz de reat y enviar a la carpeta assets
const _assets = ()=> { return __url_root + "assets/" }
const _config = ()=> { return __url_root + "config/" }
const _helpers = ()=> { return __url_root + "helpers/" }
const _view = ()=> { return __url_root + "view/" }
const _services= ()=> { return __url_root + "services/" }
const __url_root = ()=> { return window.location.origin }