function activateLoader() {
    const loader = document.getElementById('loader');
    loader.classList.remove('hide');
    loader.classList.add('show');
}
function desactivateLoader() {
    const loader = document.getElementById('loader');
    loader.classList.remove('show');
    loader.classList.add('hide');
}

function formatearMontoChile(monto) { 
    return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(monto); 
}