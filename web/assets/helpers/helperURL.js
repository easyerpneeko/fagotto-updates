// const URL=($url) => {return `http://209.145.62.104/api${$url}`;};
// const URL=($url) => {return `http://154.53.34.255/api${$url}`;};
const generarURLApi = ($url) => {
    return `https://posfagotto.cl/api${$url}`;
};

// Agregamos la función URL para compatibilidad
const URL = ($url) => {
    return `https://posfagotto.cl/api${$url}`;
};