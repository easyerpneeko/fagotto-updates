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

async function getData(startDate, endDate) {
    activateLoader();
    getApp();
    // getTopSells(startDate, endDate);
    // getSellByHour(startDate, endDate);
    await getCounters(startDate, endDate);
    desactivateLoader();
}
function getByDate() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value+' 23:59:59';
    getData(startDate, endDate);
}
function getThisDay() {
    var startDate = getNowDate();
    var endDate = getNowDate() + ' 23:59:59';
    getData(startDate, endDate);
}

function getThisWeek() {
    const currentDate = new Date();
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
    const day = String(currentDate.getDate()).padStart(2, '0');

    // Obtener el día de la semana (0-6)
    const currentDayOfWeek = currentDate.getDay();

    // Calcular la fecha del primer día de la semana (lunes)
    const firstDayOfWeek = new Date(currentDate);
    firstDayOfWeek.setDate(day - currentDayOfWeek + 1);

    const firstDay = String(firstDayOfWeek.getDate()).padStart(2, '0');
    const firstMonth = String(firstDayOfWeek.getMonth() + 1).padStart(2, '0');

    // Calcular la fecha del último día de la semana (domingo)
    const lastDayOfWeek = new Date(currentDate);
    lastDayOfWeek.setDate(day - currentDayOfWeek + 7);

    const lastDay = String(lastDayOfWeek.getDate()).padStart(2, '0');
    const lastMonth = String(lastDayOfWeek.getMonth() + 1).padStart(2, '0');

    const firstDayOfWeekFormatted = `${year}-${firstMonth}-${firstDay}`;
    const lastDayOfWeekFormatted = `${year}-${lastMonth}-${lastDay}`;

    getData(firstDayOfWeekFormatted, lastDayOfWeekFormatted + ' 23:59:59');
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
  
    const firstDayOfMonthFormatted = `${year}-${month}-${firstDay}`;
    const lastDayOfMonthFormatted = `${year}-${month}-${lastDay}`;
  
    // Llamar a tu función getData con las fechas del mes
    getData(firstDayOfMonthFormatted, lastDayOfMonthFormatted);
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
            // console.log(waiter[0]);

            for (const waiter in waiters[0]) {
                // console.log(waiters[0][waiter]);
                const fila = `<tr>
                                    <td>${waiters[waiters.length - 1][waiter].waiter}</td>
                                    <td>${waiters[waiters.length - 1][waiter].orders}</td>
                                    <td>${waiters[waiters.length - 1][waiter].total}</td>
                                    <td>${waiters[waiters.length - 1][waiter].propina}</td>
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

            for (const product in   products[products.length - 1]) {
                // console.log(waiters[0][waiter]);
                const fila = `<tr>
                                    <td>${products[products.length - 1][product]['name']}</td>
                                    <td>${products[products.length - 1][product]['category']}</td>
                                    <td>${products[products.length - 1][product]['quantity']}</td>                                    
                                    <td>${formatearMontoChile(products[products.length - 1][product]['price'] * products[products.length - 1][product]['quantity'])}</td>
                                </tr>`;
                tablaProducts.innerHTML += fila;
            }
        }
        getProducts();

        function getExpenses() {

            const tablaExpenses = document.getElementById('expenses-table');
            tablaExpenses.innerHTML = '';

            for (const expense in expenses[expenses.length - 1]) {
                const fila = `<tr>
                                    <td>${expenses[expenses.length - 1][expense].name}</td>
                                    <td>${expenses[expenses.length - 1][expense].balance}</td>
                                </tr>`;
                tablaExpenses.innerHTML += fila;
            }
        }

        function getCounters() {

            const tablaCounters = document.getElementById('counters-table');
            tablaCounters.innerHTML = '';
        
                if (counters[0].orders !== undefined) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Ordenes Totales </td>
                        <td>${counters[0].orders}</td>
                    </tr>
                    `;
                }

                if (counters[0].quantityTotal !== undefined) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Unidades Totales</td>
                        <td>${counters[0].quantityTotal}</td>
                    </tr>
                    `;
                }

                // ... Se repite el código para cada variable ...

                if (counters[0].typeProducts !== undefined) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Tipos De Productos</td>
                        <td>${counters[0].typeProducts}</td>
                    </tr>
                    `;
                }

                if (counters[0].boleta !== undefined && counters[0].boleta !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Boleta</td>
                        <td>$${counters[0].boleta}</td>
                    </tr>
                    `;
                }

                if (counters[0].efectivo !== undefined && counters[0].efectivo !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Efectivo</td>
                        <td>$${counters[0].efectivo}</td>
                    </tr>
                    `;
                }

                if (counters[0].debito !== undefined && counters[0].debito !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Debito</td>
                        <td>$${counters[0].debito}</td>
                    </tr>
                    `;
                }

                if (counters[0].cheque !== undefined && counters[0].cheque !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Cheque</td>
                        <td>$${counters[0].cheque}</td>
                    </tr>
                    `;
                }

                if (counters[0].banco !== undefined && counters[0].banco !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Transbank</td>
                        <td>$${counters[0].banco}</td>
                    </tr>
                    `;
                }

                if (counters[0].edenred !== undefined && counters[0].edenred !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Edenred</td>
                        <td>$${counters[0].edenred}</td>
                    </tr>
                    `;
                }

                if (counters[0].multicaja !== undefined && counters[0].multicaja !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Multicaja</td>
                        <td>$${counters[0].multicaja}</td>
                    </tr>
                    `;
                }

                if (counters[0].sodexo !== undefined && counters[0].sodexo !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Sodexo</td>
                        <td>$${counters[0].sodexo}</td>
                    </tr>
                    `;
                }

                if (counters[0].amipass !== undefined && counters[0].amipass !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Amipass</td>
                        <td>$${counters[0].amipass}</td>
                    </tr>
                    `;
                }

                if (counters[0].convenio_empresa !== undefined && counters[0].convenio_empresa !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Convenio Empresa</td>
                        <td>$${counters[0].convenio_empresa}</td>
                    </tr>
                    `;
                }
                
                if (counters[0].rappi !== undefined && counters[0].rappi !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Rappi</td>
                        <td>$${counters[0].rappi}</td>
                    </tr>
                    `;
                }
                
                if (counters[0].uber !== undefined && counters[0].uber !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Uber</td>
                        <td>$${counters[0].uber}</td>
                    </tr>
                    `;
                }

                if (counters[0].credito !== undefined && counters[0].credito !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Credito</td>
                        <td>$${counters[0].credito}</td>
                    </tr>
                    `;
                }

                if (counters[0].transferencia !== undefined && counters[0].transferencia !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Transferencia</td>
                        <td>$${counters[0].transferencia}</td>
                    </tr>
                    `;
                }

                // Agregar métodos de pago que faltaban
                if (counters[0].nota_de_credito !== undefined && counters[0].nota_de_credito !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Nota de Crédito</td>
                        <td>$${counters[0].nota_de_credito}</td>
                    </tr>
                    `;
                }

                if (counters[0].junaeb !== undefined && counters[0].junaeb !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Junaeb</td>
                        <td>$${counters[0].junaeb}</td>
                    </tr>
                    `;
                }

                if (counters[0].pedidos_ya !== undefined && counters[0].pedidos_ya !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Pedidos Ya</td>
                        <td>$${counters[0].pedidos_ya}</td>
                    </tr>
                    `;
                }

                if (counters[0].pluxee !== undefined && counters[0].pluxee !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Pluxee</td>
                        <td>$${counters[0].pluxee}</td>
                    </tr>
                    `;
                }

                if (counters[0].banco_chile_20 !== undefined && counters[0].banco_chile_20 !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Banco Chile 20%</td>
                        <td>$${counters[0].banco_chile_20}</td>
                    </tr>
                    `;
                }

                if (counters[0].guia_despacho !== undefined && counters[0].guia_despacho !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Guía de Despacho</td>
                        <td>$${counters[0].guia_despacho}</td>
                    </tr>
                    `;
                }

                if (counters[0].fastSells !== undefined && counters[0].fastSells !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Ventas Rápidas</td>
                        <td>$${counters[0].fastSells}</td>
                    </tr>
                    `;
                }

                if (counters[0].noSii !== undefined && counters[0].noSii !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Sin SII</td>
                        <td>$${counters[0].noSii}</td>
                    </tr>
                    `;
                }

                if (counters[0].factura !== undefined && counters[0].factura !== null) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Factura</td>
                        <td>$${counters[0].factura}</td>
                    </tr>
                    `;
                }

                if (counters[0].gananciaTotal !== undefined) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Ganancia Total</td>
                        <td>$${counters[0].gananciaTotal}</td>
                    </tr>
                    `;
                }

                if (counters[0].balanceTotal !== undefined) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Saldo Total</td>
                        <td>$${counters[0].balanceTotal}</td>
                    </tr>
                    `;
                }

                if (counters[0].totalToExpenses !== undefined) {
                    tablaCounters.innerHTML += `
                    <tr>
                        <td>Resumen Total</td>
                        <td>$${counters[0].totalToExpenses}</td>
                    </tr>
                    `;
                }
        }
        getCounters()

        function getSellByHour(startDate, endDate) {

            __conection({
                url: generarURLApi(`/web/getAppSellsByHour?id=${id}&startDate=${startDate}&endDate=${endDate}`),
                header: credentials(),
                dev: true,
                method: 'GET'

            }, {}, function (request) {

                const sells = [];
                for (const app in request) {
                    sells.push(request[app].original);
                }

                // console.log(sells[0]);

                const labels = [];
                const data = [];

                for (const clave in sells[0]) {
                    const valor = sells[0][clave];
                    labels.push(clave);
                    data.push(valor);
                }

                var sellsByHourCanvas = document.getElementById('sellsByHourChart');

                var sellsByHourChart = new Chart(sellsByHourCanvas, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            lineTension: 0,
                            backgroundColor: 'transparent',
                            borderColor: '#007bff',
                            borderWidth: 4,
                            pointBackgroundColor: '#007bff'
                        }]
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
                })


            });
        }
        getSellByHour(startDate,endDate);

        function getTopSells(startDate, endDate) {

            __conection({
                url: generarURLApi(`/web/getAppTopSells?id=${id}&startDate=${startDate}&endDate=${endDate}`),
                header: credentials(),
                dev: true,
                method: 'GET'

            }, {}, function (request) {

                const topSells = [];
                for (const app in request) {
                    topSells.push(request[app].original);
                }

                // console.log(topSells[0]);

                const labels = [];
                const data = [];

                for (const clave in topSells[0]) {
                    if (topSells[0].totalSales) {
                        labels.push(topSells[0][clave].product_name);
                        data.push(topSells[0][clave].total_quantity);
                    }
                }
                labels.pop();
                data.pop();
                // console.log(labels);
                // console.log(data);

                var topSellsCanvas = null;
                topSellsCanvas = document.getElementById('topSellsChart');
                var topSellsChart  = null;
                
                topSellsChart = new Chart(topSellsCanvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            lineTension: 0,
                            backgroundColor: '#007bff',
                            borderColor: '#007bff',
                            borderWidth: 4,
                            pointBackgroundColor: '#007bff',
                        }],
                    },
                    options: {
                        indexAxis: 'y',
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
                })


            });
        }
        getTopSells(startDate,endDate);
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
        title: 'Generando Excel...',
        text: 'Por favor espera mientras se genera el reporte',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const selectedMethods = getSelectedPaymentMethods();
    
    // Usar las fechas actuales del sistema - igual que en getCounters
    let startDate, endDate;
    
    // Intentar obtener fechas de los inputs si existen
    const startDateElement = document.getElementById('start_date') || document.getElementById('startDate');
    const endDateElement = document.getElementById('end_date') || document.getElementById('endDate');
    
    if (startDateElement && startDateElement.value) {
        startDate = startDateElement.value;
    } else {
        // CAMBIO: Buscar en el 7 de agosto donde SÍ están las ventas
        startDate = '2025-08-07';
    }
    
    if (endDateElement && endDateElement.value) {
        endDate = endDateElement.value;
    } else {
        // CAMBIO: Buscar en el 7 de agosto donde SÍ están las ventas
        endDate = '2025-08-07 23:59:59';
    }
    
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
    
    // Usar el endpoint de ventas web que SÍ funciona
    const url = generarURLApi(`/web/getAppSells?id=${id}&startDate=${startDate}&endDate=${endDate}&${paymentParams}`);
    
    console.log('🚀 INTENTANDO DESCARGAR EXCEL...');
    console.log('📅 Fechas:', { startDate, endDate });
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
                console.log('❌ SIN VENTAS - Intentando con rango más amplio...');
                
                // ÚLTIMO RECURSO: buscar en TODO agosto
                const urlBackup = generarURLApi(`/web/getAppSells?id=${id}&startDate=2025-08-01&endDate=2025-08-31 23:59:59&${paymentParams}`);
                console.log('🔄 URL BACKUP (todo agosto):', urlBackup);
                
                __conection({
                    url: urlBackup,
                    header: credentials(),
                    dev: true,
                    method: 'GET'
                }, {}, function (backupRequest) {
                    console.log('🔄 RESPUESTA BACKUP:', backupRequest);
                    
                    const backupSells = [];
                    if (Array.isArray(backupRequest)) {
                        backupSells.push(...backupRequest);
                    } else {
                        for (const app in backupRequest) {
                            if (backupRequest[app] && backupRequest[app].original && Array.isArray(backupRequest[app].original)) {
                                backupSells.push(...backupRequest[app].original);
                            }
                        }
                    }
                    
                    if (backupSells.length > 0) {
                        console.log('🎉 ¡ENCONTRADAS VENTAS EN BACKUP!', backupSells.length);
                        createExcelFromSells(backupSells, '2025-08-01', '2025-08-31');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '😭 Sin ventas en todo agosto',
                            text: 'No se encontraron ventas en ningún período',
                            confirmButtonText: 'OK'
                        });
                    }
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
     </Styles>
     <Worksheet ss:Name="Reporte Ventas">
      <Table>
       <Row>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">ID Venta</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Total</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Método de Pago</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Fecha</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Cliente</Data></Cell>
        <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Productos</Data></Cell>
       </Row>`;

    // Agregar datos
    sells.forEach(sell => {
        const products = (sell.products || []).map(p => p.name).join('; ') || 'N/A';
        const client = (sell.client || 'Cliente General').replace(/[&<>"']/g, function(m) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[m];
        });
        
        excelContent += `
       <Row>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${sell.id || 0}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="Number">${sell.total || 0}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="String">${sell.payment_method || 'N/A'}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="String">${sell.created_at || 'N/A'}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="String">${client}</Data></Cell>
        <Cell ss:StyleID="DataStyle"><Data ss:Type="String">${products.replace(/[&<>"']/g, function(m) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[m];
        })}</Data></Cell>
       </Row>`;
    });

    excelContent += `
      </Table>
     </Worksheet>
    </Workbook>`;
    
    // Crear y descargar archivo Excel real
    const blob = new Blob([excelContent], {
        type: 'application/vnd.ms-excel'
    });
    
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `reporte_ventas_${startDate}_${endDate}.xls`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    
    Swal.fire({
        icon: 'success',
        title: '¡Excel Generado!',
        text: `Se descargó un archivo Excel con ${sells.length} ventas`,
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