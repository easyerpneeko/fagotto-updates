/**
 * dashboard-delivery-tickets.js
 * Funcionalidad para calcular conteos de tickets por plataforma de delivery
 * Autor: Fagotto Franquicias
 * Fecha: 2025-11-05
 */

/**
 * Calcula el conteo de tickets de delivery (Uber, Rappi, Pedidos Ya) para cada sucursal
 * @param {Object} counterRequest - Respuesta del endpoint /getAppCounters
 * @param {string} startDate - Fecha de inicio
 * @param {string} endDate - Fecha de fin
 * @returns {Promise} - Promise que se resuelve cuando se completa el cálculo
 */
async function calcularTicketsDelivery(counterRequest, startDate, endDate) {
    console.log('🎫 === INICIANDO CÁLCULO DE TICKETS DELIVERY ===');
    
    // Array de promesas para procesar todas las sucursales en paralelo
    const promesas = [];
    
    for (const app in counterRequest) {
        const appIdName = app.split(',', 2);
        const appId = appIdName[0];
        const appName = appIdName[1] || app;
        
        // Solo procesar si tiene counters
        if (counterRequest[app] && counterRequest[app].original && counterRequest[app].original.counters) {
            const counters = counterRequest[app].original.counters;
            
            // Si el backend NO tiene los conteos, calcularlos desde ventas individuales
            if (typeof counters.uber_count === 'undefined' || 
                typeof counters.rappi_count === 'undefined' || 
                typeof counters.pedidos_ya_count === 'undefined') {
                
                console.log(`🔍 Procesando tickets para: ${appName} (ID: ${appId})`);
                
                // Crear promesa para esta sucursal
                const promesa = new Promise((resolve, reject) => {
                    // Obtener ventas de esta sucursal para contar tickets
                    const paymentParams = 'uber=true&rappi=true&pedidos_ya=true';
                    const sellsUrl = generarURLApi(`/web/getAppSells?id=${appId}&startDate=${startDate}&endDate=${endDate}&${paymentParams}&per_page=10000`);
                    
                    __conection({
                        url: sellsUrl,
                        header: credentials(),
                        dev: true,
                        method: 'GET'
                    }, {}, function(sellsResponse) {
                        try {
                            // Extraer ventas del response
                            const sells = [];
                            if (Array.isArray(sellsResponse)) {
                                sells.push(...sellsResponse);
                            } else {
                                for (const appKey in sellsResponse) {
                                    if (sellsResponse[appKey] && sellsResponse[appKey].original && Array.isArray(sellsResponse[appKey].original)) {
                                        sells.push(...sellsResponse[appKey].original);
                                    }
                                }
                            }
                            
                            // Contar tickets por plataforma
                            let uberCount = 0;
                            let rappiCount = 0;
                            let pedidosYaCount = 0;
                            
                            sells.forEach(venta => {
                                // Usar other_type (plataforma delivery) o paymode
                                let metodo = (venta.other_type || venta.paymode || '').toLowerCase().trim();
                                
                                // 🔍 DEBUG: Ver qué métodos llegan
                                if (metodo && (metodo.includes('uber') || metodo.includes('rappi') || metodo.includes('pedidos'))) {
                                    console.log(`   📱 Método detectado: "${metodo}" (original: "${venta.other_type || venta.paymode}")`);
                                }
                                
                                // Mapeo de nombres de plataformas (más variantes)
                                if (metodo === 'uber_eats' || metodo === 'uber' || metodo === 'ubereats' || 
                                    metodo === 'uber eats' || metodo.includes('uber')) {
                                    uberCount++;
                                } else if (metodo === 'rappi' || metodo.includes('rappi')) {
                                    rappiCount++;
                                } else if (metodo === 'pedidos_ya' || metodo === 'pedidosya' || 
                                           metodo === 'pedidos ya' || metodo.includes('pedidos')) {
                                    pedidosYaCount++;
                                }
                            });
                            
                            // Agregar los conteos al objeto counters
                            counters.uber_count = uberCount;
                            counters.rappi_count = rappiCount;
                            counters.pedidos_ya_count = pedidosYaCount;
                            
                            const totalTickets = uberCount + rappiCount + pedidosYaCount;
                            console.log(`✅ ${appName}: ${totalTickets} tickets (Uber: ${uberCount}, Rappi: ${rappiCount}, Pedidos Ya: ${pedidosYaCount})`);
                            
                            resolve();
                        } catch (error) {
                            console.error(`❌ Error procesando ventas para ${appName}:`, error);
                            // Establecer en 0 si hay error
                            counters.uber_count = 0;
                            counters.rappi_count = 0;
                            counters.pedidos_ya_count = 0;
                            resolve(); // Continuar aunque haya error
                        }
                    }, function(error) {
                        console.error(`❌ Error obteniendo ventas para ${appName}:`, error);
                        // Establecer en 0 si hay error
                        counters.uber_count = 0;
                        counters.rappi_count = 0;
                        counters.pedidos_ya_count = 0;
                        resolve(); // Continuar aunque haya error
                    });
                });
                
                promesas.push(promesa);
            } else {
                // Si ya tiene los conteos, solo loggear
                const totalTickets = (counters.uber_count || 0) + (counters.rappi_count || 0) + (counters.pedidos_ya_count || 0);
                console.log(`✓ ${appName}: Ya tiene conteos (${totalTickets} tickets)`);
            }
        }
    }
    
    // Esperar a que todas las promesas se completen
    await Promise.all(promesas);
    console.log('🎉 === CÁLCULO DE TICKETS COMPLETADO ===');
}

// Exponer la función globalmente
window.calcularTicketsDelivery = calcularTicketsDelivery;
