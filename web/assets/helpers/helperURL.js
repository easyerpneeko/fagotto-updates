// const URL=($url) => {return `http://209.145.62.104/api${$url}`;};
// const URL=($url) => {return `http://154.53.34.255/api${$url}`;};
const generarURLApi = ($url) => {
    // La API está en posfagotto.cl, no en el dominio actual
    return `https://posfagotto.cl/api${$url}`;
};