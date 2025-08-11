// KPI Management for Modern Dashboard
class DashboardKPIs {
    // Inicializar elementos KPI
    constructor() {
        console.log('=== INICIALIZANDO DashboardKPIs ===');
        
        this.kpiElements = {
            ventasTotales: document.getElementById('kpi-ventas-totales'),
            sucursalTop: document.getElementById('kpi-sucursal-top'),
            peorNegocio: document.getElementById('kpi-peor-negocio'),
            facturasTotales: document.getElementById('kpi-facturas-totales')
        };
        
        console.log('Elementos KPI inicializados:', this.kpiElements);
        
        // Verificar que todos los elementos existan
        let elementosFaltantes = [];
        for (const [key, element] of Object.entries(this.kpiElements)) {
            if (!element) {
                elementosFaltantes.push(key);
            }
        }
        
        if (elementosFaltantes.length > 0) {
            console.warn('Elementos KPI faltantes:', elementosFaltantes);
        } else {
            console.log('Todos los elementos KPI encontrados correctamente');
        }
    }

    // Método para reinicializar elementos si es necesario
    reinitialize() {
        console.log('Reinicializando elementos KPI...');
        this.constructor();
    }

    // Actualizar KPIs copiando exactamente la lógica de cargarCounterEnTabla
    updateFromCounterData(request) {
        console.log('=== ACTUALIZANDO KPIs ===');
        console.log('Request data:', request);
        
        // Verificar si formatearMontoChile está disponible
        if (typeof formatearMontoChile !== 'function') {
            console.error('formatearMontoChile no está definida');
            console.log('Funciones disponibles en window:', Object.getOwnPropertyNames(window).filter(name => name.includes('format')));
            return;
        } else {
            console.log('formatearMontoChile está disponible');
        }
        
        // Verificar elementos KPI
        console.log('Elementos KPI:', this.kpiElements);
        for (const [key, element] of Object.entries(this.kpiElements)) {
            if (!element) {
                console.error(`Elemento KPI no encontrado: ${key}`);
            } else {
                console.log(`Elemento KPI OK: ${key}`, element);
            }
        }
        
        // Copiar exactamente la lógica de cargarCounterEnTabla para Fagotto Franquicias
        let totalSucursales = 0;
        let mejorSucursal = { name: '-', monto: 0 };
        let peorSucursal = { name: '-', monto: Infinity };
        let totalFacturas = 0; // Esto será el monto total, no el conteo

        for (const app in request) {
            let appIdName = app.split(',', 2);
            let sucursalNombre = appIdName[1];
            
            console.log(`Procesando: ${sucursalNombre} (ID: ${appIdName[0]})`);
            
            // EXACTAMENTE la misma condición que en cargarCounterEnTabla
            if(appIdName[0] != 79 && appIdName[0] != 86 && appIdName[0] != 98 && appIdName[0] != 109 && appIdName[0] != 106) {
                let balance = request[app].original.counters.balanceTotal;
                totalSucursales += balance;
                console.log(`Sumando ${balance} de ${sucursalNombre}. Total: ${totalSucursales}`);
                
                // Mejor sucursal
                if (balance > mejorSucursal.monto) {
                    mejorSucursal = { name: sucursalNombre, monto: balance };
                }
                
                // Peor sucursal (que tenga ventas > 0)
                if (balance < peorSucursal.monto && balance > 0) {
                    peorSucursal = { name: sucursalNombre, monto: balance };
                }
            }
            
            // Sumar facturas de TODAS las sucursales (tanto franquicias como oficiales)
            // Usar el balanceTotal como "monto de facturas"
            let balance = request[app].original.counters.balanceTotal;
            totalFacturas += balance;
            console.log(`Monto facturas de ${sucursalNombre}: ${balance}, Total acumulado: ${totalFacturas}`);
        }

        console.log('=== RESULTADOS FINALES ===');
        console.log('Total ventas (mismo que tabla):', totalSucursales);
        console.log('Total facturas:', totalFacturas);
        console.log('Mejor sucursal:', mejorSucursal);
        console.log('Peor sucursal:', peorSucursal);

        // Si no hay peor sucursal válida
        if (peorSucursal.monto === Infinity) {
            peorSucursal = { name: '-', monto: 0 };
        }

        // Actualizar los elementos con animaciones
        try {
            if (this.kpiElements.ventasTotales) {
                const valorFormateado = formatearMontoChile(totalSucursales);
                this.animateUpdate(this.kpiElements.ventasTotales, valorFormateado);
                console.log('Actualizado ventas totales:', valorFormateado);
            }
            if (this.kpiElements.sucursalTop) {
                this.animateUpdate(this.kpiElements.sucursalTop, mejorSucursal.name);
                console.log('Actualizada mejor sucursal:', mejorSucursal.name);
            }
            if (this.kpiElements.peorNegocio) {
                this.animateUpdate(this.kpiElements.peorNegocio, peorSucursal.name);
                console.log('Actualizado peor negocio:', peorSucursal.name);
            }
            if (this.kpiElements.facturasTotales) {
                const valorFormateado = formatearMontoChile(totalFacturas);
                this.animateUpdate(this.kpiElements.facturasTotales, valorFormateado);
                console.log('Actualizado facturas totales:', valorFormateado);
            }
            console.log('=== ACTUALIZACION KPIs COMPLETADA ===');
        } catch (updateError) {
            console.error('Error durante la actualización de KPIs:', updateError);
        }
    }

    // Función para animar la actualización de valores
    animateUpdate(element, newValue) {
        console.log('Animando actualización:', element, 'con valor:', newValue);
        
        if (!element) {
            console.error('Elemento no encontrado para animación');
            return;
        }
        
        try {
            // Agregar clase de loading
            element.classList.add('loading');
            
            setTimeout(() => {
                // Remover loading y actualizar valor
                element.classList.remove('loading');
                element.textContent = newValue;
                element.classList.add('updated');
                
                console.log('Elemento actualizado:', element.id, 'con texto:', element.textContent);
                
                // Remover clase de actualizado después de la animación
                setTimeout(() => {
                    element.classList.remove('updated');
                }, 600);
            }, 300);
        } catch (animationError) {
            console.error('Error en animación:', animationError);
            // Fallback: actualizar directamente sin animación
            if (element) {
                element.textContent = newValue;
            }
        }
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM CARGADO - Inicializando DashboardKPIs ===');
    
    // Verificar que formatearMontoChile esté disponible
    if (typeof formatearMontoChile !== 'function') {
        console.warn('formatearMontoChile no está disponible en DOMContentLoaded');
    } else {
        console.log('formatearMontoChile está disponible');
    }
    
    try {
        window.dashboardKPIs = new DashboardKPIs();
        console.log('DashboardKPIs inicializado exitosamente');
        console.log('window.dashboardKPIs:', window.dashboardKPIs);
    } catch (error) {
        console.error('Error inicializando DashboardKPIs:', error);
    }
});

// Función de utilidad para verificar el estado
window.checkDashboardKPIs = function() {
    console.log('=== ESTADO DASHBOARD KPIs ===');
    console.log('window.dashboardKPIs existe:', !!window.dashboardKPIs);
    console.log('formatearMontoChile disponible:', typeof formatearMontoChile);
    if (window.dashboardKPIs) {
        console.log('Elementos KPI:', window.dashboardKPIs.kpiElements);
    }
};
