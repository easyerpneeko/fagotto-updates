$(document).ready(function () {
    // _name.text(_store().session.user().get("username")) //obtenemos el nombre del usuario
    getThisDay();

})

const app_name = document.getElementById('app-name');
var startDateValue;
var endDateValue;
const urlParams = new URLSearchParams(window.location.search);
const id = urlParams.get('id');
const waiters = [];
const workshifts = [];
const products = [];
const expenses = [];
var montoInicial = 0; // Variable global para almacenar el monto inicial del arqueo

async function getData(startDate, endDate) {
    console.log("🚀 getData iniciado con fechas:", { startDate, endDate });
    
    activateLoader();
    getApp();
    
    // Consultar arqueo para obtener monto inicial
    await consultarArqueoMontoInicial(startDate);
    
    // Llamar a las funciones de gráficos independientes
    getSellByHourIndependent(startDate, endDate);
    getTopSellsIndependent(startDate, endDate);
    getPaymentMethodsChartIndependent(startDate, endDate);
    
    // Inicializar Centro Analítico
    initializeAnalyticsCenter(startDate, endDate);
    
    // Cargar mapa de calor de salsas
    loadSauceHeatmap(startDate, endDate);
    
    await getCounters(startDate, endDate);
    desactivateLoader();
    
    console.log("✅ getData completado");
}
// Función para marcar botón activo
function setActiveButton(buttonType) {
    // Remover clase activa de todos los botones
    document.querySelectorAll('[onclick*="getThis"]').forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline-secondary');
    });
    
    // Buscar y marcar el botón específico
    let selector = '';
    switch(buttonType) {
        case 'day':
            selector = '[onclick="getThisDay()"]';
            break;
        case 'week':
            selector = '[onclick="getThisWeek()"]';
            break;
        case 'month':
            selector = '[onclick="getThisMonth()"]';
            break;
    }
    
    if (selector) {
        const activeBtn = document.querySelector(selector);
        if (activeBtn) {
            activeBtn.classList.remove('btn-outline-secondary');
            activeBtn.classList.add('btn-primary');
        }
    }
}

function getByDate() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value + ' 23:59:59';
    
    if (!startDate || !endDate) {
        Swal.fire({
            icon: 'warning',
            title: 'Fechas requeridas',
            text: 'Por favor selecciona ambas fechas',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Resetear botones (ninguno activo al buscar manualmente)
    document.querySelectorAll('[onclick*="getThis"]').forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline-secondary');
    });
    
    console.log('📅 BÚSQUEDA POR FECHA:', { startDate, endDate });
    
    // Guardar fechas globalmente para usar en resumen
    startDateValue = startDate;
    endDateValue = endDate;
    
    getData(startDate, endDate);
}
function getThisDay() {
    const today = getNowDate();
    const startDate = today;
    const endDate = today + ' 23:59:59';
    
    // Actualizar los campos de fecha en el HTML
    document.getElementById('startDate').value = today;
    document.getElementById('endDate').value = today;
    
    // Marcar botón como activo
    setActiveButton('day');
    
    console.log('📅 HOY:', { startDate, endDate });
    
    // Guardar fechas globalmente para usar en resumen
    startDateValue = startDate;
    endDateValue = endDate;
    
    getData(startDate, endDate);
}

function getThisWeek() {
    const currentDate = new Date();
    const year = currentDate.getFullYear();
    
    // Obtener el día de la semana (0 = domingo, 1 = lunes, etc.)
    const currentDayOfWeek = currentDate.getDay();
    
    // Calcular cuántos días restar para llegar al lunes (1)
    // Si es domingo (0), restar 6 días; si es lunes (1), restar 0 días, etc.
    const daysToMonday = currentDayOfWeek === 0 ? 6 : currentDayOfWeek - 1;
    
    // Calcular el primer día de la semana (lunes)
    const firstDayOfWeek = new Date(currentDate);
    firstDayOfWeek.setDate(currentDate.getDate() - daysToMonday);
    
    // Calcular el último día de la semana (domingo)
    const lastDayOfWeek = new Date(firstDayOfWeek);
    lastDayOfWeek.setDate(firstDayOfWeek.getDate() + 6);
    
    const firstDayFormatted = `${firstDayOfWeek.getFullYear()}-${String(firstDayOfWeek.getMonth() + 1).padStart(2, '0')}-${String(firstDayOfWeek.getDate()).padStart(2, '0')}`;
    const lastDayFormatted = `${lastDayOfWeek.getFullYear()}-${String(lastDayOfWeek.getMonth() + 1).padStart(2, '0')}-${String(lastDayOfWeek.getDate()).padStart(2, '0')}`;
    
    // Actualizar los campos de fecha en el HTML
    document.getElementById('startDate').value = firstDayFormatted;
    document.getElementById('endDate').value = lastDayFormatted;
    
    // Marcar botón como activo
    setActiveButton('week');
    
    console.log('📅 ESTA SEMANA:', { 
        desde: firstDayFormatted, 
        hasta: lastDayFormatted,
        endDateWithTime: lastDayFormatted + ' 23:59:59'
    });
    
    // Guardar fechas globalmente para usar en resumen
    startDateValue = firstDayFormatted;
    endDateValue = lastDayFormatted + ' 23:59:59';
    
    getData(firstDayFormatted, lastDayFormatted + ' 23:59:59');
}

function getThisMonth() {
    const currentDate = new Date();
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
  
    // Obtener el primer día del mes
    const firstDayOfMonth = new Date(year, currentDate.getMonth(), 1);
    const firstDay = String(firstDayOfMonth.getDate()).padStart(2, '0');
  
    // Obtener el último día del mes
    const lastDayOfMonth = new Date(year, currentDate.getMonth() + 1, 0); // El día 0 del siguiente mes es el último del mes actual
    const lastDay = String(lastDayOfMonth.getDate()).padStart(2, '0');
  
    const firstDayFormatted = `${year}-${month}-${firstDay}`;
    const lastDayFormatted = `${year}-${month}-${lastDay}`;
  
    // Actualizar los campos de fecha en el HTML
    document.getElementById('startDate').value = firstDayFormatted;
    document.getElementById('endDate').value = lastDayFormatted;
    
    // Marcar botón como activo
    setActiveButton('month');
    
    console.log('📅 ESTE MES:', { 
        desde: firstDayFormatted, 
        hasta: lastDayFormatted,
        endDateWithTime: lastDayFormatted + ' 23:59:59'
    });
    
    // Guardar fechas globalmente para usar en resumen
    startDateValue = firstDayFormatted;
    endDateValue = lastDayFormatted + ' 23:59:59';
  
    // Llamar a getData con las fechas del mes
    getData(firstDayFormatted, lastDayFormatted + ' 23:59:59');
}

function getNowDate() {
    const currentDate = new Date();
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
    const day = String(currentDate.getDate()).padStart(2, '0');
    const hours = String(currentDate.getHours()).padStart(2, '0');
    const minutes = String(currentDate.getMinutes()).padStart(2, '0');
    const seconds = String(currentDate.getSeconds()).padStart(2, '0');

    return `${year}-${month}-${day}`
}

function getApp() {
    const url = generarURLApi(`/web/getApp?id=${id}`);

    __conection({
        url: url,
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        app_name.innerHTML = request[0].name;
    });
}

// Consultar arqueo para obtener monto inicial
async function consultarArqueoMontoInicial(fecha) {
    console.log('🔍 Consultando arqueo para obtener monto inicial...', { id, fecha });
    
    // Resetear monto inicial
    montoInicial = 0;
    
    try {
        const fechaSola = fecha.split(' ')[0]; // Obtener solo la parte de fecha sin hora
        const url = generarURLApi(`/local/arqueos-new?id=${id}&fecha_inicio=${fechaSola}&fecha_fin=${fechaSola}`);
        
        console.log('🔗 URL arqueo:', url);
        
        await __conection({
            url: url,
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function(response) {
            console.log('✅ Respuesta arqueo:', response);
            
            if (response && response.success && response.data && response.data.items) {
                const arqueos = response.data.items.filter(arqueo => arqueo.app_id === parseInt(id));
                
                if (arqueos.length > 0) {
                    // Buscar arqueo del día específico
                    for (let arqueo of arqueos) {
                        const fechaArqueo = arqueo.fecha_inicio ? arqueo.fecha_inicio.split(' ')[0] : '';
                        if (fechaArqueo === fechaSola && (arqueo.estado === 'cerrado' || arqueo.estado === 'abierto')) {
                            if (arqueo.monto_inicial && parseFloat(arqueo.monto_inicial) > 0) {
                                montoInicial = parseFloat(arqueo.monto_inicial);
                                console.log('💰 Monto inicial encontrado:', montoInicial);
                                break;
                            }
                        }
                    }
                }
            }
            
            if (montoInicial === 0) {
                console.log('⚠️ No se encontró monto inicial en el arqueo');
            }
        }, function(error) {
            console.error('❌ Error consultando arqueo:', error);
        });
    } catch (error) {
        console.error('❌ Error crítico consultando arqueo:', error);
    }
}

async function getCounters(startDate, endDate) {
    var url  = "";

    if(startDate && endDate){
        // Incluir parámetros de métodos de pago para filtrar los datos
        const paymentParams = [
            'fastSell=true',
            'boleta=true', 
            'factura=true',
            'noSii=true',
            'amipass=true',
            'rappi=true',
            'uber=true',
            'junaeb=true',
            'multicaja=true',
            'edenred=true',
            'sodexo=true',
            'convenio_empresa=true',
            'debito=true',
            'credito=true',
            'transferencia=true',
            'cheque=true',
            'banco=true',
            'pluxee=true',
            'pedidos_ya=true',
            'guia_despacho=true',
            'banco_chile_20=true',
            'nota_de_credito=true',
            'efectivo=true'
        ].join('&');
        
        url = generarURLApi(`/web/getAppCounters?id=${id}&startDate=${startDate}&endDate=${endDate}&${paymentParams}`);
    }else{
        url =generarURLApi(`/web/getAppCounters?id=${id}`);
    }

    await __conection({
        url: url,
        header: credentials(),
        dev: true,
        method: 'GET'

    }, {}, function (request) {
        const counters = [];
        const waiters = [];
        const workshifts = [];
        const products = [];
        const expenses = [];
        const labels = [];
        const counterData = [];

        for (const app in request) {
            counters.push(request[app].original.counters);
            waiters.push(request[app].original.waiters);
            workshifts.push(request[app].original.workshifts);
            products.push(request[app].original.products);
            expenses.push(request[app].original.expenses);
        }
        // console.log('Products antes:',products);
        function getWaiters() {
            // console.log(waiters[0]);
            const names = [];
            const dataWaiters = [];

            for (const clave in waiters[waiters.length - 1]) {
                names.push(waiters[0][clave].waiter);
                dataWaiters.push(waiters[0][clave].orders);
            }

            var ordersCanvas = document.getElementById('ordersChart');

            var ordersChart = new Chart(ordersCanvas, {
                type: 'bar',
                data: {
                    labels: names,
                    datasets: [{
                        // label: 'Contadores',
                        data: dataWaiters,
                        lineTension: 0,
                        backgroundColor: [
                            'rgb(115, 155, 86)',
                            'rgb(220, 150, 132)',
                            'rgb(150, 0, 100)',
                            'rgb(255, 255, 86)',
                            'rgb(0, 255, 132)',
                            'rgb(255, 255, 0)',
                            'rgb(150, 255, 86)',
                            'rgb(100, 0, 132)',
                            'rgb(255, 150, 0)',
                            'rgb(180, 255, 86)',
                            'rgb(255, 180, 132)',
                            'rgb(255, 50, 0)',
                            'rgb(255, 80, 86)',
                            'rgb(255, 80, 132)',
                            'rgb(54, 255, 235)',
                            'rgb(0, 45, 86)',
                            'rgb(255, 15, 132)',
                            'rgb(255, 255, 0)',
                            'rgb(0, 80, 86)',
                            'rgb(255, 170, 132)',
                            'rgb(255, 70, 0)',
                            'rgb(0, 150, 86)',
                            'rgb(255, 0, 132)',
                            'rgb(0, 0, 0)',
                            'rgb(255, 155, 86)',
                            'rgb(255, 150, 132)',
                            'rgb(54, 0, 235)',
                            'rgb(125, 255, 86)',
                            'rgb(152, 255, 132)',
                            'rgb(255, 125, 0)',
                            'rgb(0, 0, 86)',
                            'rgb(255, 0, 132)',
                            'rgb(255, 100, 0)',
                            'rgb(0, 255, 86)',
                            'rgb(255, 255, 132)',
                            'rgb(255, 0, 0)',
                            'rgb(54, 0, 235)',
                            'rgb(125, 255, 86)',
                            'rgb(152, 255, 132)',
                            'rgb(255, 125, 0)',
                            'rgb(0, 0, 86)',
                            'rgb(255, 0, 132)',
                            'rgb(255, 100, 0)',
                            'rgb(0, 255, 86)',
                            'rgb(255, 255, 132)',
                            'rgb(255, 0, 0)',
                        ],
                        borderColor: '#fff',
                        pointBackgroundColor: '#007bff',
                    }],
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    legend: {
                        display: false
                    }
                }
            });

            const tablaWaiters = document.getElementById('waiters-table');
            tablaWaiters.innerHTML = '';
            
            // Calcular total general para los porcentajes
            let totalGeneral = 0;
            for (const waiter in waiters[0]) {
                if (waiters[waiters.length - 1][waiter] && waiters[waiters.length - 1][waiter].total) {
                    totalGeneral += parseFloat(waiters[waiters.length - 1][waiter].total) || 0;
                }
            }

            for (const waiter in waiters[0]) {
                const waiterData = waiters[waiters.length - 1][waiter];
                const total = parseFloat(waiterData.total) || 0;
                const porcentaje = totalGeneral > 0 ? ((total / totalGeneral) * 100).toFixed(1) : '0.0';
                
                const fila = `<tr>
                                    <td>${waiterData.waiter}</td>
                                    <td>${waiterData.orders}</td>
                                    <td>$${parseInt(total).toLocaleString()}</td>
                                    <td><span class="badge bg-primary">${porcentaje}%</span></td>
                                </tr>`;
                tablaWaiters.innerHTML += fila;
            }
        }
        getWaiters();

        function getWorkshift() {
            // console.log(workshifts[0]);

            const tablaWorkshift = document.getElementById('workshifts-table');
            tablaWorkshift.innerHTML = '';
            // // console.log(waiter[0]);

            for (const workshift in workshifts[workshifts.length - 1]) {
                const fila = `<tr>
                                    <td>${workshifts[workshifts.length - 1][workshift].username}</td>
                                    <td>${workshifts[workshifts.length - 1][workshift].init_money}</td>
                                    <td>${workshifts[workshifts.length - 1][workshift].final_money}</td>
                                    <td>${workshifts[workshifts.length - 1][workshift].start_workshift}</td>
                                    <td>${workshifts[workshifts.length - 1][workshift].end_workshift}</td>
                                </tr>`;
                tablaWorkshift.innerHTML += fila;
            }
        }
        getWorkshift();

        function getProducts() {
            const tablaProducts = document.getElementById('products-table');
            tablaProducts.innerHTML = '';

            if (products && products.length > 0 && products[products.length - 1]) {
                // Convertir objeto a array para poder ordenar
                const productsArray = [];
                const productsData = products[products.length - 1];
                
                for (const product in productsData) {
                    if (productsData[product]) {
                        const productInfo = productsData[product];
                        const quantity = parseInt(productInfo['quantity']) || 0;
                        const price = parseFloat(productInfo['price']) || 0;
                        const total = price * quantity;
                        
                        productsArray.push({
                            name: productInfo['name'] || 'Sin nombre',
                            category: productInfo['category'] || 'Sin categoría',
                            quantity: quantity,
                            price: price,
                            total: total
                        });
                    }
                }

                // Ordenar por cantidad vendida (mayor a menor)
                productsArray.sort((a, b) => b.quantity - a.quantity);

                // Generar filas de la tabla
                productsArray.forEach((product, index) => {
                    const rankingIcon = index < 3 ? 
                        `<span class="badge bg-warning text-dark me-1">#${index + 1}</span>` : 
                        `<span class="text-muted me-1">#${index + 1}</span>`;
                    
                    const fila = `<tr ${index < 3 ? 'class="table-warning"' : ''}>
                                        <td>${rankingIcon}${product.name}</td>
                                        <td>${product.category}</td>
                                        <td><strong>${product.quantity}</strong></td>                                    
                                        <td>${formatearMontoChile(product.total)}</td>
                                    </tr>`;
                    tablaProducts.innerHTML += fila;
                });

                console.log('📊 Productos ordenados por cantidad:', productsArray);
            } else {
                tablaProducts.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No hay datos de productos disponibles</td></tr>';
            }
        }
        getProducts();

        function getExpenses() {
            const tablaGastos = document.getElementById('gastos-table');
            const badgeTotal = document.getElementById('badge-total-gastos');
            const gastosKPI = document.getElementById('gastos-total-kpi');
            const gastosCountKPI = document.getElementById('gastos-count-kpi');
            
            if (!tablaGastos) {
                console.warn('⚠️ Tabla de gastos no encontrada');
                return;
            }
            
            tablaGastos.innerHTML = '';
            
            // Verificar si hay gastos
            if (!expenses || !expenses[expenses.length - 1] || Object.keys(expenses[expenses.length - 1]).length === 0) {
                tablaGastos.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center text-muted">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            No hay gastos registrados en este período
                        </td>
                    </tr>
                `;
                if (badgeTotal) badgeTotal.textContent = 'Total: $0';
                if (gastosKPI) gastosKPI.textContent = '$0';
                if (gastosCountKPI) gastosCountKPI.textContent = '0 gastos registrados';
                return;
            }
            
            let totalGastos = 0;
            let countGastos = 0;
            
            for (const expense in expenses[expenses.length - 1]) {
                const gasto = expenses[expenses.length - 1][expense];
                const monto = parseFloat(gasto.balance) || 0;
                totalGastos += monto;
                countGastos++;
                
                const fecha = gasto.created_at || gasto.date || '-';
                const fechaFormateada = fecha !== '-' ? new Date(fecha).toLocaleString('es-CL') : '-';
                
                const fila = `<tr>
                                    <td>
                                        <i class="fas fa-receipt text-danger me-2"></i>
                                        ${gasto.name || 'Sin descripción'}
                                    </td>
                                    <td class="text-danger fw-bold">-$${Math.round(monto).toLocaleString('es-CL')}</td>
                                    <td class="text-muted small">${fechaFormateada}</td>
                                </tr>`;
                tablaGastos.innerHTML += fila;
            }
            
            const totalFormateado = Math.round(totalGastos).toLocaleString('es-CL');
            
            if (badgeTotal) {
                badgeTotal.textContent = `Total: -$${totalFormateado}`;
            }
            
            // Actualizar KPI arriba
            if (gastosKPI) {
                gastosKPI.textContent = `-$${totalFormateado}`;
            }
            if (gastosCountKPI) {
                gastosCountKPI.textContent = `${countGastos} gasto${countGastos !== 1 ? 's' : ''} registrado${countGastos !== 1 ? 's' : ''}`;
            }
            
            console.log('✅ Gastos cargados:', countGastos, 'gastos por un total de $' + totalGastos);
        }
        getExpenses();

        function getCounters() {

            const tablaCounters = document.getElementById('counters-table');
            tablaCounters.innerHTML = '<tr><td colspan="4" class="text-center">⏳ Cargando transacciones...</td></tr>';
            
            // 🎯 Obtener ventas individuales para contar transacciones
            const allPaymentParams = 'boleta=true&debito=true&banco=true&credito=true&transferencia=true&rappi=true&uber=true&junaeb=true&edenred=true&sodexo=true&amipass=true&pluxee=true&pedidos_ya=true&efectivo=true';
            const sellsUrl = generarURLApi(`/web/getAppSells?id=${id}&startDate=${startDate}&endDate=${endDate}&${allPaymentParams}&per_page=10000`);
            
            console.log('🔗 URL de ventas:', sellsUrl);
            
            __conection({
                url: sellsUrl,
                header: credentials(),
                dev: true,
                method: 'GET'
            }, {}, function (sellsRequest) {
                console.log('📦 Respuesta de ventas para conteo:', sellsRequest);
                
                // Extraer ventas del response
                const sells = [];
                if (Array.isArray(sellsRequest)) {
                    sells.push(...sellsRequest);
                } else {
                    for (const app in sellsRequest) {
                        if (sellsRequest[app] && sellsRequest[app].original && Array.isArray(sellsRequest[app].original)) {
                            sells.push(...sellsRequest[app].original);
                        } else if (sellsRequest[app] && Array.isArray(sellsRequest[app])) {
                            sells.push(...sellsRequest[app]);
                        }
                    }
                }
                
                console.log('📊 Total de ventas para contar:', sells.length);
                
                // Debug: mostrar los primeros 5 paymodes para verificar
                sells.slice(0, 5).forEach((venta, idx) => {
                    console.log(`🔍 Venta ${idx + 1} - paymode: "${venta.paymode}", typeSell: "${venta.typeSell}", other_type: "${venta.other_type}"`);
                });
                
                // Contar transacciones Y totales por método de pago
                const transaccionCounts = {};
                const transaccionTotals = {};
                
                // Mapeo de nombres de plataformas a nombres del backend
                const platformMapping = {
                    'uber_eats': 'uber',
                    'pedidos_ya': 'pedidos_ya',
                    'rappi': 'rappi'
                };
                
                sells.forEach(venta => {
                    // PRIORIDAD: Si tiene other_type (delivery platforms), usar ese
                    // Si no, usar paymode normal
                    let metodo = venta.other_type || venta.paymode || 'efectivo';
                    metodo = metodo.toLowerCase().trim(); // Normalizar
                    
                    // Mapear plataformas delivery al nombre del backend
                    metodo = platformMapping[metodo] || metodo;
                    
                    if (!transaccionCounts[metodo]) {
                        transaccionCounts[metodo] = 0;
                        transaccionTotals[metodo] = 0;
                    }
                    transaccionCounts[metodo]++;
                    transaccionTotals[metodo] += parseFloat(venta.total) || 0;
                });
                
                console.log('💳 Conteo de transacciones por método:', transaccionCounts);
                console.log('💰 Totales por método:', transaccionTotals);
                
                // Agregar los conteos Y totales al objeto counters
                if (counters[0]) {
                    Object.keys(transaccionCounts).forEach(metodo => {
                        counters[0][`${metodo}_count`] = transaccionCounts[metodo];
                        // Si el backend no tiene el total, usar el que calculamos
                        if (!counters[0][metodo] || parseFloat(counters[0][metodo]) === 0) {
                            counters[0][metodo] = transaccionTotals[metodo];
                        }
                    });
                    console.log('📊 Counters actualizados con conteos y totales:', counters[0]);
                }
                
                // Ahora sí, generar la tabla con los datos actualizados
                tablaCounters.innerHTML = '';
        
                if (counters[0].orders !== undefined) {
                    tablaCounters.innerHTML += `
                    <tr class="table-info fw-bold">
                        <td colspan="4">📦 ÓRDENES TOTALES: ${counters[0].orders}</td>
                    </tr>
                    `;
                }

                // FUNCIÓN AUXILIAR para generar fila con transacciones y promedio
                function generarFilaMetodoPago(nombre, total, countKey, iconoEmoji = '💳', metodoInterno = '') {
                    const totalAmount = parseFloat(total) || 0;
                    const transaccionCount = counters[0][countKey] || 0;
                    const transaccionPromedio = transaccionCount > 0 ? (totalAmount / transaccionCount) : 0;
                    
                    if (totalAmount > 0 || transaccionCount > 0) {
                        return `
                        <tr>
                            <td>${iconoEmoji} ${nombre}</td>
                            <td>
                                <strong 
                                    style="cursor: pointer; color: var(--primary-color); text-decoration: underline;" 
                                    onclick="mostrarDesglosePago('${nombre}', '${metodoInterno}', ${transaccionCount}, ${totalAmount})"
                                    title="Click para ver desglose de ventas">
                                    ${transaccionCount} transacciones
                                    <i class="fas fa-search ms-1"></i>
                                </strong>
                            </td>
                            <td class="fw-bold text-success">$${totalAmount.toLocaleString('es-CL')}</td>
                            <td class="text-muted">$${transaccionPromedio.toLocaleString('es-CL', {maximumFractionDigits: 0})}</td>
                        </tr>
                        `;
                    }
                    return '';
                }

                // ... Se repite el código para cada variable ...

                if (counters[0].boleta !== undefined && counters[0].boleta !== null) {
                    tablaCounters.innerHTML += generarFilaMetodoPago('Efectivo', counters[0].boleta, 'boleta_count', '💵', 'boleta');
                }

            
                if (counters[0].debito !== undefined && counters[0].debito !== null) {
                    tablaCounters.innerHTML += generarFilaMetodoPago('Débito', counters[0].debito, 'debito_count', '💳', 'debito');
                }

            
                if (counters[0].banco !== undefined && counters[0].banco !== null) {
                    tablaCounters.innerHTML += generarFilaMetodoPago('Transbank', counters[0].banco, 'banco_count', '🏦', 'banco');
                }

                if (counters[0].edenred !== undefined && counters[0].edenred !== null) {
                    tablaCounters.innerHTML += generarFilaMetodoPago('Edenred', counters[0].edenred, 'edenred_count', '🍽️', 'edenred');
                }

              

                if (counters[0].sodexo !== undefined && counters[0].sodexo !== null) {
                    tablaCounters.innerHTML += generarFilaMetodoPago('Sodexo', counters[0].sodexo, 'sodexo_count', '🍽️', 'sodexo');
                }

                if (counters[0].amipass !== undefined && counters[0].amipass !== null) {
                    tablaCounters.innerHTML += generarFilaMetodoPago('Amipass', counters[0].amipass, 'amipass_count', '🍽️', 'amipass');
                }
                
                if (counters[0].rappi !== undefined && counters[0].rappi !== null) {
                    tablaCounters.innerHTML += generarFilaMetodoPago('Rappi', counters[0].rappi, 'rappi_count', '📱', 'rappi');
                }
                
                if (counters[0].uber !== undefined && counters[0].uber !== null) {
                    tablaCounters.innerHTML += generarFilaMetodoPago('Uber Eats', counters[0].uber, 'uber_count', '🚗', 'uber');
                }

                if (counters[0].credito !== undefined && counters[0].credito !== null) {
                    tablaCounters.innerHTML += generarFilaMetodoPago('Crédito', counters[0].credito, 'credito_count', '💳', 'credito');
                }

                if (counters[0].transferencia !== undefined && counters[0].transferencia !== null) {
                    tablaCounters.innerHTML += generarFilaMetodoPago('Transferencia', counters[0].transferencia, 'transferencia_count', '🏦', 'transferencia');
                }

                if (counters[0].junaeb !== undefined && counters[0].junaeb !== null) {
                    tablaCounters.innerHTML += generarFilaMetodoPago('Junaeb', counters[0].junaeb, 'junaeb_count', '🎓', 'junaeb');
                }

                if (counters[0].pedidos_ya !== undefined && counters[0].pedidos_ya !== null) {
                    tablaCounters.innerHTML += generarFilaMetodoPago('Pedidos Ya', counters[0].pedidos_ya, 'pedidos_ya_count', '📱', 'pedidos_ya');
                }

                if (counters[0].pluxee !== undefined && counters[0].pluxee !== null) {
                    tablaCounters.innerHTML += generarFilaMetodoPago('Pluxee', counters[0].pluxee, 'pluxee_count', '🍽️', 'pluxee');
                }

                if (counters[0].banco_chile_20 !== undefined && counters[0].banco_chile_20 !== null) {
                    tablaCounters.innerHTML += generarFilaMetodoPago('Banco Chile 20%', counters[0].banco_chile_20, 'banco_chile_20_count', '🏦', 'banco_chile_20');
                }

                // MONTO INICIAL (del arqueo de caja)
                if (montoInicial > 0) {
                    tablaCounters.innerHTML += `
                    <tr class="table-info">
                        <td colspan="4" class="fw-bold text-info">
                            💰 Monto Inicial: +$${Math.round(montoInicial).toLocaleString('es-CL')}
                        </td>
                    </tr>
                    `;
                }

                // GASTOS DEL DÍA
                if (counters[0].expenses_day !== undefined && counters[0].expenses_day > 0) {
                    tablaCounters.innerHTML += `
                    <tr class="table-danger">
                        <td colspan="4" class="fw-bold text-danger">
                            💸 Total Gastos: -$${Math.round(counters[0].expenses_day).toLocaleString('es-CL')}
                        </td>
                    </tr>
                    `;
                }

                if (counters[0].balanceTotal !== undefined) {
                    tablaCounters.innerHTML += `
                    <tr class="table-light">
                        <td colspan="2" class="fw-bold">Saldo Total (Ventas)</td>
                        <td colspan="2" class="fw-bold text-primary">$${Math.round(counters[0].balanceTotal).toLocaleString('es-CL')}</td>
                    </tr>
                    `;
                }

                // RESUMEN FINAL (SOLO VENTAS - GASTOS)
                if (counters[0].balanceTotal !== undefined) {
                    const ventasTotal = counters[0].balanceTotal || 0;
                    const gastosTotal = counters[0].expenses_day || 0;
                    const resumenFinal = ventasTotal - gastosTotal; // NO sumar monto inicial
                    
                    // Calcular efectivo total en caja (solo informativo)
                    const efectivoEnCaja = montoInicial + ventasTotal - gastosTotal;
                    
                    console.log('💰 Cálculo final:', {
                        montoInicial: montoInicial,
                        ventas: ventasTotal,
                        gastos: gastosTotal,
                        resumenFinal: resumenFinal,
                        efectivoEnCaja: efectivoEnCaja
                    });
                    
                    tablaCounters.innerHTML += `
                    <tr class="table-success">
                        <td colspan="2" class="fw-bold">Resumen Final del Día</td>
                        <td colspan="2" class="fw-bold text-success">$${Math.round(resumenFinal).toLocaleString('es-CL')}</td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="4" class="text-muted small text-center">
                            (Ventas - Gastos)
                        </td>
                    </tr>
                    `;
                    
                    // Mostrar saldo total esperado en caja (informativo)
                    if (montoInicial > 0) {
                        tablaCounters.innerHTML += `
                        <tr class="table-info">
                            <td colspan="2" class="fw-bold text-info">💰 Saldo Inicial + Ventas</td>
                            <td colspan="2" class="fw-bold text-info">$${Math.round(efectivoEnCaja).toLocaleString('es-CL')}</td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="4" class="text-muted small text-center">
                                (Monto Inicial + Ventas - Gastos)
                            </td>
                        </tr>
                        `;
                    }
                }
            }); // Fin del callback de __conection para ventas
        }
        getCounters();
        
        // ACTUALIZAR RESUMEN TOTAL
        console.log("🎯 LLAMANDO updateResumenTotal con fechas:", { startDate, endDate });
        updateResumenTotal(startDate, endDate, counters);


        function getSellByHour(startDate, endDate) {
            // Destruir gráfico existente si existe
            if (window.sellsByHourChart && typeof window.sellsByHourChart.destroy === 'function') {
                window.sellsByHourChart.destroy();
            }

            __conection({
                url: generarURLApi(`/web/getAppSellsByHour?id=${id}&startDate=${startDate}&endDate=${endDate}`),
                header: credentials(),
                dev: true,
                method: 'GET'

            }, {}, function (request) {

                console.log('📊 Datos ventas por hora:', request);

                const sells = [];
                for (const app in request) {
                    if (request[app].original) {
                        sells.push(request[app].original);
                    }
                }

                const labels = [];
                const data = [];

                if (sells.length > 0 && sells[0]) {
                    // Consolidar datos de múltiples días
                    const hourlyData = {};
                    
                    sells.forEach(sellData => {
                        for (const hora in sellData) {
                            if (sellData.hasOwnProperty(hora)) {
                                if (!hourlyData[hora]) {
                                    hourlyData[hora] = 0;
                                }
                                hourlyData[hora] += sellData[hora] || 0;
                            }
                        }
                    });

                    // Ordenar por hora y preparar datos
                    const sortedHours = Object.keys(hourlyData).sort();
                    sortedHours.forEach(hora => {
                        labels.push(`${hora}:00`);
                        data.push(hourlyData[hora]);
                    });
                }

                console.log('📊 Labels:', labels);
                console.log('📊 Data:', data);

                var sellsByHourCanvas = document.getElementById('sellsByHourChart');

                if (sellsByHourCanvas) {
                    window.sellsByHourChart = new Chart(sellsByHourCanvas, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Ventas por Hora',
                                data: data,
                                fill: false,
                                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                borderColor: '#007bff',
                                borderWidth: 3,
                                pointBackgroundColor: '#007bff',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Cantidad de Ventas'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Hora del Día'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                }
                            }
                        }
                    });
                }
            });
        }
        getSellByHour(startDate,endDate);

        function getTopSells(startDate, endDate) {
            // Destruir gráfico existente si existe
            if (window.topSellsChart && typeof window.topSellsChart.destroy === 'function') {
                window.topSellsChart.destroy();
            }

            __conection({
                url: generarURLApi(`/web/getAppTopSells?id=${id}&startDate=${startDate}&endDate=${endDate}`),
                header: credentials(),
                dev: true,
                method: 'GET'

            }, {}, function (request) {

                console.log('📊 Datos top productos:', request);

                const topSells = [];
                for (const app in request) {
                    if (request[app].original) {
                        topSells.push(request[app].original);
                    }
                }

                const labels = [];
                const data = [];
                const backgroundColors = [
                    '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'
                ];

                if (topSells.length > 0 && topSells[0]) {
                    // Convertir objeto a array y ordenar por cantidad
                    const productsArray = [];
                    for (const clave in topSells[0]) {
                        if (topSells[0][clave] && topSells[0][clave].product_name) {
                            productsArray.push({
                                name: topSells[0][clave].product_name,
                                quantity: parseInt(topSells[0][clave].total_quantity) || 0
                            });
                        }
                    }

                    // Ordenar por cantidad descendente y tomar top 5
                    productsArray
                        .sort((a, b) => b.quantity - a.quantity)
                        .slice(0, 5)
                        .forEach((product, index) => {
                            labels.push(product.name);
                            data.push(product.quantity);
                        });
                }

                console.log('📊 Top 5 Productos:', { labels, data });

                var topSellsCanvas = document.getElementById('topSellsChart');
                
                if (topSellsCanvas) {
                    window.topSellsChart = new Chart(topSellsCanvas, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Cantidad Vendida',
                                data: data,
                                backgroundColor: backgroundColors.slice(0, data.length),
                                borderColor: backgroundColors.slice(0, data.length),
                                borderWidth: 2,
                                borderRadius: 8,
                                borderSkipped: false,
                            }],
                        },
                        options: {
                            responsive: true,
                            indexAxis: 'y', // Barras horizontales
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Cantidad Vendida'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Productos'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                title: {
                                    display: true,
                                    text: 'Top 5 Productos Más Vendidos',
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    }
                                }
                            }
                        }
                    });
                }
            });
        }
        getTopSells(startDate,endDate);

        // Nueva función para gráfico de métodos de pago
        function getPaymentMethodsChart(startDate, endDate) {
            // Destruir gráfico existente si existe
            if (window.paymentMethodsChart && typeof window.paymentMethodsChart.destroy === 'function') {
                window.paymentMethodsChart.destroy();
            }

            __conection({
                url: generarURLApi(`/web/getAppSells?id=${id}&startDate=${startDate}&endDate=${endDate}&efectivo=true&debito=true&transferencia=true&credito=true&rappi=true&uber=true&boleta=true&per_page=10000`),
                header: credentials(),
                dev: true,
                method: 'GET'
            }, {}, function (request) {
                console.log('💳 Datos métodos de pago:', request);

                const sells = [];
                // Extraer ventas de la respuesta
                if (Array.isArray(request)) {
                    sells.push(...request);
                } else {
                    for (const app in request) {
                        if (request[app] && request[app].original && Array.isArray(request[app].original)) {
                            sells.push(...request[app].original);
                        } else if (request[app] && Array.isArray(request[app])) {
                            sells.push(...request[app]);
                        }
                    }
                }

                // Calcular métodos de pago
                const paymentMethods = {};
                let totalVentas = 0;

                sells.forEach(sell => {
                    const method = sell.paymode || 'efectivo';
                    if (!paymentMethods[method]) {
                        paymentMethods[method] = 0;
                    }
                    paymentMethods[method]++;
                    totalVentas++;
                });

                // Preparar datos para el gráfico
                const labels = [];
                const data = [];
                const percentages = [];
                const backgroundColors = [
                    '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8',
                    '#6f42c1', '#e83e8c', '#fd7e14', '#20c997', '#6c757d'
                ];

                Object.entries(paymentMethods)
                    .sort((a, b) => b[1] - a[1]) // Ordenar por cantidad descendente
                    .forEach(([method, count], index) => {
                        const percentage = totalVentas > 0 ? ((count / totalVentas) * 100).toFixed(1) : 0;
                        labels.push(`${method.charAt(0).toUpperCase() + method.slice(1)} (${percentage}%)`);
                        data.push(count);
                        percentages.push(percentage);
                    });

                console.log('💳 Métodos de pago procesados:', { labels, data, percentages });

                var paymentCanvas = document.getElementById('paymentMethodsChart');
                
                if (paymentCanvas) {
                    window.paymentMethodsChart = new Chart(paymentCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: backgroundColors.slice(0, data.length),
                                borderColor: '#ffffff',
                                borderWidth: 2,
                                hoverBorderWidth: 3
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true,
                                        font: {
                                            size: 12
                                        }
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Distribución de Métodos de Pago',
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.parsed;
                                            const percentage = percentages[context.dataIndex];
                                            return `${label}: ${value} ventas (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        }
        getPaymentMethodsChart(startDate, endDate);

    });
}

// Función para obtener métodos de pago seleccionados
function getSelectedPaymentMethods() {
    // Por defecto, incluir todos los métodos de pago disponibles
    const allPaymentMethods = [
        'efectivo',
        'debito', 
        'credito',
        'transferencia',
        'cheque',
        'banco',
        'edenred',
        'multicaja',
        'sodexo',
        'amipass',
        'convenio_empresa',
        'rappi',
        'uber',
        'junaeb',
        'nota_de_credito',
        'pedidos_ya',
        'pluxee',
        'banco_chile_20',
        'guia_despacho',
        'fastSell',
        'boleta',
        'factura',
        'noSii'
    ];
    
    console.log('💳 Métodos de pago incluidos:', allPaymentMethods);
    return allPaymentMethods;
}

// Función para descargar Excel mejorada
function downloadCustomExcel() {
    Swal.fire({
        title: 'Generando Reporte Completo...',
        text: 'Por favor espera mientras se genera el reporte',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const selectedMethods = getSelectedPaymentMethods();
    
    // Obtener fechas del filtro actual del sistema
    const startDateElement = document.getElementById('startDate');
    const endDateElement = document.getElementById('endDate');
    
    // USAR SIEMPRE las fechas del filtro, no defaults hardcodeados
    const startDate = startDateElement?.value || getNowDate();
    const endDate = (endDateElement?.value || getNowDate()) + ' 23:59:59';
    
    console.log('📅 FECHAS DEL FILTRO:', { startDate, endDate });
    
    // Usar el mismo endpoint que funciona para obtener datos - el de web
    const paymentParams = [
        'fastSell=true',
        'boleta=true', 
        'factura=true',
        'noSii=true',
        'amipass=true',
        'rappi=true',
        'uber=true',
        'junaeb=true',
        'multicaja=true',
        'edenred=true',
        'sodexo=true',
        'convenio_empresa=true',
        'debito=true',
        'credito=true',
        'transferencia=true',
        'cheque=true',
        'banco=true',
        'pluxee=true',
        'pedidos_ya=true',
        'guia_despacho=true',
        'banco_chile_20=true',
        'nota_de_credito=true',
        'efectivo=true'
    ].join('&');
    
    // Usar el endpoint de ventas web con parámetros de paginación para obtener todos los datos
    const url = generarURLApi(`/web/getAppSells?id=${id}&startDate=${startDate}&endDate=${endDate}&${paymentParams}&per_page=10000&orderBy=created_at&orderBy_date=asc`);
    
    console.log('🚀 DESCARGANDO EXCEL CON FILTROS...');
    console.log('📅 Fechas del filtro:', { startDate, endDate });
    console.log('🌐 URL:', url);

    __conection({
        url: url,
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        console.log('✅ RESPUESTA DEL SERVIDOR:', request);
        
        try {
            // Procesar los datos y crear Excel
            const sells = [];
            
            // MÚLTIPLES FORMAS DE EXTRAER DATOS
            if (Array.isArray(request)) {
                console.log('📊 Datos en array directo');
                sells.push(...request);
            } else {
                for (const app in request) {
                    console.log(`🔍 Revisando app: ${app}`, request[app]);
                    if (request[app] && request[app].original && Array.isArray(request[app].original)) {
                        console.log(`✅ Encontradas ${request[app].original.length} ventas en ${app}`);
                        sells.push(...request[app].original);
                    } else if (request[app] && Array.isArray(request[app])) {
                        console.log(`✅ Encontradas ${request[app].length} ventas directas en ${app}`);
                        sells.push(...request[app]);
                    }
                }
            }
            
            console.log('📋 TOTAL DE VENTAS PROCESADAS:', sells.length);
            
            if (sells.length === 0) {
                console.log('❌ SIN VENTAS encontradas para el período seleccionado');
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin ventas en el período',
                    text: `No se encontraron ventas entre ${startDate} y ${endDate.split(' ')[0]}`,
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Si hay ventas, crear Excel
            console.log('🎉 ¡CREANDO EXCEL CON VENTAS!');
            createExcelFromSells(sells, startDate, endDate);
            
        } catch (error) {
            console.error('❌ ERROR CRÍTICO:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error procesando datos',
                text: `Error: ${error.message}`,
                confirmButtonText: 'Entendido'
            });
        }
    }, function(error) {
        console.error('❌ ERROR DE CONEXIÓN:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar al servidor',
            confirmButtonText: 'Entendido'
        });
    });
}

// Función para crear Excel desde los datos de ventas
function createExcelFromSells(sells, startDate, endDate) {
    console.log('📊 Creando Excel con datos:', { sells: sells.length, startDate, endDate });
    
    // Siempre crear Excel completo con todas las hojas
    createFranchiseExcel(sells, startDate, endDate);
}

// Función para crear Excel completo para franquiciados
function createFranchiseExcel(sells, startDate, endDate) {
    console.log('📊 Creando Excel completo para franquiciados...');
    
    // Calcular estadísticas generales
    const totalOrders = sells.length;
    const totalAmount = sells.reduce((sum, sell) => sum + (parseFloat(sell.total) || 0), 0);
    const averageTicket = totalOrders > 0 ? totalAmount / totalOrders : 0;
    const maxSale = Math.max(...sells.map(sell => parseFloat(sell.total) || 0));
    const minSale = Math.min(...sells.map(sell => parseFloat(sell.total) || 0));
    
    // Calcular productos más vendidos
    const productSales = {};
    let totalProductos = 0; // DEBUG: Total calculado de productos
    let totalVentas = 0; // DEBUG: Total de ventas
    
    sells.forEach(sell => {
        totalVentas += parseFloat(sell.total) || 0; // DEBUG
        
        if (sell.products && Array.isArray(sell.products)) {
            sell.products.forEach(product => {
                const name = product.name || product.product_name || 'Producto sin nombre';
                // Usar 'quantity' como campo principal basado en el controlador
                const quantity = parseInt(product.quantity || product.qty || product.pivot?.qty || 1);
                
                // Calcular el total: cantidad × precio unitario
                const unitPrice = parseFloat(product.price || product.totalPrice || product.total || 0);
                const productTotal = quantity * unitPrice;
                
                totalProductos += productTotal; // DEBUG: Acumular total de productos
                
                if (!productSales[name]) {
                    productSales[name] = { cantidad: 0, ingresos: 0 };
                }
                productSales[name].cantidad += quantity;
                productSales[name].ingresos += productTotal;
            });
        }
    });
    
    // DEBUG: Mostrar diferencia en consola
    console.log('🔍 DEBUG TOTALES:');
    console.log('Total Ventas:', totalVentas.toFixed(2));
    console.log('Total Productos:', totalProductos.toFixed(2));
    console.log('Diferencia:', (totalVentas - totalProductos).toFixed(2));
    console.log('Ventas sin productos:', sells.filter(s => !s.products || s.products.length === 0).length);
    
    // Calcular métodos de pago
    const paymentMethods = {};
    sells.forEach(sell => {
        // Usar el mismo campo que se usa en sells.js para mostrar en la tabla
        const method = sell.paymode || sell.paymentType || sell.payment_type || sell.payment_method || 
                      sell.metodo_pago || sell.tipo_pago || 'efectivo';
        if (!paymentMethods[method]) {
            paymentMethods[method] = { cantidad: 0, total: 0 };
        }
        paymentMethods[method].cantidad++;
        paymentMethods[method].total += parseFloat(sell.total) || 0;
    });
    
    // Determinar nombre del período
    const start = new Date(startDate);
    const end = new Date(endDate.split(' ')[0]);
    const isDateRange = start.getTime() !== end.getTime();
    const periodoNombre = isDateRange ? `${startDate}_${endDate.split(' ')[0]}` : startDate;
    
    // Crear contenido Excel con múltiples hojas
    let excelContent = `<?xml version="1.0"?>
    <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
     xmlns:o="urn:schemas-microsoft-com:office:office"
     xmlns:x="urn:schemas-microsoft-com:office:excel"
     xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
     xmlns:html="http://www.w3.org/TR/REC-html40">
     <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
      <Title>Reporte Franquiciados ${periodoNombre}</Title>
      <Author>Sistema Fagotto ERP</Author>
     </DocumentProperties>
     <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
      <WindowHeight>13020</WindowHeight>
      <WindowWidth>25600</WindowWidth>
      <WindowTopX>120</WindowTopX>
      <WindowTopY>120</WindowTopY>
      <ProtectStructure>False</ProtectStructure>
      <ProtectWindows>False</ProtectWindows>
     </ExcelWorkbook>
     <Styles>
      <Style ss:ID="Default" ss:Name="Normal">
       <Alignment ss:Vertical="Bottom"/>
       <Borders/>
       <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
       <Interior/>
       <NumberFormat/>
       <Protection/>
      </Style>
      <Style ss:ID="HeaderStyle">
       <Font ss:FontName="Calibri" ss:Bold="1" ss:Size="12" ss:Color="#FFFFFF"/>
       <Interior ss:Color="#4472C4" ss:Pattern="Solid"/>
       <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      </Style>
     </Styles>`;

    // HOJA 1: Resumen Ejecutivo
    excelContent += `
     <Worksheet ss:Name="Resumen Ejecutivo">
      <Table>
       <Column ss:Width="150"/>
       <Column ss:Width="150"/>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Reporte Completo para Franquiciados</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Período: ${startDate} - ${endDate.split(' ')[0]}</Data></Cell>
       </Row>
       <Row><Cell><Data ss:Type="String"></Data></Cell></Row>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Estadística</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Valor</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">Ordenes Totales</Data></Cell>
        <Cell><Data ss:Type="Number">${totalOrders}</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">Ingresos Totales</Data></Cell>
        <Cell><Data ss:Type="Number">${totalAmount.toFixed(2)}</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">Ticket Promedio</Data></Cell>
        <Cell><Data ss:Type="Number">${averageTicket.toFixed(2)}</Data></Cell>
       </Row>
      </Table>
     </Worksheet>`;

    // ========== RANKING COMPLETO DE TODOS LOS PRODUCTOS ==========
    excelContent += `
     <Worksheet ss:Name="Todos los Productos">
      <Table>
       <Column ss:Width="50"/>
       <Column ss:Width="200"/>
       <Column ss:Width="100"/>
       <Column ss:Width="120"/>
       <Column ss:Width="80"/>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Posición</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Producto</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Cantidad Vendida</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Ingresos Totales</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">% del Total</Data></Cell>
       </Row>`;
    
    // Ordenar TODOS los productos por cantidad vendida (de mayor a menor)
    const allProductsRanked = Object.entries(productSales)
        .sort((a, b) => b[1].cantidad - a[1].cantidad);
    
    allProductsRanked.forEach(([name, data], index) => {
        const percentage = totalAmount > 0 ? ((data.ingresos / totalAmount) * 100).toFixed(1) : 0;
        excelContent += `
        <Row>
         <Cell><Data ss:Type="Number">${index + 1}</Data></Cell>
         <Cell><Data ss:Type="String">${name}</Data></Cell>
         <Cell><Data ss:Type="Number">${data.cantidad}</Data></Cell>
         <Cell><Data ss:Type="Number">${data.ingresos.toFixed(2)}</Data></Cell>
         <Cell><Data ss:Type="String">${percentage}%</Data></Cell>
        </Row>`;
    });
    
    excelContent += `
      </Table>
     </Worksheet>`;

    // ========== MÉTODOS DE PAGO ==========
    excelContent += `
     <Worksheet ss:Name="Métodos de Pago">
      <Table>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Método de Pago</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Cantidad de Transacciones</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Total</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Porcentaje</Data></Cell>
       </Row>`;
    
    Object.entries(paymentMethods).forEach(([method, data]) => {
        const percentage = totalAmount > 0 ? ((data.total / totalAmount) * 100).toFixed(1) : 0;
        excelContent += `
        <Row>
         <Cell><Data ss:Type="String">${method}</Data></Cell>
         <Cell><Data ss:Type="Number">${data.cantidad}</Data></Cell>
         <Cell><Data ss:Type="Number">${data.total.toFixed(2)}</Data></Cell>
         <Cell><Data ss:Type="String">${percentage}%</Data></Cell>
        </Row>`;
    });
    
    excelContent += `
      </Table>
     </Worksheet>`;

    // ========== NUEVA FUNCIONALIDAD: DETALLE DIARIO DE VENTAS ==========
    // Agrupar ventas por día
    const ventasPorDia = {};
    sells.forEach(sell => {
        const fechaVenta = sell.created_at || sell.date || sell.fecha;
        if (!fechaVenta) return;
        
        // Extraer solo la fecha (sin hora)
        const diaKey = fechaVenta.split(' ')[0]; // "2025-01-15"
        
        if (!ventasPorDia[diaKey]) {
            ventasPorDia[diaKey] = [];
        }
        ventasPorDia[diaKey].push(sell);
    });
    
    // Crear una hoja por cada día
    const diasOrdenados = Object.keys(ventasPorDia).sort();
    
    diasOrdenados.forEach(dia => {
        const ventasDelDia = ventasPorDia[dia];
        
        // Calcular productos vendidos en este día
        const productosDia = {};
        let totalDia = 0;
        let transaccionesDia = ventasDelDia.length;
        
        ventasDelDia.forEach(sell => {
            totalDia += parseFloat(sell.total) || 0;
            
            if (sell.products && Array.isArray(sell.products)) {
                sell.products.forEach(product => {
                    const name = product.name || product.product_name || 'Sin nombre';
                    const quantity = parseInt(product.quantity || product.qty || product.pivot?.qty || 1);
                    
                    // Calcular el total: cantidad × precio unitario
                    const unitPrice = parseFloat(product.price || product.totalPrice || product.total || 0);
                    const productTotal = quantity * unitPrice;
                    
                    if (!productosDia[name]) {
                        productosDia[name] = { cantidad: 0, ingresos: 0 };
                    }
                    productosDia[name].cantidad += quantity;
                    productosDia[name].ingresos += productTotal;
                });
            }
        });
        
        // Calcular métodos de pago del día
        const pagosDia = {};
        ventasDelDia.forEach(sell => {
            const method = sell.paymode || sell.paymentType || 'efectivo';
            if (!pagosDia[method]) {
                pagosDia[method] = { cantidad: 0, total: 0 };
            }
            pagosDia[method].cantidad++;
            pagosDia[method].total += parseFloat(sell.total) || 0;
        });
        
        // Formatear nombre del día
        const fechaObj = new Date(dia + 'T00:00:00');
        const nombreDia = fechaObj.toLocaleDateString('es-CL', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric' 
        });
        
        // Crear hoja del día
        excelContent += `
     <Worksheet ss:Name="Día ${nombreDia}">
      <Table>
       <Column ss:Width="200"/>
       <Column ss:Width="120"/>
       <Column ss:Width="120"/>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">RESUMEN DEL DÍA: ${nombreDia}</Data></Cell>
       </Row>
       <Row><Cell><Data ss:Type="String"></Data></Cell></Row>
       <Row>
        <Cell><Data ss:Type="String">Total Transacciones:</Data></Cell>
        <Cell><Data ss:Type="Number">${transaccionesDia}</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">Total Ventas:</Data></Cell>
        <Cell><Data ss:Type="Number">${totalDia.toFixed(2)}</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">Ticket Promedio:</Data></Cell>
        <Cell><Data ss:Type="Number">${(totalDia / transaccionesDia).toFixed(2)}</Data></Cell>
       </Row>
       <Row><Cell><Data ss:Type="String"></Data></Cell></Row>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">PRODUCTOS VENDIDOS</Data></Cell>
       </Row>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Producto</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Cantidad</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Total Vendido</Data></Cell>
       </Row>`;
        
        // Ordenar productos del día por cantidad
        const productosOrdenados = Object.entries(productosDia)
            .sort((a, b) => b[1].cantidad - a[1].cantidad);
        
        productosOrdenados.forEach(([name, data]) => {
            excelContent += `
        <Row>
         <Cell><Data ss:Type="String">${name}</Data></Cell>
         <Cell><Data ss:Type="Number">${data.cantidad}</Data></Cell>
         <Cell><Data ss:Type="Number">${data.ingresos.toFixed(2)}</Data></Cell>
        </Row>`;
        });
        
        // Métodos de pago del día
        excelContent += `
       <Row><Cell><Data ss:Type="String"></Data></Cell></Row>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">MÉTODOS DE PAGO</Data></Cell>
       </Row>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Método</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Transacciones</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Total</Data></Cell>
       </Row>`;
        
        Object.entries(pagosDia).forEach(([method, data]) => {
            excelContent += `
        <Row>
         <Cell><Data ss:Type="String">${method}</Data></Cell>
         <Cell><Data ss:Type="Number">${data.cantidad}</Data></Cell>
         <Cell><Data ss:Type="Number">${data.total.toFixed(2)}</Data></Cell>
        </Row>`;
        });
        
        excelContent += `
      </Table>
     </Worksheet>`;
    });

    // ========== HOJA DE NOTAS Y ADVERTENCIAS ==========
    excelContent += `
     <Worksheet ss:Name="⚠️ Notas Importantes">
      <Table>
       <Column ss:Width="600"/>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">⚠️ NOTAS IMPORTANTES SOBRE ESTE REPORTE</Data></Cell>
       </Row>
       <Row><Cell><Data ss:Type="String"></Data></Cell></Row>
       <Row>
        <Cell><Data ss:Type="String">📊 INFORMACIÓN SOBRE DIFERENCIAS EN TOTALES:</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String"></Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">Si la suma de productos no coincide exactamente con el total de ventas, esto puede deberse a:</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">  • Promociones activas que modifican precios de productos</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">  • Descuentos aplicados a nivel de venta completa</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">  • Propinas agregadas al total</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">  • Cargos de servicio o delivery</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">  • Redondeos en transacciones</Data></Cell>
       </Row>
       <Row><Cell><Data ss:Type="String"></Data></Cell></Row>
       <Row>
        <Cell><Data ss:Type="String">🔧 ESTAMOS TRABAJANDO EN:</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">  • Mejorar el manejo de promociones en los reportes</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">  • Incluir detalle de descuentos aplicados</Data></Cell>
       </Row>
       <Row>
        <Cell><Data ss:Type="String">  • Separar propinas y cargos adicionales</Data></Cell>
       </Row>
       <Row><Cell><Data ss:Type="String"></Data></Cell></Row>
       <Row>
        <Cell><Data ss:Type="String">ℹ️ El total de ventas siempre muestra el monto real facturado y es el valor correcto para efectos contables.</Data></Cell>
       </Row>
      </Table>
     </Worksheet>`;

    // Cerrar workbook
    excelContent += `
    </Workbook>`;

    // Descargar archivo
    const filename = `Reporte_Franquiciados_${periodoNombre}.xls`;
    downloadExcelFile(excelContent, filename, sells.length);
}

// Función para crear Excel simple con 2 hojas: Ventas + Ventas por Hora
function createSimpleExcel(sells, startDate, endDate) {
    console.log('📊 Creando Excel simplificado con 2 hojas...');
    
    // Calcular totales generales
    const totalOrders = sells.length;
    const totalAmount = sells.reduce((sum, sell) => sum + (parseFloat(sell.total) || 0), 0);
    
    // Determinar nombre del período
    const start = new Date(startDate);
    const end = new Date(endDate.split(' ')[0]);
    const isDateRange = start.getTime() !== end.getTime();
    const periodoNombre = isDateRange ? `${startDate}_${endDate.split(' ')[0]}` : startDate;
    
    // Crear contenido Excel
    let excelContent = `<?xml version="1.0"?>
    <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
     xmlns:o="urn:schemas-microsoft-com:office:office"
     xmlns:x="urn:schemas-microsoft-com:office:excel"
     xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
     xmlns:html="http://www.w3.org/TR/REC-html40">
     <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
      <Title>Reporte de Ventas ${periodoNombre}</Title>
      <Author>Sistema Fagotto ERP</Author>
      <Created>${new Date().toISOString()}</Created>
     </DocumentProperties>
     <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
      <WindowHeight>8000</WindowHeight>
      <WindowWidth>15000</WindowWidth>
      <WindowTopX>0</WindowTopX>
      <WindowTopY>0</WindowTopY>
      <ProtectStructure>False</ProtectStructure>
      <ProtectWindows>False</ProtectWindows>
     </ExcelWorkbook>
     <Styles>
      <Style ss:ID="Default" ss:Name="Normal">
       <Alignment ss:Vertical="Bottom"/>
       <Borders/>
       <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
       <Interior/>
       <NumberFormat/>
       <Protection/>
      </Style>
      <Style ss:ID="HeaderStyle">
       <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
       <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
       </Borders>
       <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>
       <Interior ss:Color="#366092" ss:Pattern="Solid"/>
      </Style>
      <Style ss:ID="DataStyle">
       <Alignment ss:Vertical="Center"/>
       <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
       </Borders>
       <Font ss:FontName="Calibri" ss:Size="10"/>
      </Style>
      <Style ss:ID="TotalStyle">
       <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
       <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
       </Borders>
       <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>
       <Interior ss:Color="#4CAF50" ss:Pattern="Solid"/>
      </Style>
     </Styles>
     
     <!-- HOJA 1: TODAS LAS VENTAS -->
     <Worksheet ss:Name="Ventas">
      <Table>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">ID Venta</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Total</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Método de Pago</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Fecha y Hora</Data></Cell>
       </Row>`;

    // Agregar TODAS las ventas del período
    sells.forEach(sell => {
        excelContent += `
       <Row>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${sell.id || 0}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${sell.total || 0}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="String">${sell.payment_method || 'N/A'}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="String">${sell.created_at || 'N/A'}</Data></Cell>
       </Row>`;
    });

    // Fila de totales
    excelContent += `
       <Row>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">TOTALES</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="Number">${totalAmount}</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">Órdenes: ${totalOrders}</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">${periodoNombre}</Data></Cell>
       </Row>`;

    excelContent += `
      </Table>
     </Worksheet>`;
    
    // HOJA 2: VENTAS POR HORA
    excelContent += createVentasPorHoraSheet(sells, periodoNombre);
    
    excelContent += `
    </Workbook>`;
    
    // Determinar nombre del archivo
    const fileName = isDateRange ? 
        `ventas_${startDate}_${endDate.split(' ')[0]}.xls` : 
        `ventas_${startDate}.xls`;
    
    downloadExcelFile(excelContent, fileName, sells.length);
}

// Función para crear Excel de un solo día
function createSingleDayExcel(sells, startDate, endDate) {
    // Calcular totales
    const totalOrders = sells.length;
    const totalAmount = sells.reduce((sum, sell) => sum + (parseFloat(sell.total) || 0), 0);
    
    // Crear contenido Excel usando formato XML compatible con Excel
    let excelContent = `<?xml version="1.0"?>
    <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
     xmlns:o="urn:schemas-microsoft-com:office:office"
     xmlns:x="urn:schemas-microsoft-com:office:excel"
     xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
     xmlns:html="http://www.w3.org/TR/REC-html40">
     <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
      <Title>Reporte de Ventas</Title>
      <Author>Sistema Fagotto ERP</Author>
      <Created>${new Date().toISOString()}</Created>
     </DocumentProperties>
     <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
      <WindowHeight>8000</WindowHeight>
      <WindowWidth>15000</WindowWidth>
      <WindowTopX>0</WindowTopX>
      <WindowTopY>0</WindowTopY>
      <ProtectStructure>False</ProtectStructure>
      <ProtectWindows>False</ProtectWindows>
     </ExcelWorkbook>
     <Styles>
      <Style ss:ID="Default" ss:Name="Normal">
       <Alignment ss:Vertical="Bottom"/>
       <Borders/>
       <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
       <Interior/>
       <NumberFormat/>
       <Protection/>
      </Style>
      <Style ss:ID="HeaderStyle">
       <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
       <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
       </Borders>
       <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>
       <Interior ss:Color="#366092" ss:Pattern="Solid"/>
      </Style>
      <Style ss:ID="DataStyle">
       <Alignment ss:Vertical="Center"/>
       <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
       </Borders>
       <Font ss:FontName="Calibri" ss:Size="10"/>
      </Style>
      <Style ss:ID="TotalStyle">
       <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
       <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
       </Borders>
       <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>
       <Interior ss:Color="#4CAF50" ss:Pattern="Solid"/>
      </Style>
     </Styles>
     <Worksheet ss:Name="Ventas ${startDate}">
      <Table>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">ID Venta</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Total</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Método de Pago</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Fecha</Data></Cell>
       </Row>`;

    // Agregar datos
    sells.forEach(sell => {
        excelContent += `
       <Row>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${sell.id || 0}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${sell.total || 0}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="String">${sell.payment_method || 'N/A'}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="String">${sell.created_at || 'N/A'}</Data></Cell>
       </Row>`;
    });

    // Agregar fila de totales
    excelContent += `
       <Row>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">TOTALES</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="Number">${totalAmount}</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">Órdenes: ${totalOrders}</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">${startDate}</Data></Cell>
       </Row>`;

    excelContent += `
      </Table>
     </Worksheet>`;
    
    // NUEVA HOJA: VENTAS POR HORA
    excelContent += createVentasPorHoraSheet(sells, startDate);
    
    excelContent += `
    </Workbook>`;
    
    downloadExcelFile(excelContent, `reporte_ventas_${startDate}_${endDate}.xls`, sells.length);
}

// Función para crear Excel con múltiples días
function createMultiDayExcel(sells, startDate, endDate) {
    // Agrupar ventas por día
    const sellsByDay = {};
    
    sells.forEach(sell => {
        const sellDate = sell.created_at ? sell.created_at.split(' ')[0] : startDate;
        if (!sellsByDay[sellDate]) {
            sellsByDay[sellDate] = [];
        }
        sellsByDay[sellDate].push(sell);
    });
    
    console.log('📈 Ventas agrupadas por día:', Object.keys(sellsByDay));
    
    // Crear contenido Excel con múltiples hojas
    let excelContent = `<?xml version="1.0"?>
    <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
     xmlns:o="urn:schemas-microsoft-com:office:office"
     xmlns:x="urn:schemas-microsoft-com:office:excel"
     xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
     xmlns:html="http://www.w3.org/TR/REC-html40">
     <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
      <Title>Reporte de Ventas por Día</Title>
      <Author>Sistema Fagotto ERP</Author>
      <Created>${new Date().toISOString()}</Created>
     </DocumentProperties>
     <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
      <WindowHeight>8000</WindowHeight>
      <WindowWidth>15000</WindowWidth>
      <WindowTopX>0</WindowTopX>
      <WindowTopY>0</WindowTopY>
      <ProtectStructure>False</ProtectStructure>
      <ProtectWindows>False</ProtectWindows>
     </ExcelWorkbook>
     <Styles>
      <Style ss:ID="Default" ss:Name="Normal">
       <Alignment ss:Vertical="Bottom"/>
       <Borders/>
       <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
       <Interior/>
       <NumberFormat/>
       <Protection/>
      </Style>
      <Style ss:ID="HeaderStyle">
       <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
       <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
       </Borders>
       <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>
       <Interior ss:Color="#366092" ss:Pattern="Solid"/>
      </Style>
      <Style ss:ID="DataStyle">
       <Alignment ss:Vertical="Center"/>
       <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
       </Borders>
       <Font ss:FontName="Calibri" ss:Size="10"/>
      </Style>
      <Style ss:ID="TotalStyle">
       <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
       <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
       </Borders>
       <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>
       <Interior ss:Color="#4CAF50" ss:Pattern="Solid"/>
      </Style>
     </Styles>`;

    // Crear una hoja por cada día
    Object.keys(sellsByDay).sort().forEach(date => {
        const daySells = sellsByDay[date];
        const totalOrders = daySells.length;
        const totalAmount = daySells.reduce((sum, sell) => sum + (parseFloat(sell.total) || 0), 0);
        
        // Formatear nombre de hoja (Excel no permite ciertos caracteres)
        const sheetName = `Ventas ${date.replace(/-/g, '_')}`;
        
        excelContent += `
     <Worksheet ss:Name="${sheetName}">
      <Table>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">ID Venta</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Total</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Método de Pago</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Fecha</Data></Cell>
       </Row>`;

        // Agregar datos del día
        daySells.forEach(sell => {
            excelContent += `
       <Row>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${sell.id || 0}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${sell.total || 0}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="String">${sell.payment_method || 'N/A'}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="String">${sell.created_at || 'N/A'}</Data></Cell>
       </Row>`;
        });

        // Agregar fila de totales para el día
        excelContent += `
       <Row>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">TOTALES DÍA</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="Number">${totalAmount}</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">Órdenes: ${totalOrders}</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">${date}</Data></Cell>
       </Row>`;

        excelContent += `
      </Table>
     </Worksheet>`;
    });

    // AGREGAR HOJA DE RESUMEN DE VENTAS POR HORA (para múltiples días)
    if (Object.keys(sellsByDay).length > 1) {
        excelContent += createResumenVentasPorHora(sells, startDate, endDate);
    }

    excelContent += `
    </Workbook>`;
    
    const totalDays = Object.keys(sellsByDay).length;
    downloadExcelFile(excelContent, `reporte_ventas_${startDate}_${endDate}_${totalDays}dias.xls`, sells.length);
}

// Función helper para descargar el archivo Excel
function downloadExcelFile(excelContent, filename, sellsCount) {
    // Crear y descargar archivo Excel real
    const blob = new Blob([excelContent], {
        type: 'application/vnd.ms-excel'
    });
    
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    
    Swal.fire({
        icon: 'success',
        title: '¡Excel Generado!',
        text: `Se descargó un archivo Excel con ${sellsCount} ventas`,
        timer: 3000,
        showConfirmButton: false
    });
}

// Función helper para convertir base64 a blob
function base64ToBlob(base64, mimeType) {
    try {
        const byteCharacters = atob(base64);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        return new Blob([new Uint8Array(byteNumbers)], {type: mimeType});
    } catch (error) {
        console.error('Error convirtiendo base64 a blob:', error);
        throw new Error('Error al convertir el archivo: ' + error.message);
    }
}

// Función para crear hoja de Ventas por Hora
function createVentasPorHoraSheet(sells, date) {
    console.log('🕒 Creando análisis de ventas por hora...');
    
    // Agrupar ventas por hora
    const ventasPorHora = {};
    
    // Inicializar todas las horas del día (10 AM a 11 PM - horario real de operación)
    for (let hora = 10; hora <= 23; hora++) {
        const horaKey = `${hora.toString().padStart(2, '0')}:00-${(hora + 1).toString().padStart(2, '0')}:00`;
        ventasPorHora[horaKey] = {
            hora: horaKey,
            cantidad: 0,
            total: 0,
            ventas: []
        };
    }
    
    // Procesar cada venta
    sells.forEach(sell => {
        if (sell.created_at) {
            // Extraer hora directamente del string para evitar problemas de zona horaria
            // "2025-09-10 14:30:25" -> hora = 14
            const hora = parseInt(sell.created_at.split(' ')[1].split(':')[0]);
            
            // Debug: mostrar las primeras 10 ventas para verificar las horas
            if (sells.indexOf(sell) < 10) {
                console.log(`🕐 DEBUG Venta ${sells.indexOf(sell) + 1}: ${sell.created_at} → hora extraída: ${hora}`);
            }
            
            // Solo procesar horas de operación (10 AM a 11 PM)
            if (hora >= 10 && hora <= 23) {
                const horaKey = `${hora.toString().padStart(2, '0')}:00-${(hora + 1).toString().padStart(2, '0')}:00`;
                
                if (ventasPorHora[horaKey]) {
                    ventasPorHora[horaKey].cantidad++;
                    ventasPorHora[horaKey].total += parseFloat(sell.total) || 0;
                    ventasPorHora[horaKey].ventas.push({
                        id: sell.id,
                        total: sell.total,
                        hora: sell.created_at
                    });
                }
            }
        }
    });
    
    console.log('📊 Ventas agrupadas por hora:', ventasPorHora);
    
    // Crear contenido de la hoja
    let hojaContent = `
     <Worksheet ss:Name="Ventas por Hora">
      <Table>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Franja Horaria</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Cantidad Ventas</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Total Vendido</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Promedio por Venta</Data></Cell>
       </Row>`;
    
    // Variables para totales generales
    let totalVentasDelDia = 0;
    let totalMontoDelDia = 0;
    
    // Agregar datos por hora - SOLO mostrar horas que tuvieron ventas
    Object.keys(ventasPorHora).sort().forEach(horaKey => {
        const datos = ventasPorHora[horaKey];
        const promedio = datos.cantidad > 0 ? (datos.total / datos.cantidad).toFixed(0) : 0;
        
        totalVentasDelDia += datos.cantidad;
        totalMontoDelDia += datos.total;
        
        // Mostrar SOLO horas que tuvieron ventas (simplificar el reporte)
        if (datos.cantidad > 0) {
            hojaContent += `
       <Row>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="String">${datos.hora}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${datos.cantidad}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${Math.round(datos.total)}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${promedio}</Data></Cell>
       </Row>`;
        }
    });
    
    // Fila de totales
    const promedioGeneral = totalVentasDelDia > 0 ? (totalMontoDelDia / totalVentasDelDia).toFixed(0) : 0;
    hojaContent += `
       <Row>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">TOTAL PERÍODO</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="Number">${totalVentasDelDia}</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="Number">${Math.round(totalMontoDelDia)}</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="Number">${promedioGeneral}</Data></Cell>
       </Row>`;
    
    hojaContent += `
      </Table>
     </Worksheet>`;
    
    return hojaContent;
}

// Función para crear resumen de ventas por hora para múltiples días
function createResumenVentasPorHora(sells, startDate, endDate) {
    console.log('🕒 Creando resumen de ventas por hora para múltiples días...');
    
    // Agrupar ventas por hora (acumulado de todos los días)
    const ventasPorHoraTotal = {};
    
    // Inicializar todas las horas del día (10 AM a 11 PM - horario real de operación)
    for (let hora = 10; hora <= 23; hora++) {
        const horaKey = `${hora.toString().padStart(2, '0')}:00-${(hora + 1).toString().padStart(2, '0')}:00`;
        ventasPorHoraTotal[horaKey] = {
            hora: horaKey,
            cantidad: 0,
            total: 0,
            dias: new Set()
        };
    }
    
    // Procesar cada venta
    sells.forEach(sell => {
        if (sell.created_at) {
            // Extraer hora directamente del string para evitar problemas de zona horaria
            const hora = parseInt(sell.created_at.split(' ')[1].split(':')[0]);
            const dia = sell.created_at.split(' ')[0];
            
            if (hora >= 10 && hora <= 23) {
                const horaKey = `${hora.toString().padStart(2, '0')}:00-${(hora + 1).toString().padStart(2, '0')}:00`;
                
                if (ventasPorHoraTotal[horaKey]) {
                    ventasPorHoraTotal[horaKey].cantidad++;
                    ventasPorHoraTotal[horaKey].total += parseFloat(sell.total) || 0;
                    ventasPorHoraTotal[horaKey].dias.add(dia);
                }
            }
        }
    });
    
    // Crear contenido de la hoja
    let hojaContent = `
     <Worksheet ss:Name="Resumen por Horas">
      <Table>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Franja Horaria</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Total Ventas</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Total Vendido</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Promedio por Venta</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Días con Ventas</Data></Cell>
       </Row>`;
    
    let totalVentas = 0;
    let totalMonto = 0;
    
    // Agregar datos por hora
    Object.keys(ventasPorHoraTotal).sort().forEach(horaKey => {
        const datos = ventasPorHoraTotal[horaKey];
        const promedio = datos.cantidad > 0 ? (datos.total / datos.cantidad).toFixed(0) : 0;
        
        totalVentas += datos.cantidad;
        totalMonto += datos.total;
        
        if (datos.cantidad > 0) {
            hojaContent += `
       <Row>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="String">${datos.hora}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${datos.cantidad}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${Math.round(datos.total)}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${promedio}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${datos.dias.size}</Data></Cell>
       </Row>`;
        }
    });
    
    // Fila de totales
    const promedioGeneral = totalVentas > 0 ? (totalMonto / totalVentas).toFixed(0) : 0;
    hojaContent += `
       <Row>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">TOTAL PERÍODO</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="Number">${totalVentas}</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="Number">${Math.round(totalMonto)}</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="Number">${promedioGeneral}</Data></Cell>
        <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">${startDate} - ${endDate.split(' ')[0]}</Data></Cell>
       </Row>`;
    
    hojaContent += `
      </Table>
     </Worksheet>`;
    
    return hojaContent;
}

// Función para exportar tabla de contadores a Excel
function exportarContadores() {
    const tabla = document.getElementById('counters-table');
    const fechaActual = new Date().toLocaleDateString('es-CL').replace(/\//g, '-');
    const nombreArchivo = `Contadores_Financieros_${fechaActual}.csv`;
    
    // Usar TABULADORES en lugar de comas para mejor compatibilidad con Excel
    let csv = 'Método de Pago\tTransacciones\tTotal\tPromedio\n';
    
    // Recorrer las filas de la tabla
    const filas = tabla.querySelectorAll('tr');
    filas.forEach(fila => {
        const celdas = fila.querySelectorAll('td');
        if (celdas.length === 4) {
            // Extraer valores limpios
            let metodo = celdas[0].textContent.trim();
            let transacciones = celdas[1].textContent.replace(/[^\d]/g, ''); // Solo números
            let total = celdas[2].textContent.replace(/[^\d]/g, ''); // Solo números
            let promedio = celdas[3].textContent.replace(/[^\d]/g, ''); // Solo números
            
            // Separar con TABULADORES para columnas en Excel
            csv += `${metodo}\t${transacciones}\t${total}\t${promedio}\n`;
        } else if (celdas.length === 2) {
            // Filas de resumen (Saldo Total, etc.) - poner en columna 1 y 4
            let titulo = celdas[0].textContent.trim();
            let valor = celdas[1].textContent.replace(/[^\d]/g, '');
            csv += `${titulo}\t\t\t${valor}\n`;
        } else if (celdas.length === 1 && celdas[0].getAttribute('colspan') === '4') {
            // Fila con colspan="4" (como "ÓRDENES TOTALES: 32")
            csv += `${celdas[0].textContent.trim()}\t\t\t\n`;
        }
    });
    
    // Crear elemento de descarga usando data URI
    const link = document.createElement('a');
    link.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    link.download = nombreArchivo;
    link.style.display = 'none';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    console.log('✅ Tabla exportada exitosamente:', nombreArchivo);
    
    // Mostrar notificación pequeña
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000
    });
    
    Toast.fire({
        icon: 'success',
        title: 'Archivo exportado correctamente'
    });
}

// Función para actualizar resumen total
function updateResumenTotal(startDate, endDate, counters) {
    console.log("📊 Actualizando Resumen Total...", { startDate, endDate, counters });
    
    try {
        let totalGeneral = 0;
        let totalTransacciones = 0;
        let metodosActivos = 0;
        
        if (counters && counters[0]) {
            const datos = counters[0];
            
            // CÁLCULO CORRECTO: Solo Ventas - Gastos (el monto inicial no es ganancia)
            const ventasTotal = parseFloat(datos.balanceTotal) || 0;
            const gastosTotal = parseFloat(datos.expenses_day) || 0;
            totalGeneral = ventasTotal - gastosTotal; // NO sumar monto inicial
            
            console.log('💰 Cálculo Resumen Total:', {
                montoInicial: montoInicial,
                ventas: ventasTotal,
                gastos: gastosTotal,
                resumenFinal: totalGeneral
            });
            
            // Número de transacciones (órdenes)
            totalTransacciones = parseInt(datos.orders) || 0;
            
            // Contar métodos de pago activos
            const metodosPago = [
                'efectivo', 'debito', 'credito', 'transferencia', 'cheque',
                'banco', 'edenred', 'multicaja', 'sodexo', 'amipass',
                'convenio_empresa', 'rappi', 'uber', 'nota_de_credito',
                'junaeb', 'pedidos_ya', 'pluxee', 'banco_chile_20',
                'guia_despacho', 'fastSells', 'noSii', 'boleta', 'factura'
            ];

            metodosPago.forEach(metodo => {
                if (datos[metodo] && !isNaN(parseFloat(datos[metodo])) && parseFloat(datos[metodo]) > 0) {
                    metodosActivos++;
                }
            });
        }
        
        // DEBUGGING: Verificar fechas recibidas
        console.log("🔍 FECHAS RECIBIDAS EN RESUMEN:", { startDate, endDate });
        
        // Formatear período (SOLUCIÓN: parsear fecha manualmente para evitar problemas de zona horaria)
        const [yearStart, monthStart, dayStart] = startDate.split('-');
        const [yearEnd, monthEnd, dayEnd] = endDate.split(' ')[0].split('-');
        
        // Crear fechas con zona horaria local específica
        const fechaInicio = new Date(parseInt(yearStart), parseInt(monthStart) - 1, parseInt(dayStart));
        const fechaFin = new Date(parseInt(yearEnd), parseInt(monthEnd) - 1, parseInt(dayEnd));
        const esUnDia = fechaInicio.getTime() === fechaFin.getTime();
        
        console.log("📅 FECHAS PROCESADAS CORREGIDAS:", { 
            fechaInicio: fechaInicio.toISOString(), 
            fechaFin: fechaFin.toISOString(), 
            esUnDia,
            startDateOriginal: startDate,
            endDateOriginal: endDate
        });
        
        let periodoTexto;
        if (esUnDia) {
            periodoTexto = fechaInicio.toLocaleDateString("es-CL", {
                day: "numeric", 
                month: "long",
                year: "numeric"
            });
        } else {
            periodoTexto = `${fechaInicio.toLocaleDateString("es-CL", {
                day: "numeric",
                month: "short",
                year: "numeric"
            })} - ${fechaFin.toLocaleDateString("es-CL", {
                day: "numeric", 
                month: "short",
                year: "numeric"
            })}`;
        }
        
        console.log("📝 PERÍODO FORMATEADO CORREGIDO:", periodoTexto);
        
        // Actualizar UI
        document.getElementById("resumen-total").textContent = new Intl.NumberFormat("es-CL", {
            style: "currency",
            currency: "CLP",
            minimumFractionDigits: 0
        }).format(totalGeneral);
        
        document.getElementById("periodo-resumen").textContent = periodoTexto;
        document.getElementById("total-transacciones").textContent = totalTransacciones;
        document.getElementById("metodos-activos").textContent = metodosActivos;
        
        console.log("✅ Resumen actualizado:", {
            total: totalGeneral,
            transacciones: totalTransacciones,
            metodos: metodosActivos
        });
        
    } catch (error) {
        console.error("❌ Error actualizando resumen:", error);
        document.getElementById("resumen-total").textContent = "$0";
        document.getElementById("periodo-resumen").textContent = "Error al cargar";
        document.getElementById("total-transacciones").textContent = "0";
        document.getElementById("metodos-activos").textContent = "0";
    }
}

// ===== FUNCIONES DE GRÁFICOS INDEPENDIENTES =====

function getSellByHourIndependent(startDate, endDate) {
    console.log('🔥 Generando heatmap de ventas por día y hora...');
    
    // Limpiar heatmap existente
    clearHeatmap();

    __conection({
        url: generarURLApi(`/web/getAppSellsByHour?id=${id}&startDate=${startDate}&endDate=${endDate}`),
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        console.log('📊 Datos heatmap desde backend:', request);
        console.log('📊 Tipo de request:', typeof request);
        console.log('📊 Keys de request:', Object.keys(request));
        console.log('📊 JSON completo:', JSON.stringify(request, null, 2));

        let heatmapData = {};
        
        // Extraer datos del heatmap - NUEVO FORMATO
        if (request && typeof request === 'object') {
            // Buscar en cada app
            for (const app in request) {
                console.log(`🔍 Revisando app: ${app}`, request[app]);
                
                // Formato directo: app.heatmap_data
                if (request[app] && request[app].heatmap_data) {
                    heatmapData = request[app].heatmap_data;
                    console.log('✅ Encontrado en heatmap_data directo');
                    break;
                }
                // Formato anidado: app.original.heatmap_data
                else if (request[app] && request[app].original && request[app].original.heatmap_data) {
                    heatmapData = request[app].original.heatmap_data;
                    console.log('✅ Encontrado en original.heatmap_data');
                    break;
                }
            }
        }

        console.log('🎨 Datos del heatmap extraídos:', heatmapData);
        
        // Si no hay datos estructurados, crear matriz vacía
        if (Object.keys(heatmapData).length === 0) {
            console.warn('⚠️ No se encontraron datos de heatmap, creando matriz vacía');
            for (let day = 0; day <= 6; day++) {
                heatmapData[day] = {};
                for (let hour = 7; hour <= 20; hour++) {
                    heatmapData[day][hour] = { count: 0, total: 0 };
                }
            }
        }
        
        // Actualizar la tabla con los datos
        updateHeatmapTable(heatmapData);
    });
}

function clearHeatmap() {
    // Limpiar todas las celdas del heatmap
    const heatCells = document.querySelectorAll('.heat-cell');
    heatCells.forEach(cell => {
        cell.textContent = '0';
        cell.className = 'heat-cell heat-level-0';
        cell.removeAttribute('data-bs-toggle');
        cell.removeAttribute('data-bs-placement');
        cell.removeAttribute('title');
    });
}

function updateHeatmapTable(heatmapData) {
    console.log('🎯 Actualizando tabla heatmap...');
    console.log('🎯 Datos recibidos:', heatmapData);
    
    // Encontrar el valor máximo para normalizar los colores
    let maxValue = 0;
    for (let day = 0; day <= 6; day++) {
        for (let hour = 7; hour <= 23; hour++) {
            if (heatmapData[day] && heatmapData[day][hour]) {
                const value = typeof heatmapData[day][hour] === 'object' ? 
                    heatmapData[day][hour].count : heatmapData[day][hour];
                maxValue = Math.max(maxValue, value);
            }
        }
    }
    
    console.log(`📊 Valor máximo de ventas: ${maxValue}`);
    
    // Actualizar cada celda
    for (let day = 0; day <= 6; day++) {
        for (let hour = 7; hour <= 23; hour++) {
            const cell = document.querySelector(`[data-day="${day}"][data-hour="${hour}"]`);
            if (cell) {
                let value = 0;
                let total = 0;
                
                if (heatmapData[day] && heatmapData[day][hour]) {
                    if (typeof heatmapData[day][hour] === 'object') {
                        value = heatmapData[day][hour].count || 0;
                        total = heatmapData[day][hour].total || 0;
                    } else {
                        value = heatmapData[day][hour] || 0;
                    }
                }
                
                console.log(`🔍 Celda día=${day} hora=${hour}: value=${value}, total=${total}`);
                
                cell.textContent = value;
                
                // Calcular nivel de intensidad (0-10)
                const intensity = maxValue > 0 ? Math.floor((value / maxValue) * 10) : 0;
                
                // Aplicar clase de color
                cell.className = `heat-cell heat-level-${intensity}`;
                
                // Agregar tooltip con información detallada
                if (value > 0) {
                    const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                    let hourLabel;
                    
                    if (hour === 0) {
                        hourLabel = '12:00 AM';
                    } else if (hour === 12) {
                        hourLabel = '12:00 PM';
                    } else if (hour > 12) {
                        hourLabel = `${hour - 12}:00 PM`;
                    } else {
                        hourLabel = `${hour}:00 AM`;
                    }
                    
                    let tooltipText = `${dayNames[day]} ${hourLabel}: ${value} ventas`;
                    if (total > 0) {
                        tooltipText += `\nTotal: $${formatearMontoChile(total)}`;
                    }
                    
                    cell.setAttribute('data-bs-toggle', 'tooltip');
                    cell.setAttribute('data-bs-placement', 'top');
                    cell.setAttribute('title', tooltipText);
                }
            }
        }
    }
    
    // Inicializar tooltips de Bootstrap
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    console.log('✅ Heatmap actualizado correctamente');
}

// Función helper para formatear montos en pesos chilenos
function formatearMontoChile(monto) {
    return new Intl.NumberFormat("es-CL", {
        style: "currency",
        currency: "CLP",
        minimumFractionDigits: 0
    }).format(monto);
}

function getTopSellsIndependent(startDate, endDate) {
    // Destruir gráfico existente si existe
    if (window.topSellsChart && typeof window.topSellsChart.destroy === 'function') {
        window.topSellsChart.destroy();
    }

    __conection({
        url: generarURLApi(`/web/getAppTopSells?id=${id}&startDate=${startDate}&endDate=${endDate}`),
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        console.log('📊 Datos top productos:', request);

        const topSells = [];
        for (const app in request) {
            if (request[app] && request[app].original) {
                topSells.push(request[app].original);
            }
        }

        const labels = [];
        const data = [];
        const backgroundColors = [
            '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'
        ];

        if (topSells.length > 0 && topSells[0]) {
            // Convertir objeto a array y ordenar por cantidad
            const productsArray = [];
            for (const clave in topSells[0]) {
                if (topSells[0][clave] && topSells[0][clave].product_name) {
                    productsArray.push({
                        name: topSells[0][clave].product_name,
                        quantity: parseInt(topSells[0][clave].total_quantity) || 0
                    });
                }
            }

            // Ordenar por cantidad descendente y tomar top 5
            productsArray
                .sort((a, b) => b.quantity - a.quantity)
                .slice(0, 5)
                .forEach(product => {
                    labels.push(product.name);
                    data.push(product.quantity);
                });
        }

        var topSellsCanvas = document.getElementById('topSellsChart');
        
        if (topSellsCanvas) {
            window.topSellsChart = new Chart(topSellsCanvas, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Cantidad Vendida',
                        data: data,
                        backgroundColor: backgroundColors.slice(0, data.length),
                        borderWidth: 2,
                        borderRadius: 8,
                    }],
                },
                options: {
                    responsive: true,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Cantidad Vendida'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Top 5 Productos Más Vendidos'
                        }
                    }
                }
            });
        }
    });
}

function getPaymentMethodsChartIndependent(startDate, endDate) {
    // Destruir gráfico existente si existe
    if (window.paymentMethodsChart && typeof window.paymentMethodsChart.destroy === 'function') {
        window.paymentMethodsChart.destroy();
    }

    __conection({
        url: generarURLApi(`/web/getAppSells?id=${id}&startDate=${startDate}&endDate=${endDate}&efectivo=true&debito=true&transferencia=true&credito=true&rappi=true&uber=true&boleta=true&per_page=10000`),
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        console.log('💳 Datos métodos de pago:', request);

        const sells = [];
        // Extraer ventas de la respuesta
        if (Array.isArray(request)) {
            sells.push(...request);
        } else {
            for (const app in request) {
                if (request[app] && request[app].original && Array.isArray(request[app].original)) {
                    sells.push(...request[app].original);
                } else if (request[app] && Array.isArray(request[app])) {
                    sells.push(...request[app]);
                }
            }
        }

        // Calcular métodos de pago
        const paymentMethods = {};
        let totalVentas = 0;

        sells.forEach(sell => {
            const method = sell.paymode || 'efectivo';
            if (!paymentMethods[method]) {
                paymentMethods[method] = 0;
            }
            paymentMethods[method]++;
            totalVentas++;
        });

        // Preparar datos para el gráfico
        const labels = [];
        const data = [];
        const percentages = [];
        const backgroundColors = [
            '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8',
            '#6f42c1', '#e83e8c', '#fd7e14', '#20c997', '#6c757d'
        ];

        Object.entries(paymentMethods)
            .sort((a, b) => b[1] - a[1])
            .forEach(([method, count]) => {
                const percentage = totalVentas > 0 ? ((count / totalVentas) * 100).toFixed(1) : 0;
                labels.push(`${method.charAt(0).toUpperCase() + method.slice(1)} (${percentage}%)`);
                data.push(count);
                percentages.push(percentage);
            });

        var paymentCanvas = document.getElementById('paymentMethodsChart');
        
        if (paymentCanvas) {
            window.paymentMethodsChart = new Chart(paymentCanvas, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors.slice(0, data.length),
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Distribución de Métodos de Pago'
                        }
                    }
                }
            });
        }
    });
}

// ========== CENTRO ANALÍTICO FUNCTIONS ==========

// Variables para los gráficos del centro analítico
let trendChart = null;
let productsChart = null;
let hourlyChart = null;
let weeklyChart = null;

// Función principal para inicializar el centro analítico
function initializeAnalyticsCenter(startDate, endDate) {
    console.log("🚀 Inicializando Centro Analítico");
    
    // Cargar todos los componentes del centro analítico
    loadAnalyticsKPIs(startDate, endDate);
    loadTrendChart(startDate, endDate);
    loadSimpleHourlyChart(startDate, endDate);
    loadWorkingProductsChart(startDate, endDate);
    loadWorkingWeeklyChart(startDate, endDate);
    loadWorkingTopProductsList(startDate, endDate);
    generateSimpleInsights(startDate, endDate);
}

// Cargar KPIs del centro analítico
async function loadAnalyticsKPIs(startDate, endDate) {
    try {
        console.log("🔍 Cargando KPIs con fechas:", { startDate, endDate });
        
        // Usar exactamente la misma consulta que el heatmap
        __conection({
            url: generarURLApi(`/web/getAppSellsByHour?id=${id}&startDate=${startDate}&endDate=${endDate}`),
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function (request) {
            console.log("📊 Respuesta KPIs desde heatmap endpoint:", request);

            let heatmapData = {};
            let totalRevenue = 0;
            let totalOrders = 0;
            let peakHour = { hour: 0, sales: 0 };

            // Extraer datos del heatmap igual que en getSellByHourIndependent
            if (request && typeof request === 'object') {
                for (const app in request) {
                    if (request[app] && request[app].original && request[app].original.heatmap_data) {
                        heatmapData = request[app].original.heatmap_data;
                        break;
                    } else if (request[app] && request[app].heatmap_data) {
                        heatmapData = request[app].heatmap_data;
                        break;
                    }
                }
            }

            console.log("🎨 Datos del heatmap para KPIs:", heatmapData);

            // Calcular métricas desde los datos del heatmap
            if (heatmapData && typeof heatmapData === 'object') {
                Object.keys(heatmapData).forEach(day => {
                    if (heatmapData[day] && typeof heatmapData[day] === 'object') {
                        Object.keys(heatmapData[day]).forEach(hour => {
                            const cellData = heatmapData[day][hour];
                            if (cellData) {
                                const sales = parseInt(cellData.count || 0);
                                const revenue = parseFloat(cellData.total || 0);
                                
                                totalOrders += sales;
                                totalRevenue += revenue;
                                
                                // Encontrar hora pico
                                if (sales > peakHour.sales) {
                                    peakHour.hour = parseInt(hour);
                                    peakHour.sales = sales;
                                }
                            }
                        });
                    }
                });
            }

            console.log("🔥 Datos procesados:", {
                totalRevenue,
                totalOrders,
                peakHour
            });

            const avgTicket = totalOrders > 0 ? totalRevenue / totalOrders : 0;
            const totalCustomers = totalOrders; // 1 cliente por orden
            // const conversion = 100; // ELIMINADO - no tiene sentido sin tracking de visitas

            console.log("📈 Métricas calculadas desde heatmap:", {
                totalRevenue,
                totalOrders,
                avgTicket,
                peakHour,
                totalCustomers
            });

            // Actualizar UI con cambios realistas
            updateKPI('analytics-revenue', formatearMontoChile(totalRevenue), calculateChange(totalRevenue, 0));
            updateKPI('analytics-orders', totalOrders, calculateChange(totalOrders, 0));
            updateKPI('analytics-avg-ticket', formatearMontoChile(avgTicket), calculateChange(avgTicket, 0));
            
            // Actualizar hora pico de forma especial
            const peakHourElement = document.getElementById('analytics-peak-hour');
            const peakSalesElement = document.getElementById('analytics-peak-sales');
            
            if (peakHourElement) {
                peakHourElement.textContent = formatHour(peakHour.hour);
            }
            if (peakSalesElement) {
                peakSalesElement.textContent = `${peakHour.sales} ventas`;
            }
            
            updateKPI('analytics-customers', totalCustomers, calculateChange(totalCustomers, 0));
            // updateKPI('analytics-conversion', `${conversion.toFixed(1)}%`, calculateChange(conversion, 0)); // ELIMINADO
        });

    } catch (error) {
        console.error("❌ Error cargando KPIs:", error);
    }
}

// Función para actualizar KPIs
function updateKPI(elementId, value, change) {
    const element = document.getElementById(elementId);
    const changeElement = document.getElementById(elementId + '-change');
    
    if (element) {
        element.textContent = value;
    }
    
    if (changeElement) {
        // Si es la hora pico, no mostrar porcentaje
        if (elementId === 'analytics-peak-hour') {
            changeElement.textContent = change;
        } else {
            changeElement.textContent = change;
        }
        changeElement.className = `trend-indicator ${getTrendClass(change)}`;
    }
}

// Función para calcular cambios mejorada
function calculateChange(current, previous) {
    // Para efectos de demo, generar cambios aleatorios realistas
    if (current > 0) {
        const randomChange = (Math.random() - 0.5) * 20; // -10% a +10%
        const sign = randomChange >= 0 ? '+' : '';
        return `${sign}${randomChange.toFixed(1)}%`;
    }
    return '+0%';
}

// Función para obtener clase de tendencia
function getTrendClass(change) {
    if (change.includes('+') && !change.includes('+0')) return 'positive';
    if (change.includes('-')) return 'negative';
    return 'neutral';
}

// Función para formatear hora
function formatHour(hour) {
    if (hour === 0) return '--:--';
    const period = hour < 12 ? 'AM' : 'PM';
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
    return `${displayHour.toString().padStart(2, '0')}:00 ${period}`;
}

// Cargar gráfico de tendencias (7 días)
async function loadTrendChart(startDate, endDate) {
    try {
        console.log("📈 Cargando gráfico de tendencias");
        
        __conection({
            url: generarURLApi(`/web/getAppSellsByHour?id=${id}&startDate=${startDate}&endDate=${endDate}`),
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function (request) {
            console.log("📊 Datos para gráfico de tendencias:", request);

            let heatmapData = {};
            
            // Extraer datos del heatmap
            if (request && typeof request === 'object') {
                for (const app in request) {
                    if (request[app] && request[app].original && request[app].original.heatmap_data) {
                        heatmapData = request[app].original.heatmap_data;
                        break;
                    } else if (request[app] && request[app].heatmap_data) {
                        heatmapData = request[app].heatmap_data;
                        break;
                    }
                }
            }

            if (heatmapData && typeof heatmapData === 'object') {
                const ctx = document.getElementById('trendChart');
                if (!ctx) return;

                // Procesar datos por día desde el heatmap
                // Backend usa: 0=Domingo, 1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado
                const dailyData = {
                    'Domingo': 0,
                    'Lunes': 0,
                    'Martes': 0,
                    'Miércoles': 0,
                    'Jueves': 0,
                    'Viernes': 0,
                    'Sábado': 0
                };

                const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

                Object.keys(heatmapData).forEach(day => {
                    const dayIndex = parseInt(day); // 0-6 directamente desde el backend
                    const dayName = dayNames[dayIndex];
                    
                    console.log(`📅 Procesando día ${day} (${dayName}):`, heatmapData[day]);
                    
                    if (dayName && heatmapData[day] && typeof heatmapData[day] === 'object') {
                        Object.keys(heatmapData[day]).forEach(hour => {
                            const cellData = heatmapData[day][hour];
                            if (cellData) {
                                dailyData[dayName] += parseFloat(cellData.total || 0);
                            }
                        });
                    }
                });

                const labels = Object.keys(dailyData);
                const data = Object.values(dailyData);

                console.log("📊 Datos de tendencia procesados desde heatmap:", { labels, data, dailyData });

                // Destruir gráfico anterior si existe
                if (trendChart) {
                    trendChart.destroy();
                }

                // Configurar el canvas con altura fija
                ctx.style.height = '250px';
                ctx.height = 250;

                trendChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ventas ($)',
                            data: data,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        height: 250,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Total: $' + context.parsed.y.toLocaleString('es-CL');
                                    }
                                }
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                offset: 4,
                                formatter: function(value) {
                                    if (value === 0) return '';
                                    return '$' + value.toLocaleString('es-CL');
                                },
                                color: '#1f2937',
                                font: {
                                    weight: 'bold',
                                    size: 11
                                },
                                backgroundColor: 'rgba(255, 255, 255, 0.8)',
                                borderRadius: 4,
                                padding: 4
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString('es-CL');
                                    }
                                }
                            }
                        },
                        onResize: function(chart, size) {
                            // Limitar la altura durante resize
                            if (size.height > 250) {
                                chart.canvas.style.height = '250px';
                            }
                        }
                    }
                });
            } else {
                console.log("⚠️ No hay datos para gráfico de tendencias");
            }
        });

    } catch (error) {
        console.error("❌ Error cargando gráfico de tendencias:", error);
    }
}

// Función simple para gráfico horario
function loadSimpleHourlyChart(startDate, endDate) {
    try {
        console.log("⏰ Cargando gráfico horario simple");
        
        __conection({
            url: generarURLApi(`/web/getAppSellsByHour?id=${id}&startDate=${startDate}&endDate=${endDate}`),
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function (request) {
            console.log("📊 Datos para gráfico horario:", request);

            let heatmapData = {};
            
            // Extraer datos del heatmap
            if (request && typeof request === 'object') {
                for (const app in request) {
                    if (request[app] && request[app].original && request[app].original.heatmap_data) {
                        heatmapData = request[app].original.heatmap_data;
                        break;
                    } else if (request[app] && request[app].heatmap_data) {
                        heatmapData = request[app].heatmap_data;
                        break;
                    }
                }
            }

            if (heatmapData && typeof heatmapData === 'object') {
                const ctx = document.getElementById('hourlyChart');
                if (!ctx) return;

                // Procesar datos por hora desde el heatmap (7 AM - 8 PM)
                const hourlyData = {};
                for (let i = 7; i <= 20; i++) {
                    hourlyData[i] = 0;
                }

                Object.keys(heatmapData).forEach(day => {
                    if (heatmapData[day] && typeof heatmapData[day] === 'object') {
                        Object.keys(heatmapData[day]).forEach(hour => {
                            const hourInt = parseInt(hour);
                            if (hourInt >= 7 && hourInt <= 20) {
                                const cellData = heatmapData[day][hour];
                                if (cellData) {
                                    hourlyData[hourInt] += parseFloat(cellData.total || 0);
                                }
                            }
                        });
                    }
                });

                const labels = Object.keys(hourlyData).map(hour => formatHour(parseInt(hour)));
                const data = Object.values(hourlyData);

                console.log("⏰ Datos horarios procesados desde heatmap:", { labels, data, hourlyData });

                // Destruir gráfico anterior si existe
                if (hourlyChart) {
                    hourlyChart.destroy();
                }

                // Configurar el canvas con altura fija
                ctx.style.height = '250px';
                ctx.height = 250;

                hourlyChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ventas por Hora',
                            data: data,
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: '#3b82f6',
                            borderWidth: 1,
                            borderRadius: 4,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        height: 250,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Total: $' + context.parsed.y.toLocaleString('es-CL');
                                    }
                                }
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                formatter: function(value) {
                                    if (value === 0) return '';
                                    return '$' + value.toLocaleString('es-CL');
                                },
                                color: '#1f2937',
                                font: {
                                    weight: 'bold',
                                    size: 11
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString('es-CL');
                                    }
                                }
                            }
                        },
                        onResize: function(chart, size) {
                            // Limitar la altura durante resize
                            if (size.height > 250) {
                                chart.canvas.style.height = '250px';
                            }
                        }
                    }
                });
            } else {
                console.log("⚠️ No hay datos para gráfico horario");
            }
        });

    } catch (error) {
        console.error("❌ Error cargando gráfico horario:", error);
    }
}

// Función simple para insights
function generateSimpleInsights(startDate, endDate) {
    try {
        console.log("💡 Generando insights simples");
        
        const container = document.getElementById('analyticsAlerts');
        if (container) {
            container.innerHTML = `
                <div class="alert-item info">
                    <div class="alert-icon" style="background: #06b6d4;">
                        <i class="fas fa-info-circle" style="color: white;"></i>
                    </div>
                    <div class="alert-content">
                        <div class="alert-title">Centro Analítico Activo</div>
                        <div class="alert-message">Los datos se están cargando desde el mismo origen que el heatmap. Revisa las métricas arriba para ver el resumen de actividad.</div>
                    </div>
                </div>
                <div class="alert-item success">
                    <div class="alert-icon" style="background: #10b981;">
                        <i class="fas fa-chart-line" style="color: white;"></i>
                    </div>
                    <div class="alert-content">
                        <div class="alert-title">Datos Sincronizados</div>
                        <div class="alert-message">El centro analítico utiliza la misma fuente de datos que el mapa de calor para garantizar consistencia en la información.</div>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error("❌ Error generando insights:", error);
    }
}

// Cargar gráfico de productos
// Función para gráfico de productos funcional
function loadWorkingProductsChart(startDate, endDate) {
    try {
        console.log("📊 Cargando gráfico de productos REALES");
        
        __conection({
            url: generarURLApi(`/web/getAppTopSells?id=${id}&startDate=${startDate}&endDate=${endDate}`),
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function (request) {
            console.log("📊 Datos REALES de productos para gráfico:", request);

            const ctx = document.getElementById('productsChart');
            if (!ctx) return;

            let productos = [];
            
            // PROCESAR OBJETOS NUMERADOS PARA GRÁFICO
            if (request && typeof request === 'object') {
                console.log("🔍 Procesando datos para gráfico...");
                
                for (const app in request) {
                    if (request[app] && request[app].original) {
                        const original = request[app].original;
                        console.log(`� Datos de gráfico de ${app}:`, original);
                        
                        // Buscar objetos con claves numéricas
                        for (const key in original) {
                            const item = original[key];
                            
                            // Verificar si es un producto válido
                            if (item && 
                                typeof item === 'object' && 
                                item.product_name && 
                                item.total_quantity !== undefined) {
                                
                                productos.push({
                                    name: item.product_name,
                                    quantity: parseInt(item.total_quantity) || 0
                                });
                                
                                console.log(`✅ Producto para gráfico: ${item.product_name} - ${item.total_quantity}`);
                            }
                        }
                        
                        if (productos.length > 0) {
                            console.log(`🎯 Productos para gráfico encontrados en ${app}: ${productos.length}`);
                            break;
                        }
                    }
                }
            }

            console.log("📊 PRODUCTOS PARA GRÁFICO:", productos);

            if (productos.length > 0) {
                // Ordenar por cantidad descendente y tomar top 5
                productos.sort((a, b) => b.quantity - a.quantity);
                const top5 = productos.slice(0, 5);
                
                const labels = top5.map(p => p.name);
                const data = top5.map(p => p.quantity);
                
                const colors = [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)', 
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(139, 92, 246, 0.8)'
                ];

                console.log("📊 GRÁFICO con datos REALES:", { labels, data });

                // Destruir gráfico anterior si existe
                if (productsChart) {
                    productsChart.destroy();
                }

                // Configurar el canvas con altura fija
                ctx.style.height = '250px';
                ctx.height = 250;

                productsChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: colors.slice(0, data.length),
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        height: 250,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed + ' unidades';
                                    }
                                }
                            }
                        },
                        onResize: function(chart, size) {
                            if (size.height > 250) {
                                chart.canvas.style.height = '250px';
                            }
                        }
                    }
                });

                console.log("🎉 Gráfico de productos REALES creado exitosamente");
            } else {
                console.log("⚠️ No hay productos válidos para el gráfico");
                ctx.getContext('2d').clearRect(0, 0, ctx.width, ctx.height);
            }
        });

    } catch (error) {
        console.error("❌ Error cargando gráfico de productos:", error);
    }
}

// Cargar gráfico horario
// Función duplicada eliminada - se usa loadSimpleHourlyChart

// Cargar gráfico semanal
// Función para gráfico semanal funcional
function loadWorkingWeeklyChart(startDate, endDate) {
    try {
        console.log("📅 Cargando gráfico semanal");
        
        __conection({
            url: generarURLApi(`/web/getAppSellsByHour?id=${id}&startDate=${startDate}&endDate=${endDate}`),
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function (request) {
            console.log("📊 Datos para gráfico semanal:", request);

            let heatmapData = {};
            
            // Extraer datos del heatmap
            if (request && typeof request === 'object') {
                for (const app in request) {
                    if (request[app] && request[app].original && request[app].original.heatmap_data) {
                        heatmapData = request[app].original.heatmap_data;
                        break;
                    } else if (request[app] && request[app].heatmap_data) {
                        heatmapData = request[app].heatmap_data;
                        break;
                    }
                }
            }

            if (heatmapData && typeof heatmapData === 'object') {
                const ctx = document.getElementById('weeklyChart');
                if (!ctx) return;

                // Procesar datos por día de la semana desde el heatmap
                // Carbon dayOfWeek: 0=Domingo, 1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado
                const weeklyData = {
                    0: 0, // Domingo
                    1: 0, // Lunes
                    2: 0, // Martes
                    3: 0, // Miércoles
                    4: 0, // Jueves
                    5: 0, // Viernes
                    6: 0  // Sábado
                };

                Object.keys(heatmapData).forEach(day => {
                    const dayInt = parseInt(day);
                    if (heatmapData[day] && typeof heatmapData[day] === 'object') {
                        Object.keys(heatmapData[day]).forEach(hour => {
                            const cellData = heatmapData[day][hour];
                            if (cellData) {
                                weeklyData[dayInt] += parseFloat(cellData.total || 0);
                            }
                        });
                    }
                });

                const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                const labels = dayNames;
                const data = [
                    weeklyData[0],
                    weeklyData[1], 
                    weeklyData[2],
                    weeklyData[3],
                    weeklyData[4],
                    weeklyData[5],
                    weeklyData[6]
                ];

                console.log("📅 Datos semanales procesados desde heatmap:", { labels, data, weeklyData });

                // Destruir gráfico anterior si existe
                if (weeklyChart) {
                    weeklyChart.destroy();
                }

                // Configurar el canvas con altura fija
                ctx.style.height = '250px';
                ctx.height = 250;

                weeklyChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ventas Semanales',
                            data: data,
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(139, 92, 246, 0.8)',
                                'rgba(6, 182, 212, 0.8)',
                                'rgba(236, 72, 153, 0.8)'
                            ],
                            borderWidth: 1,
                            borderRadius: 4,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        height: 250,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Total: $' + context.parsed.y.toLocaleString('es-CL');
                                    }
                                }
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                formatter: function(value) {
                                    if (value === 0) return '';
                                    return '$' + value.toLocaleString('es-CL');
                                },
                                color: '#1f2937',
                                font: {
                                    weight: 'bold',
                                    size: 12
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString('es-CL');
                                    }
                                }
                            }
                        },
                        onResize: function(chart, size) {
                            if (size.height > 250) {
                                chart.canvas.style.height = '250px';
                            }
                        }
                    }
                });

                console.log("📅 Gráfico semanal creado exitosamente");
            } else {
                console.log("⚠️ No hay datos para gráfico semanal");
            }
        });

    } catch (error) {
        console.error("❌ Error cargando gráfico semanal:", error);
    }
}

// Cargar lista de TODOS los productos
// Función para mostrar TODOS los productos vendidos - USA DATOS REALES
function loadWorkingTopProductsList(startDate, endDate) {
    try {
        console.log("🏆 Cargando TODOS los productos REALES");
        
        __conection({
            url: generarURLApi(`/web/getAppTopSells?id=${id}&startDate=${startDate}&endDate=${endDate}`),
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function (request) {
            console.log("📊 Datos REALES de top productos:", request);

            const container = document.getElementById('topProductsList');
            if (!container) return;

            let productos = [];
            
            // NUEVA ESTRATEGIA: Procesar objetos numerados con product_name y total_quantity
            if (request && typeof request === 'object') {
                console.log("🔍 Procesando apps de datos...");
                
                for (const app in request) {
                    if (request[app] && request[app].original) {
                        const original = request[app].original;
                        console.log(`📦 Procesando datos de ${app}:`, original);
                        
                        // Buscar objetos con claves numéricas
                        for (const key in original) {
                            const item = original[key];
                            
                            // Verificar si es un producto válido
                            if (item && 
                                typeof item === 'object' && 
                                item.product_name && 
                                item.total_quantity !== undefined) {
                                
                                productos.push({
                                    name: item.product_name,
                                    quantity: parseInt(item.total_quantity) || 0
                                });
                                
                                console.log(`✅ Producto procesado: ${item.product_name} - ${item.total_quantity} unidades`);
                            }
                        }
                        
                        // Si encontramos productos, no seguir buscando en otras apps
                        if (productos.length > 0) {
                            console.log(`🎯 Total productos encontrados en ${app}: ${productos.length}`);
                            break;
                        }
                    }
                }
            }

            console.log("📊 PRODUCTOS FINALES procesados:", productos);

            if (productos.length > 0) {
                console.log("✅ CREANDO HTML con productos reales...");
                
                // Ordenar por cantidad descendente - MOSTRAR TODOS
                productos.sort((a, b) => b.quantity - a.quantity);
                
                let html = '';
                productos.forEach((producto, index) => {
                    const badgeColor = index === 0 ? 'bg-success' :     // 🥇 Oro
                                     index === 1 ? 'bg-info' :        // 🥈 Plata  
                                     index === 2 ? 'bg-warning' :     // 🥉 Bronce
                                     index === 3 ? 'bg-danger' :      // 4to lugar
                                     index === 4 ? 'bg-primary' :     // 5to lugar
                                     index === 5 ? 'bg-dark' :        // 6to lugar
                                     index === 6 ? 'bg-secondary' :   // 7mo lugar
                                     index === 7 ? 'bg-success' :     // 8vo lugar
                                     index === 8 ? 'bg-info' :        // 9no lugar
                                     'bg-warning';                     // 10mo lugar
                    
                    console.log(`📦 Producto ${index + 1}: ${producto.name} - ${producto.quantity} unidades`);
                    
                    html += `
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                            <div>
                                <span class="badge ${badgeColor} me-2">#${index + 1}</span>
                                <strong>${producto.name}</strong>
                                <br>
                                <small class="text-muted">${producto.quantity} unidades vendidas</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary fs-4">${producto.quantity}</div>
                                <small class="text-muted">Total Vendido</small>
                            </div>
                        </div>
                    `;
                });

                container.innerHTML = html;
                console.log("🎯 TODOS los productos REALES mostrados exitosamente");
                
            } else {
                console.log("⚠️ No se encontraron productos válidos");
                console.log("� Estructura recibida:", JSON.stringify(request, null, 2));
                
                container.innerHTML = '<div class="text-center p-4"><i class="fas fa-exclamation-circle text-muted"></i><br><small class="text-muted">No hay datos de productos disponibles</small></div>';
            }
        });

    } catch (error) {
        console.error("❌ Error cargando todos los productos:", error);
        const container = document.getElementById('topProductsList');
        if (container) {
            container.innerHTML = '<div class="text-center p-4"><i class="fas fa-exclamation-triangle text-danger"></i><br><small class="text-danger">Error cargando datos</small></div>';
        }
    }
}

// Generar insights y recomendaciones
// Función duplicada eliminada - se usa generateSimpleInsights

// Función auxiliar para colores de alertas
function getAlertColor(type) {
    const colors = {
        info: '#06b6d4',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444'
    };
    return colors[type] || '#6b7280';
}

// Función auxiliar para obtener nombre del día
function getDayName(dayNumber) {
    const days = ['', 'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    return days[dayNumber] || 'Día';
}

// ========== FUNCIONES PARA DESGLOSE DE VENTAS POR MÉTODO DE PAGO ==========

// Variable global para guardar las ventas del desglose actual
let ventasDesglosePagoActual = [];

/**
 * Muestra el modal con el desglose de ventas de un método de pago específico
 */
async function mostrarDesglosePago(nombreMetodo, metodoInterno, totalTransacciones, totalMonto) {
    console.log('🔍 Mostrando desglose para:', nombreMetodo, metodoInterno);
    
    // Actualizar título y resumen del modal
    document.getElementById('modal-metodo-nombre').textContent = `${nombreMetodo} - Desglose de Ventas`;
    document.getElementById('modal-total-transacciones').textContent = totalTransacciones;
    document.getElementById('modal-total-monto').textContent = '$' + Math.round(totalMonto).toLocaleString('es-CL');
    const promedio = totalTransacciones > 0 ? totalMonto / totalTransacciones : 0;
    document.getElementById('modal-promedio').textContent = '$' + Math.round(promedio).toLocaleString('es-CL');
    
    // Obtener fechas actuales del filtro
    const startDate = startDateValue || getNowDate();
    const endDate = endDateValue || (getNowDate() + ' 23:59:59');
    
    // Formatear período
    const fechaInicio = new Date(startDate).toLocaleDateString('es-CL');
    const fechaFin = new Date(endDate.split(' ')[0]).toLocaleDateString('es-CL');
    document.getElementById('modal-periodo').textContent = `${fechaInicio} - ${fechaFin}`;
    
    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById('modalDesglosePago'));
    modal.show();
    
    // Mostrar loader
    const tbody = document.getElementById('tabla-desglose-ventas-body');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2 text-muted">Obteniendo ventas...</p>
            </td>
        </tr>
    `;
    
    // Obtener las ventas individuales
    try {
        await obtenerVentasDesglose(metodoInterno, nombreMetodo);
    } catch (error) {
        console.error('Error obteniendo ventas:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>Error al cargar las ventas. Por favor intenta de nuevo.</p>
                </td>
            </tr>
        `;
    }
}

/**
 * Obtiene las ventas individuales para un método de pago específico
 */
async function obtenerVentasDesglose(metodoInterno, nombreMetodo) {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    
    if (!id) {
        throw new Error('ID de sucursal no encontrado');
    }
    
    // Usar las fechas del filtro actual
    const startDate = startDateValue || getNowDate();
    const endDate = endDateValue || (getNowDate() + ' 23:59:59');
    
    console.log('📅 Obteniendo ventas de', startDate, 'a', endDate, 'para método:', metodoInterno);
    
    // Construir parámetros según el método
    let paymentParam = '';
    
    // Mapeo de métodos internos a parámetros de la API
    const metodosMap = {
        'boleta': 'boleta=true&efectivo=true',
        'debito': 'debito=true',
        'banco': 'banco=true',
        'edenred': 'edenred=true',
        'sodexo': 'sodexo=true',
        'amipass': 'amipass=true',
        'rappi': 'rappi=true',
        'uber': 'uber=true',
        'credito': 'credito=true',
        'transferencia': 'transferencia=true',
        'junaeb': 'junaeb=true',
        'pedidos_ya': 'pedidos_ya=true',
        'pluxee': 'pluxee=true',
        'banco_chile_20': 'banco_chile_20=true'
    };
    
    paymentParam = metodosMap[metodoInterno] || `${metodoInterno}=true`;
    
    const sellsUrl = generarURLApi(`/web/getAppSells?id=${id}&startDate=${startDate}&endDate=${endDate}&${paymentParam}&per_page=10000`);
    
    console.log('🔗 URL:', sellsUrl);
    
    __conection({
        url: sellsUrl,
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function(sellsResponse) {
        console.log('📦 Respuesta ventas:', sellsResponse);
        
        // Extraer ventas del response
        const sells = [];
        if (Array.isArray(sellsResponse)) {
            sells.push(...sellsResponse);
        } else {
            for (const app in sellsResponse) {
                if (sellsResponse[app] && sellsResponse[app].original && Array.isArray(sellsResponse[app].original)) {
                    sells.push(...sellsResponse[app].original);
                }
            }
        }
        
        console.log('✅ Total ventas encontradas:', sells.length);
        
        // Filtrar ventas por método de pago (para métodos delivery que usan other_type)
        const ventasFiltradas = sells.filter(venta => {
            if (metodoInterno === 'uber' || metodoInterno === 'rappi' || metodoInterno === 'pedidos_ya') {
                const otherType = (venta.other_type || '').toLowerCase().trim();
                const metodoLower = metodoInterno.toLowerCase();
                return otherType === metodoLower || 
                       otherType === metodoLower.replace('_', '') ||
                       otherType === metodoLower.replace('_', ' ') ||
                       (metodoInterno === 'uber' && otherType === 'uber_eats') ||
                       (metodoInterno === 'pedidos_ya' && otherType === 'pedidosya');
            }
            return true; // Para otros métodos, incluir todas
        });
        
        console.log('🎯 Ventas filtradas:', ventasFiltradas.length);
        
        ventasDesglosePagoActual = ventasFiltradas;
        mostrarVentasEnTabla(ventasFiltradas, nombreMetodo);
    });
}

/**
 * Muestra las ventas en la tabla del modal
 */
function mostrarVentasEnTabla(ventas, nombreMetodo) {
    const tbody = document.getElementById('tabla-desglose-ventas-body');
    
    if (ventas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p>No se encontraron ventas para este método de pago en el período seleccionado.</p>
                </td>
            </tr>
        `;
        return;
    }
    
    // Ordenar ventas por fecha descendente (más reciente primero)
    ventas.sort((a, b) => {
        const fechaA = new Date(a.created_at || a.fecha || a.date);
        const fechaB = new Date(b.created_at || b.fecha || b.date);
        return fechaB - fechaA;
    });
    
    let html = '';
    ventas.forEach((venta, index) => {
        const id = venta.id || venta.sale_id || '-';
        const fecha = venta.created_at || venta.fecha || venta.date || '-';
        const monto = parseFloat(venta.total || venta.price_total || venta.precio_total || 0);
        const paymode = venta.paymode || venta.payment_method || '-';
        const otherType = venta.other_type || '';
        const typeSell = venta.typeSell || venta.type_sell || '';
        
        // Formatear fecha
        let fechaFormateada = '-';
        if (fecha !== '-') {
            const fechaObj = new Date(fecha);
            fechaFormateada = fechaObj.toLocaleString('es-CL', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        // Detalles adicionales
        let detalles = [];
        if (otherType) detalles.push(`Plataforma: ${otherType}`);
        if (typeSell) detalles.push(`Tipo: ${typeSell}`);
        if (venta.mesa) detalles.push(`Mesa: ${venta.mesa}`);
        if (venta.waiter) detalles.push(`Mesero: ${venta.waiter}`);
        
        const detallesHTML = detalles.length > 0 ? detalles.join('<br>') : 'Sin detalles';
        
        html += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td><strong class="text-primary">#${id}</strong></td>
                <td>${fechaFormateada}</td>
                <td class="fw-bold text-success">$${Math.round(monto).toLocaleString('es-CL')}</td>
                <td><span class="badge bg-secondary">${paymode}</span></td>
                <td><small>${detallesHTML}</small></td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

/**
 * Exporta el desglose actual a Excel
 */
function exportarDesglosePago() {
    if (!ventasDesglosePagoActual || ventasDesglosePagoActual.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin datos',
            text: 'No hay ventas para exportar',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    const nombreMetodo = document.getElementById('modal-metodo-nombre').textContent.split(' - ')[0];
    const periodo = document.getElementById('modal-periodo').textContent;
    
    // Preparar datos para Excel
    const datosExcel = [];
    
    // Encabezados
    datosExcel.push(['#', 'ID Venta', 'Fecha/Hora', 'Monto', 'Método', 'Plataforma', 'Tipo', 'Mesa', 'Mesero']);
    
    // Ordenar ventas por fecha
    const ventasOrdenadas = [...ventasDesglosePagoActual].sort((a, b) => {
        const fechaA = new Date(a.created_at || a.fecha || a.date);
        const fechaB = new Date(b.created_at || b.fecha || b.date);
        return fechaB - fechaA;
    });
    
    // Datos
    ventasOrdenadas.forEach((venta, index) => {
        const id = venta.id || venta.sale_id || '-';
        const fecha = venta.created_at || venta.fecha || venta.date || '-';
        const monto = parseFloat(venta.total || venta.price_total || venta.precio_total || 0);
        const paymode = venta.paymode || venta.payment_method || '-';
        const otherType = venta.other_type || '-';
        const typeSell = venta.typeSell || venta.type_sell || '-';
        const mesa = venta.mesa || '-';
        const waiter = venta.waiter || '-';
        
        datosExcel.push([
            index + 1,
            id,
            fecha,
            monto,
            paymode,
            otherType,
            typeSell,
            mesa,
            waiter
        ]);
    });
    
    // Crear libro de Excel
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(datosExcel);
    
    // Ajustar ancho de columnas
    ws['!cols'] = [
        {wch: 5},   // #
        {wch: 10},  // ID
        {wch: 20},  // Fecha
        {wch: 15},  // Monto
        {wch: 15},  // Método
        {wch: 15},  // Plataforma
        {wch: 15},  // Tipo
        {wch: 10},  // Mesa
        {wch: 20}   // Mesero
    ];
    
    XLSX.utils.book_append_sheet(wb, ws, 'Desglose');
    
    // Nombre del archivo
    const nombreArchivo = `Desglose_${nombreMetodo.replace(/\s+/g, '_')}_${periodo.replace(/\s+/g, '_')}.xlsx`;
    
    // Descargar
    XLSX.writeFile(wb, nombreArchivo);
    
    Swal.fire({
        icon: 'success',
        title: '¡Exportado!',
        text: `El archivo ${nombreArchivo} se ha descargado correctamente`,
        timer: 2000,
        showConfirmButton: false
    });
}

// ========== FUNCIÓN PARA DESCARGAR VENTAS CON FOLIO ==========

/**
 * Descarga un Excel con todas las ventas que tienen folio (boletas electrónicas)
 * según el rango de fechas seleccionado
 */
async function descargarVentasConFolio() {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    
    if (!id) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró el ID de la sucursal',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Obtener fechas del filtro actual
    const startDate = startDateValue || getNowDate();
    const endDate = endDateValue || (getNowDate() + ' 23:59:59');
    
    console.log('📅 Descargando ventas con folio de', startDate, 'a', endDate);
    
    // Mostrar loader
    Swal.fire({
        title: 'Descargando...',
        html: 'Obteniendo ventas con folio (boletas electrónicas)',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Obtener ventas con boleta (que deberían tener folio)
    const sellsUrl = generarURLApi(`/web/getAppSells?id=${id}&startDate=${startDate}&endDate=${endDate}&boleta=true&per_page=10000`);
    
    console.log('🔗 URL:', sellsUrl);
    
    __conection({
        url: sellsUrl,
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function(sellsResponse) {
        console.log('📦 Respuesta ventas:', sellsResponse);
        
        // Extraer ventas del response
        const sells = [];
        if (Array.isArray(sellsResponse)) {
            sells.push(...sellsResponse);
        } else {
            for (const app in sellsResponse) {
                if (sellsResponse[app] && sellsResponse[app].original && Array.isArray(sellsResponse[app].original)) {
                    sells.push(...sellsResponse[app].original);
                }
            }
        }
        
        console.log('✅ Total ventas encontradas:', sells.length);
        
        // Filtrar solo las que tienen folio
        const ventasConFolio = sells.filter(venta => {
            const folio = venta.sell_folio || venta.folio || venta.response_folio;
            return folio !== null && folio !== undefined && folio !== '';
        });
        
        console.log('📄 Ventas con folio:', ventasConFolio.length);
        
        if (ventasConFolio.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin datos',
                text: 'No se encontraron ventas con folio (boleta electrónica) en el período seleccionado',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // DEBUG: Ver primera venta para identificar el problema
        console.log('🔍 Primera venta con folio:', ventasConFolio[0]);
        console.log('🔍 Campos de la venta:', Object.keys(ventasConFolio[0]));
        
        // Preparar datos para Excel de forma SUPER segura
        const datosExcel = [];
        
        // Encabezados
        datosExcel.push(['ID VENTA', 'FOLIO', 'NETO', 'TOTAL', 'FECHA']);
        
        // Ordenar por fecha descendente
        ventasConFolio.sort((a, b) => {
            const fechaA = new Date(a.created_at || a.fecha || a.date || 0);
            const fechaB = new Date(b.created_at || b.fecha || b.date || 0);
            return fechaB - fechaA;
        });
        
        console.log('🔄 Procesando', ventasConFolio.length, 'ventas...');
        
        console.log('🔄 Procesando', ventasConFolio.length, 'ventas...');
        
        // Agregar datos - ULTRA SEGURO
        let filasProcesadas = 0;
        ventasConFolio.forEach((venta, index) => {
            try {
                // Extraer valores de forma ultra segura
                let idVenta = '';
                let folio = '';
                let total = 0;
                let neto = 0;
                let fechaStr = '';
                
                // ID VENTA - convertir a string simple
                if (venta.id !== null && venta.id !== undefined) {
                    idVenta = '' + venta.id;
                } else if (venta.sale_id !== null && venta.sale_id !== undefined) {
                    idVenta = '' + venta.sale_id;
                }
                
                // FOLIO - convertir a string simple
                if (venta.sell_folio !== null && venta.sell_folio !== undefined) {
                    folio = '' + venta.sell_folio;
                } else if (venta.folio !== null && venta.folio !== undefined) {
                    folio = '' + venta.folio;
                } else if (venta.response_folio !== null && venta.response_folio !== undefined) {
                    folio = '' + venta.response_folio;
                }
                
                // TOTAL - convertir a número
                const totalRaw = venta.total || venta.price_total || venta.precio_total || '0';
                total = parseFloat(totalRaw);
                if (isNaN(total) || !isFinite(total)) {
                    total = 0;
                }
                
                // NETO - calcular
                neto = Math.round(total / 1.19);
                if (isNaN(neto) || !isFinite(neto)) {
                    neto = 0;
                }
                
                // FECHA - convertir a string legible
                const fechaRaw = venta.created_at || venta.fecha || venta.date || '';
                if (fechaRaw && fechaRaw !== '') {
                    try {
                        // Intentar formatear la fecha
                        const partes = ('' + fechaRaw).split(' ');
                        if (partes.length >= 2) {
                            fechaStr = partes[0] + ' ' + partes[1].substring(0, 5);
                        } else {
                            fechaStr = '' + fechaRaw;
                        }
                    } catch (e) {
                        fechaStr = '' + fechaRaw;
                    }
                } else {
                    fechaStr = '';
                }
                
                // Agregar fila con SOLO valores primitivos
                datosExcel.push([
                    idVenta,      // String
                    folio,        // String
                    neto,         // Number
                    total,        // Number
                    fechaStr      // String
                ]);
                
                filasProcesadas++;
                
            } catch (error) {
                console.error('❌ Error procesando venta', index, ':', error, venta);
            }
        });
        
        console.log('✅ Filas procesadas:', filasProcesadas);
        console.log('📊 Ejemplo de datos:', datosExcel.slice(0, 3));
        
        console.log('✅ Filas procesadas:', filasProcesadas);
        console.log('📊 Ejemplo de datos:', datosExcel.slice(0, 3));
        
        // EXPORTAR COMO EXCEL XML (igual que en exportarContadores)
        try {
            console.log('📝 Creando archivo Excel...');
            
            // Crear contenido Excel en formato XML
            let excelContent = `<?xml version="1.0"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Title>Ventas con Folio</Title>
  <Author>Sistema Fagotto</Author>
 </DocumentProperties>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Font ss:FontName="Calibri" ss:Size="11"/>
  </Style>
  <Style ss:ID="HeaderStyle">
   <Font ss:FontName="Calibri" ss:Bold="1" ss:Size="12" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#4472C4" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Ventas con Folio">
  <Table>
   <Column ss:Width="80"/>
   <Column ss:Width="100"/>
   <Column ss:Width="100"/>
   <Column ss:Width="100"/>
   <Column ss:Width="150"/>
   <Row>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">ID VENTA</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">FOLIO</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">NETO</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">TOTAL</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">FECHA</Data></Cell>
   </Row>`;
            
            // Agregar filas de datos (saltando encabezado)
            for (let i = 1; i < datosExcel.length; i++) {
                const fila = datosExcel[i];
                excelContent += `
   <Row>
    <Cell><Data ss:Type="String">${fila[0]}</Data></Cell>
    <Cell><Data ss:Type="String">${fila[1]}</Data></Cell>
    <Cell><Data ss:Type="Number">${fila[2]}</Data></Cell>
    <Cell><Data ss:Type="Number">${fila[3]}</Data></Cell>
    <Cell><Data ss:Type="String">${fila[4]}</Data></Cell>
   </Row>`;
            }
            
            excelContent += `
  </Table>
 </Worksheet>
</Workbook>`;
            
            console.log('💾 Guardando archivo Excel...');
            
            // Crear elemento de descarga
            const fechaActual = new Date().toLocaleDateString('es-CL').replace(/\//g, '-');
            const nombreArchivo = `Ventas_Folio_${fechaActual}.xls`;
            
            const link = document.createElement('a');
            link.href = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(excelContent);
            link.download = nombreArchivo;
            link.style.display = 'none';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            console.log('✅ Archivo Excel guardado exitosamente!');
            
            // Notificación tipo toast
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            Toast.fire({
                icon: 'success',
                title: `✅ Excel descargado`,
                text: `${filasProcesadas} ventas con folio - ${nombreArchivo}`
            });
            
        } catch (error) {
            console.error('❌ ERROR al crear CSV:', error);
            
            Swal.fire({
                icon: 'error',
                title: 'Error al generar archivo',
                html: `
                    <p>Error: ${error.message}</p>
                    <p class="text-muted">Por favor reporta este error al administrador.</p>
                `,
                confirmButtonText: 'OK'
            });
        }
    });
}

// ==================== MAPA DE CALOR DE SALSAS ====================

// Configuración de salsas a analizar
const SALSAS_CONFIG = [
    { nombre: 'Boloñesa', keywords: ['bolonesa', 'boloñesa'], emoji: '🍝', gramaje: 125 },
    { nombre: 'Pesto', keywords: ['pesto'], emoji: '🌿', gramaje: 80 },
    { nombre: 'Alfredo', keywords: ['alfredo'], emoji: '🧀', gramaje: 140 },
    { nombre: 'Champiñón', keywords: ['champinon', 'champiñon', 'champiñón'], emoji: '🍄', gramaje: 140 },
    { nombre: 'Camarón', keywords: ['camaron', 'camarón'], emoji: '🦐', gramaje: 140 },
    { nombre: 'Pollo Mostaza', keywords: ['pollo mostaza', 'crema/pollo/mostaza', 'crema pollo mostaza'], emoji: '🍗', gramaje: 140 }
];

let currentSauceView = 'month'; // 'month' o 'week'

// Función para cambiar vista
function setSauceView(view) {
    currentSauceView = view;
    
    // Actualizar botones
    document.getElementById('sauce-view-month').classList.toggle('active', view === 'month');
    document.getElementById('sauce-view-week').classList.toggle('active', view === 'week');
    document.getElementById('sauce-view-month').classList.toggle('btn-primary', view === 'month');
    document.getElementById('sauce-view-month').classList.toggle('btn-outline-primary', view !== 'month');
    document.getElementById('sauce-view-week').classList.toggle('btn-primary', view === 'week');
    document.getElementById('sauce-view-week').classList.toggle('btn-outline-primary', view !== 'week');
    
    // Recargar datos
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value + ' 23:59:59';
    
    if (startDate && endDate) {
        loadSauceHeatmap(startDate, endDate);
    }
}

// Cargar mapa de calor de salsas
async function loadSauceHeatmap(startDate, endDate) {
    console.log('🌶️ Cargando mapa de calor de salsas...', { startDate, endDate });
    
    try {
        // Usar getAppSells para obtener todas las ventas con productos
        const url = generarURLApi(`/web/getAppSells?id=${id}&startDate=${startDate}&endDate=${endDate}&boleta=true&factura=true&per_page=10000`);
        console.log('🔗 URL ventas para salsas:', url);
        
        await __conection({
            url: url,
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function(response) {
            console.log('✅ Respuesta de ventas recibida:', response);
            
            if (!response) {
                console.error('❌ Respuesta vacía del servidor');
                showSauceError('No se recibieron datos del servidor');
                return;
            }
            
            processSauceDataFromSells(response, startDate, endDate);
        }, function(error) {
            console.error('❌ Error en la petición de ventas:', error);
            showSauceError('Error al consultar ventas: ' + (error.message || 'Error desconocido'));
        });
    } catch (error) {
        console.error('❌ Error crítico cargando datos de salsas:', error);
        showSauceError('Error crítico: ' + error.message);
    }
}

// Procesar datos de salsas desde ventas
function processSauceDataFromSells(response, startDate, endDate) {
    console.log('🔍 Procesando ventas para extraer datos de salsas...');
    console.log('🔍 Estructura de respuesta completa:', response);
    
    // Extraer ventas - probar diferentes estructuras
    let ventas = [];
    
    // Opción 1: response[app].original.data
    for (const app in response) {
        console.log(`🔍 Procesando app: ${app}`, response[app]);
        
        if (response[app] && response[app].original) {
            console.log('🔍 Original encontrado:', response[app].original);
            
            if (response[app].original.data) {
                ventas = response[app].original.data;
                console.log('✅ Ventas en .data:', ventas.length);
                break;
            } else if (Array.isArray(response[app].original)) {
                ventas = response[app].original;
                console.log('✅ Ventas como array directo:', ventas.length);
                break;
            }
        }
    }
    
    if (!ventas || ventas.length === 0) {
        console.log('⚠️ No hay ventas para procesar');
        console.log('📊 Keys de response:', Object.keys(response));
        showSauceError('No hay datos de ventas en este período');
        return;
    }
    
    console.log(`📦 Total ventas recibidas: ${ventas.length}`);
    
    // Extraer todos los productos de las ventas
    let productos = [];
    ventas.forEach(venta => {
        if (venta.products && Array.isArray(venta.products)) {
            venta.products.forEach(producto => {
                productos.push({
                    name: producto.name,
                    quantity: producto.quantity || 1,
                    created_at: venta.created_at
                });
            });
        }
    });
    
    console.log(`📦 Total productos extraídos: ${productos.length}`);
    
    if (productos.length === 0) {
        console.log('⚠️ No hay productos en las ventas');
        showSauceError('No hay productos vendidos en este período');
        return;
    }
    
    // Ahora procesar como antes
    processSauceData(productos, startDate, endDate);
}

// Procesar datos de salsas
function processSauceData(productos, startDate, endDate) {
    console.log('🔍 Procesando productos de salsas...');
    console.log(`📦 Total productos a procesar: ${productos.length}`);
    
    // Actualizar título con fechas
    updateSauceHeatmapTitle(startDate, endDate);
    
    // Crear estructura de fechas - usar el día directo del string para evitar problemas de zona horaria
    const fechaInicioStr = startDate.split(' ')[0]; // "2025-12-18"
    const fechaFinStr = endDate.split(' ')[0];
    
    const fechaInicio = new Date(fechaInicioStr + 'T12:00:00'); // Usar mediodía para evitar problemas de zona horaria
    const fechaFin = new Date(fechaFinStr + 'T12:00:00');
    
    // Generar días del período
    const dias = [];
    const currentDate = new Date(fechaInicio);
    while (currentDate <= fechaFin) {
        dias.push(new Date(currentDate));
        currentDate.setDate(currentDate.getDate() + 1);
    }
    
    console.log(`📅 Días a procesar: ${dias.length}`);
    
    // Inicializar matriz de ventas por salsa y día
    const ventasPorSalsa = {};
    
    SALSAS_CONFIG.forEach(salsa => {
        ventasPorSalsa[salsa.nombre] = {};
        dias.forEach(dia => {
            const diaKey = dia.getDate();
            ventasPorSalsa[salsa.nombre][diaKey] = 0;
        });
    });
    
    console.log('🔍 Días inicializados:', dias.map(d => d.getDate()));
    console.log('🔍 Estructura inicial de ventasPorSalsa:', ventasPorSalsa);
    
    // Procesar cada producto
    console.log('🔍 Primeros 10 productos de ejemplo:', productos.slice(0, 10));
    
    // Mostrar TODOS los nombres únicos de productos
    const nombresUnicos = [...new Set(productos.map(p => (p.name || '').toLowerCase()))];
    console.log('📋 TODOS LOS NOMBRES DE PRODUCTOS (únicos):', nombresUnicos);
    
    let productosContados = 0;
    productos.forEach((producto, index) => {
        const nombreProducto = (producto.name || '').toLowerCase();
        const fechaVenta = new Date(producto.created_at);
        const diaVenta = fechaVenta.getDate();
        const cantidad = parseInt(producto.quantity) || 1;
        
        if (index < 10) {
            console.log(`🔍 Producto ${index + 1}:`, {
                nombre: nombreProducto,
                fecha: producto.created_at,
                dia: diaVenta,
                cantidad: cantidad
            });
        }
        
        // Buscar a qué salsa corresponde
        SALSAS_CONFIG.forEach(salsa => {
            const coincide = salsa.keywords.some(keyword => {
                const keywordLower = keyword.toLowerCase();
                const includes = nombreProducto.includes(keywordLower);
                
                if (index < 3 && includes) {
                    console.log(`🎯 MATCH! "${nombreProducto}" contiene "${keywordLower}" → ${salsa.nombre}, día ${diaVenta}`);
                    console.log(`🔍 ¿Existe ventasPorSalsa[${salsa.nombre}][${diaVenta}]? ${ventasPorSalsa[salsa.nombre][diaVenta] !== undefined}`);
                    if (ventasPorSalsa[salsa.nombre][diaVenta] === undefined) {
                        console.log(`❌ Días disponibles para ${salsa.nombre}:`, Object.keys(ventasPorSalsa[salsa.nombre]));
                    }
                }
                
                return includes;
            });
            
            if (coincide && ventasPorSalsa[salsa.nombre][diaVenta] !== undefined) {
                ventasPorSalsa[salsa.nombre][diaVenta] += cantidad;
                productosContados++;
                
                if (productosContados <= 5) {
                    console.log(`✅ Match encontrado: "${nombreProducto}" → ${salsa.nombre} (${cantidad} unidades) día ${diaVenta}`);
                }
            }
        });
    });
    
    console.log(`📊 Total productos que coinciden con salsas: ${productosContados}`);
    
    console.log('📊 Ventas procesadas por salsa:', ventasPorSalsa);
    
    // Calcular KPIs
    calculateSauceKPIs(productos);
    
    // Renderizar tabla
    renderSauceHeatmap(ventasPorSalsa, dias);
}

// Calcular KPIs de totales
function calculateSauceKPIs(productos) {
    let salsaExtra = 0;
    let fettucine = 0;
    let bigoli = 0;
    let quesoExtra = 0;
    let focaccias = 0;
    let focacciasDesglose = {}; // Objeto para contar focaccias por nombre
    
    productos.forEach(producto => {
        const nombreLower = (producto.name || '').toLowerCase();
        const nombreOriginal = producto.name || '';
        const cantidad = parseInt(producto.quantity) || 1;
        
        // Salsa Extra (categoría Extras - salsas vendidas solas, sin pasta)
        // Detectar si tiene nombre de salsa PERO NO es parte de una pasta
        const tieneSalsa = nombreLower.includes('alfredo') || 
                          nombreLower.includes('bolonesa') || nombreLower.includes('boloñesa') ||
                          nombreLower.includes('pesto') ||
                          nombreLower.includes('champinon') || nombreLower.includes('champiñon') ||
                          nombreLower.includes('camaron') || nombreLower.includes('camarón') ||
                          nombreLower.includes('pollo mostaza');
        
        const esPasta = nombreLower.includes('pasta') ||
                       nombreLower.includes('fettucine') ||
                       nombreLower.includes('bigoli');
        
        // Si tiene salsa pero NO es pasta, es salsa extra
        if (tieneSalsa && !esPasta) {
            salsaExtra += cantidad;
        }
        
        // Fettucine
        if (nombreLower.includes('fettucine')) {
            fettucine += cantidad;
        }
        
        // Bigoli
        if (nombreLower.includes('bigoli')) {
            bigoli += cantidad;
        }
        
        // Queso Extra (nombre exacto del sistema)
        if (nombreLower === 'queso extra' || nombreLower.includes('queso extra')) {
            quesoExtra += cantidad;
        }
        
        // Focaccias - contador general y desglose por nombre
        if (nombreLower.includes('focaccia')) {
            focaccias += cantidad;
            
            // Agregar al desglose
            if (!focacciasDesglose[nombreOriginal]) {
                focacciasDesglose[nombreOriginal] = 0;
            }
            focacciasDesglose[nombreOriginal] += cantidad;
        }
    });
    
    // Actualizar UI - Totales
    document.getElementById('sauceExtraTotal').textContent = salsaExtra;
    document.getElementById('fettucineTotal').textContent = fettucine;
    document.getElementById('bigoliTotal').textContent = bigoli;
    document.getElementById('quesoExtraTotal').textContent = quesoExtra;
    document.getElementById('focacciasTotal').textContent = focaccias;
    
    // Actualizar tabla de desglose de focaccias
    renderFocacciasBreakdown(focacciasDesglose);
}

// Renderizar tabla de desglose de focaccias
function renderFocacciasBreakdown(focacciasDesglose) {
    const tbody = document.getElementById('focacciasBreakdownBody');
    
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    // Convertir a array y ordenar por cantidad (mayor a menor)
    const focacciasArray = Object.entries(focacciasDesglose).sort((a, b) => b[1] - a[1]);
    
    if (focacciasArray.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="2" class="text-center text-muted py-3">
                    <i class="fas fa-info-circle me-2"></i>
                    No se vendieron focaccias en este período
                </td>
            </tr>
        `;
        return;
    }
    
    // Generar filas
    focacciasArray.forEach(([nombre, cantidad]) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td style="font-weight: 500;">
                <i class="fas fa-bread-slice me-2" style="color: #fcb69f;"></i>
                ${nombre}
            </td>
            <td style="text-align: center; font-weight: 600; color: #fcb69f;">
                ${cantidad}
            </td>
        `;
        tbody.appendChild(row);
    });
    
    // Agregar fila de total
    const totalCantidad = focacciasArray.reduce((sum, [_, cant]) => sum + cant, 0);
    const rowTotal = document.createElement('tr');
    rowTotal.style.backgroundColor = '#fff3e0';
    rowTotal.innerHTML = `
        <td style="font-weight: 700; text-align: right;">
            TOTAL FOCACCIAS:
        </td>
        <td style="text-align: center; font-weight: 700; color: #f57c00;">
            ${totalCantidad}
        </td>
    `;
    tbody.appendChild(rowTotal);
}

// Renderizar tabla de mapa de calor
function renderSauceHeatmap(ventasPorSalsa, dias) {
    const header = document.getElementById('sauceHeatmapHeader');
    const tbody = document.getElementById('sauceHeatmapBody');
    
    // Limpiar
    header.innerHTML = '<th class="sticky-col">Salsa</th>';
    tbody.innerHTML = '';
    
    // Generar encabezados de días
    dias.forEach(dia => {
        const diaMes = dia.getDate();
        const nombreDia = dia.toLocaleDateString('es-CL', { weekday: 'short' });
        header.innerHTML += `
            <th title="${dia.toLocaleDateString('es-CL', { day: 'numeric', month: 'long', year: 'numeric' })}">
                ${diaMes}<br>
                <small>${nombreDia}</small>
            </th>
        `;
    });
    
    // Agregar columna de total
    header.innerHTML += '<th style="background: #ffc107; color: #000;">Total</th>';
    
    // Calcular máximo para escalado de colores
    let maxVentas = 0;
    Object.values(ventasPorSalsa).forEach(salsaData => {
        Object.values(salsaData).forEach(cantidad => {
            if (cantidad > maxVentas) maxVentas = cantidad;
        });
    });
    
    console.log(`📈 Máximo de ventas en un día: ${maxVentas}`);
    
    // Generar filas por salsa
    let mejorSalsa = { nombre: '', total: 0 };
    
    SALSAS_CONFIG.forEach(salsa => {
        let totalSalsa = 0;
        let totalGramos = 0;
        let html = `<tr>`;
        html += `<td class="sticky-col">${salsa.emoji} ${salsa.nombre} <span style="opacity: 0.7; font-size: 0.85rem;">(${salsa.gramaje}g)</span></td>`;
        
        dias.forEach(dia => {
            const diaMes = dia.getDate();
            const cantidad = ventasPorSalsa[salsa.nombre][diaMes] || 0;
            const gramos = cantidad * salsa.gramaje;
            totalSalsa += cantidad;
            totalGramos += gramos;
            
            // Determinar color según intensidad
            const colorClass = getColorClass(cantidad, maxVentas);
            
            // Formato de peso legible
            const pesoTexto = gramos >= 1000 ? `${(gramos / 1000).toFixed(1)}kg` : `${gramos}g`;
            const tooltip = `${salsa.nombre} - ${dia.toLocaleDateString('es-CL')}: ${cantidad} unidades (${pesoTexto})`;
            
            html += `
                <td class="${colorClass}" 
                    title="${tooltip}"
                    data-salsa="${salsa.nombre}"
                    data-dia="${diaMes}"
                    data-cantidad="${cantidad}">
                    ${cantidad > 0 ? `${cantidad}<br><small style="opacity: 0.8;">${pesoTexto}</small>` : '-'}
                </td>
            `;
        });
        
        // Total - formato legible
        const totalPesoTexto = totalGramos >= 1000 ? `${(totalGramos / 1000).toFixed(1)}kg` : `${totalGramos}g`;
        html += `<td style="background: #fff3cd; font-weight: 800; font-size: 0.9rem;">${totalSalsa}<br><small style="opacity: 0.8;">${totalPesoTexto}</small></td>`;
        html += `</tr>`;
        
        tbody.innerHTML += html;
        
        // Actualizar mejor salsa
        if (totalSalsa > mejorSalsa.total) {
            mejorSalsa = { nombre: salsa.nombre, total: totalSalsa, emoji: salsa.emoji };
        }
    });
    
    // Actualizar estadísticas
    updateSauceStats(mejorSalsa, ventasPorSalsa);
    
    console.log('✅ Mapa de calor de salsas renderizado');
}

// Actualizar título del heatmap con fechas
function updateSauceHeatmapTitle(startDate, endDate) {
    const titleElement = document.getElementById('sauceHeatmapTitle');
    if (!titleElement) return;
    
    const fechaInicio = new Date(startDate.split(' ')[0] + 'T12:00:00');
    const fechaFin = new Date(endDate.split(' ')[0] + 'T12:00:00');
    
    const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
    
    let textoFecha;
    if (fechaInicio.getTime() === fechaFin.getTime()) {
        // Un solo día
        textoFecha = fechaInicio.toLocaleDateString('es-ES', opciones);
    } else {
        // Rango de fechas
        const inicioStr = fechaInicio.toLocaleDateString('es-ES', opciones);
        const finStr = fechaFin.toLocaleDateString('es-ES', opciones);
        textoFecha = `${inicioStr} - ${finStr}`;
    }
    
    titleElement.textContent = `Mapa de Calor - Ventas de Salsas (${textoFecha})`;
}

// Obtener clase de color según cantidad
function getColorClass(cantidad, maxVentas) {
    if (cantidad === 0) return 'sauce-cell-0';
    
    const porcentaje = (cantidad / maxVentas) * 100;
    
    if (porcentaje <= 10) return 'sauce-cell-1';
    if (porcentaje <= 30) return 'sauce-cell-2';
    if (porcentaje <= 60) return 'sauce-cell-3';
    if (porcentaje <= 85) return 'sauce-cell-4';
    return 'sauce-cell-5';
}

// Actualizar estadísticas de salsas
function updateSauceStats(mejorSalsa, ventasPorSalsa) {
    const statsDiv = document.getElementById('sauceStats');
    
    if (mejorSalsa.total === 0) {
        statsDiv.innerHTML = `
            <h6 class="mb-2"><i class="fas fa-trophy me-2"></i>Salsa Más Vendida</h6>
            <p class="mb-0 text-muted">No hay ventas en este período</p>
        `;
        return;
    }
    
    // Calcular porcentajes
    let totalGeneral = 0;
    Object.values(ventasPorSalsa).forEach(salsaData => {
        Object.values(salsaData).forEach(cantidad => {
            totalGeneral += cantidad;
        });
    });
    
    const porcentaje = ((mejorSalsa.total / totalGeneral) * 100).toFixed(1);
    
    statsDiv.innerHTML = `
        <h6 class="mb-2"><i class="fas fa-trophy me-2 text-warning"></i>Salsa Más Vendida</h6>
        <h4 class="mb-1">${mejorSalsa.emoji} ${mejorSalsa.nombre}</h4>
        <p class="mb-0">
            <strong>${mejorSalsa.total}</strong> unidades vendidas 
            <span class="badge bg-warning text-dark">${porcentaje}% del total</span>
        </p>
    `;
}

// Mostrar error en mapa de salsas
function showSauceError(mensaje = 'Error al cargar datos de salsas') {
    const tbody = document.getElementById('sauceHeatmapBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="100%" class="text-center text-danger py-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${mensaje}
            </td>
        </tr>
    `;
}


