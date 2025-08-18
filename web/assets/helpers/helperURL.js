// const URL=($url) => {return `http://209.145.62.104/api${$url}`;};
// const URL=($url) => {return `http://154.53.34.255/api${$url}`;};
const generarURLApi = ($url) => {
    // Usar el dominio actual donde está cargada la página para evitar CORS
    const currentDomain = window.location.origin; // https://fagottoerp.cl
    return `${currentDomain}/api${$url}`;
  };