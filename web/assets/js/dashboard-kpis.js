// KPI Management for Modern Dashboard
class DashboardKPIs {
    // Inicializar elementos KPI
    constructor() {
        this.kpiElements = {
            ventasFranquicias: document.getElementById('kpi-ventas-franquicias'),
            ventasSucursales: document.getElementById('kpi-ventas-sucursales'),
            ventasTrai: document.getElementById('kpi-ventas-trai'),
            ventasTotalGeneral: document.getElementById('kpi-ventas-total-general')
        };
    }

    // Actualizar KPIs con la nueva lógica
    updateFromCounterData(request) {
        console.log('=== ACTUALIZANDO KPIs ===');
        console.log('Request data:', request);
        
        let ventasFranquicias = 0;
        let ventasSucursales = 0;
        let ventasTrai = 0;
        
        // IDs de sucursales oficiales: Fagotto Ahumada (86) y Fagotto Manuel Montt (98)
        const idsSucursalesOficiales = [86, 98];
        // IDs de Trai i Pasti: 109 y 106
        const idsTraiIPasti = [109, 106];
        // ID a excluir: Facturación (79)
        const idsExcluir = [79];

        for (const app in request) {
            let appIdName = app.split(',', 2);
            let appId = parseInt(appIdName[0]);
            let sucursalNombre = appIdName[1] || app;
            let balance = parseFloat(request[app].original.counters.balanceTotal) || 0;
            
            console.log(`Procesando: ${sucursalNombre} (ID: ${appId}) - Balance: ${balance}`);
            
            // Excluir Facturación
            if(idsExcluir.includes(appId)) {
                console.log(`❌ Excluido (Facturación): ${sucursalNombre}`);
                continue;
            }
            
            // Clasificar por tipo
            if (idsTraiIPasti.includes(appId)) {
                ventasTrai += balance;
                console.log(`🍝 Trai i Pasti: ${sucursalNombre} = ${balance}, Total: ${ventasTrai}`);
            } else if (idsSucursalesOficiales.includes(appId)) {
                ventasSucursales += balance;
                console.log(`🏢 Sucursal Oficial: ${sucursalNombre} = ${balance}, Total: ${ventasSucursales}`);
            } else {
                ventasFranquicias += balance;
                console.log(`🤝 Franquicia: ${sucursalNombre} = ${balance}, Total: ${ventasFranquicias}`);
            }
        }
        
        // Calcular ventas totales (Franquicias + Sucursales Oficiales, sin Trai)
        let ventasTotalGeneral = ventasFranquicias + ventasSucursales;

        console.log('=== RESULTADOS FINALES KPIs ===');
        console.log('💰 Ventas Franquicias:', ventasFranquicias);
        console.log('🏢 Ventas Sucursales Oficiales:', ventasSucursales);
        console.log('🍝 Ventas Trai i Pasti:', ventasTrai);
        console.log('📊 Ventas Total General (Franq+Sucurs):', ventasTotalGeneral);

        // Actualizar los elementos con animaciones
        if (this.kpiElements.ventasFranquicias) {
            this.animateUpdate(this.kpiElements.ventasFranquicias, formatearMontoChile(ventasFranquicias));
        }
        if (this.kpiElements.ventasSucursales) {
            this.animateUpdate(this.kpiElements.ventasSucursales, formatearMontoChile(ventasSucursales));
        }
        if (this.kpiElements.ventasTrai) {
            this.animateUpdate(this.kpiElements.ventasTrai, formatearMontoChile(ventasTrai));
        }
        if (this.kpiElements.ventasTotalGeneral) {
            this.animateUpdate(this.kpiElements.ventasTotalGeneral, formatearMontoChile(ventasTotalGeneral));
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
