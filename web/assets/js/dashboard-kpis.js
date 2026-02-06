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
        
        let ventasLocalesPropios = 0;
        let ventasSucursales = 0;
        let ventasDeliveryTotal = 0;
        
        // IDs de Locales Propios (los 8 locales específicos)
        const idsLocalesPropios = [
            86,  // Fagotto Ahumada
            58,  // Fagotto Agustinas
            107, // Fagotto Bulnes
            59,  // Fagotto Plaza De Armas
            111, // Fagotto Mall Imperio
            97,  // Fagotto Rosario norte
            78,  // Fagotto Encomenderos
            116  // Fagotto Las Condes
        ];
        
        // IDs de Sucursales (9 locales específicos)
        const idsSucursalesOficiales = [
            114, // Fagotto Amunategi
            95,  // Fagotto Bombero Osa
            77,  // Fagotto Merced
            108, // Fagotto Puente Alto
            117, // Fagotto Rancagua
            113, // Fagotto Vergara
            96,  // Fagotto Suecia
            102, // Fagotto Turbus
            98   // Fagotto Manuel Montt
        ];
        // ID a excluir: Facturación (79)
        const idsExcluir = [79];

        for (const app in request) {
            let appIdName = app.split(',', 2);
            let appId = parseInt(appIdName[0]);
            let sucursalNombre = appIdName[1] || app;
            let counters = request[app].original.counters;
            let balance = parseFloat(counters.balanceTotal) || 0;
            
            console.log(`Procesando: ${sucursalNombre} (ID: ${appId}) - Balance: ${balance}`);
            
            // Excluir Facturación
            if(idsExcluir.includes(appId)) {
                console.log(`❌ Excluido (Facturación): ${sucursalNombre}`);
                continue;
            }
            
            // Sumar ventas de delivery de todos los locales
            const uber = parseFloat(counters.uber) || 0;
            const rappi = parseFloat(counters.rappi) || 0;
            const pedidosYa = parseFloat(counters.pedidos_ya) || 0;
            const deliveryLocal = uber + rappi + pedidosYa;
            
            ventasDeliveryTotal += deliveryLocal;
            
            if (deliveryLocal > 0) {
                console.log(`🚗 Delivery ${sucursalNombre}: Uber=${uber}, Rappi=${rappi}, PedidosYa=${pedidosYa}, Total=${deliveryLocal}`);
            }
            
            // Clasificar por tipo para tarjetas 1 y 2
            if (idsLocalesPropios.includes(appId)) {
                ventasLocalesPropios += balance;
                console.log(`🏪 Local Propio: ${sucursalNombre} = ${balance}, Total: ${ventasLocalesPropios}`);
            } else if (idsSucursalesOficiales.includes(appId)) {
                ventasSucursales += balance;
                console.log(`🏢 Sucursal Oficial: ${sucursalNombre} = ${balance}, Total: ${ventasSucursales}`);
            }
        }
        
        // Calcular ventas totales (Locales Propios + Sucursales Oficiales, sin Trai)
        let ventasTotalGeneral = ventasLocalesPropios + ventasSucursales;

        console.log('=== RESULTADOS FINALES KPIs ===');
        console.log('🏪 Ventas Locales Propios (8 locales):', ventasLocalesPropios);
        console.log('🏢 Ventas Sucursales (9 locales):', ventasSucursales);
        console.log('🚗 Ventas Delivery Total (Todos los locales):', ventasDeliveryTotal);
        console.log('📊 Ventas Total General (Locales+Sucurs):', ventasTotalGeneral);

        // Actualizar los elementos con animaciones
        if (this.kpiElements.ventasFranquicias) {
            this.animateUpdate(this.kpiElements.ventasFranquicias, formatearMontoChile(ventasLocalesPropios));
        }
        if (this.kpiElements.ventasSucursales) {
            this.animateUpdate(this.kpiElements.ventasSucursales, formatearMontoChile(ventasSucursales));
        }
        if (this.kpiElements.ventasTrai) {
            this.animateUpdate(this.kpiElements.ventasTrai, formatearMontoChile(ventasDeliveryTotal));
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
