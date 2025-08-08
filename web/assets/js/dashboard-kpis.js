// KPI Management for Modern Dashboard
class DashboardKPIs {
    // Inicializar elementos KPI
    constructor() {
        this.kpiElements = {
            ventasTotales: document.getElementById('kpi-ventas-totales'),
            sucursalTop: document.getElementById('kpi-sucursal-top'),
            peorNegocio: document.getElementById('kpi-peor-negocio'),
            facturasTotales: document.getElementById('kpi-facturas-totales')
        };
    }

    // Actualizar KPIs copiando exactamente la lógica de cargarCounterEnTabla
    updateFromCounterData(request) {
        console.log('=== ACTUALIZANDO KPIs ===');
        console.log('Request data:', request);
        
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
        if (this.kpiElements.ventasTotales) {
            this.animateUpdate(this.kpiElements.ventasTotales, formatearMontoChile(totalSucursales));
            console.log('Actualizado ventas totales:', formatearMontoChile(totalSucursales));
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
            this.animateUpdate(this.kpiElements.facturasTotales, formatearMontoChile(totalFacturas));
            console.log('Actualizado facturas totales:', formatearMontoChile(totalFacturas));
        }
    }

    // Función para animar la actualización de valores
    animateUpdate(element, newValue) {
        // Agregar clase de loading
        element.classList.add('loading');
        
        setTimeout(() => {
            // Remover loading y actualizar valor
            element.classList.remove('loading');
            element.textContent = newValue;
            element.classList.add('updated');
            
            // Remover clase de actualizado después de la animación
            setTimeout(() => {
                element.classList.remove('updated');
            }, 600);
        }, 300);
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    window.dashboardKPIs = new DashboardKPIs();
});
