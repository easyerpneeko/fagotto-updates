// Función MEJORADA para descargar Excel con paginación múltiple
function downloadCustomExcel() {
    Swal.fire({
        title: 'Generando Excel Completo...',
        text: 'Obteniendo TODAS las ventas (puede tomar unos momentos)',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // OBTENER FECHAS DE LOS FILTROS
    let startDate, endDate;
    
    const startDateElement = document.getElementById('startDate');
    const endDateElement = document.getElementById('endDate');
    
    if (startDateElement && startDateElement.value) {
        startDate = startDateElement.value;
    } else {
        startDate = getNowDate();
    }
    
    if (endDateElement && endDateElement.value) {
        endDate = endDateElement.value + ' 23:59:59';
    } else {
        endDate = getNowDate() + ' 23:59:59';
    }
    
    console.log('🚀 INICIANDO DESCARGA COMPLETA DE VENTAS...');
    console.log('📅 Período:', `${startDate} hasta ${endDate}`);
    
    // FUNCIÓN PARA OBTENER TODAS LAS PÁGINAS
    async function obtenerTodasLasVentas() {
        let todasLasVentas = [];
        let paginaActual = 1;
        let seguirBuscando = true;
        
        while (seguirBuscando) {
            console.log(`📄 Obteniendo página ${paginaActual}...`);
            
            const urlPagina = generarURLApi(`/web/getAppVentas?params=true&page=${paginaActual}&per_page=50&startDate=${startDate}&endDate=${endDate}&id=${id}`);
            
            try {
                const resultado = await new Promise((resolve, reject) => {
                    __conection({
                        url: urlPagina,
                        header: credentials(),
                        dev: true,
                        method: 'GET'
                    }, {}, resolve, reject);
                });
                
                let ventasPagina = [];
                
                // Extraer ventas de esta página
                if (resultado && typeof resultado === 'object') {
                    for (const app in resultado) {
                        if (resultado[app] && resultado[app].original && resultado[app].original.items && Array.isArray(resultado[app].original.items)) {
                            ventasPagina.push(...resultado[app].original.items);
                        }
                    }
                }
                
                console.log(`✅ Página ${paginaActual}: ${ventasPagina.length} ventas`);
                
                if (ventasPagina.length === 0 || ventasPagina.length < 50) {
                    console.log('🏁 Última página alcanzada');
                    if (ventasPagina.length > 0) {
                        todasLasVentas.push(...ventasPagina);
                    }
                    seguirBuscando = false;
                } else {
                    todasLasVentas.push(...ventasPagina);
                    paginaActual++;
                    
                    // Límite de seguridad
                    if (paginaActual > 100) {
                        console.log('⚠️ Límite de 100 páginas alcanzado');
                        seguirBuscando = false;
                    }
                }
                
            } catch (error) {
                console.error(`❌ Error en página ${paginaActual}:`, error);
                seguirBuscando = false;
            }
        }
        
        console.log(`🎉 TOTAL OBTENIDO: ${todasLasVentas.length} ventas`);
        
        if (todasLasVentas.length > 0) {
            // Mostrar rango de fechas
            const fechas = todasLasVentas.map(s => s.created_at).filter(f => f).sort();
            console.log(`📅 Desde: ${fechas[0]} hasta: ${fechas[fechas.length - 1]}`);
            
            // Mostrar estadísticas por hora
            const porHora = {};
            todasLasVentas.forEach(sell => {
                const fecha = new Date(sell.created_at);
                const hora = fecha.getHours();
                porHora[hora] = (porHora[hora] || 0) + 1;
            });
            
            console.log('📊 Distribución por hora:');
            Object.keys(porHora).sort((a, b) => parseInt(a) - parseInt(b)).forEach(h => {
                console.log(`   ${h.padStart(2, '0')}:xx → ${porHora[h]} ventas`);
            });
        }
        
        return todasLasVentas;
    }
    
    // EJECUTAR Y CREAR EXCEL
    obtenerTodasLasVentas().then(sells => {
        if (sells.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin ventas',
                text: `No se encontraron ventas para el período seleccionado`,
                confirmButtonText: 'Entendido'
            });
            return;
        }
        
        console.log('🎉 ¡CREANDO EXCEL CON TODAS LAS VENTAS!');
        createSingleDayExcel(sells, startDate, endDate);
        
    }).catch(error => {
        console.error('❌ ERROR CRÍTICO:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error obteniendo ventas',
            text: 'No se pudo obtener las ventas del servidor',
            confirmButtonText: 'Entendido'
        });
    });
}