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

    console.log(firstDayOfWeekFormatted);
    console.log(lastDayOfWeekFormatted + ' 23:59:59');

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
  
    console.log(firstDayOfMonthFormatted);
    console.log(lastDayOfMonthFormatted);
  
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
        console.log('🔍 DEBUG - URL de contadores:', url);
    }else{
        url =generarURLApi(`/web/getAppCounters?id=${id}`);
    }

    await __conection({
        url: url,
        header: credentials(),
        dev: true,
        method: 'GET'

    }, {}, function (request) {
        console.log("Counters:", request);
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

            console.log('Products: ',products);

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
                console.log("byhour", request);

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
                console.log("top sells", request);

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
    console.log('🔄 Iniciando descarga de Excel...');
    
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
        // Usar los últimos 30 días por defecto para mayor probabilidad de encontrar datos
        const today = new Date();
        const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
        startDate = thirtyDaysAgo.toISOString().split('T')[0];
    }
    
    if (endDateElement && endDateElement.value) {
        endDate = endDateElement.value;
    } else {
        // Usar fecha de hoy por defecto con hora final
        endDate = getNowDate() + ' 23:59:59';
    }
    
    console.log('📅 Fechas:', { startDate, endDate });
    console.log('💳 Métodos de pago seleccionados:', selectedMethods);
    
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
    console.log('🌐 URL completa:', url);

    __conection({
        url: url,
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        console.log('🔍 DEBUGGING - Respuesta completa del servidor:', request);
        console.log('🔍 DEBUGGING - Tipo de respuesta:', typeof request);
        console.log('🔍 DEBUGGING - Es array?', Array.isArray(request));
        console.log('🔍 DEBUGGING - Keys de la respuesta:', Object.keys(request || {}));
        
        // Intentar diferentes formas de acceder a los datos
        let sells = [];
        
        try {
            // Método 1: Como lo teníamos antes
            for (const app in request) {
                console.log('🔍 App:', app, 'Contenido:', request[app]);
                if (request[app] && request[app].original && Array.isArray(request[app].original)) {
                    console.log('✅ Encontrado original array con', request[app].original.length, 'elementos');
                    sells.push(...request[app].original);
                } else if (request[app] && Array.isArray(request[app])) {
                    console.log('✅ Encontrado array directo con', request[app].length, 'elementos');
                    sells.push(...request[app]);
                }
            }
            
            // Método 2: Si la respuesta es un array directo
            if (Array.isArray(request)) {
                console.log('✅ La respuesta es un array directo con', request.length, 'elementos');
                sells = request;
            }
            
            // Método 3: Si hay una propiedad 'data' o similar
            if (request && request.data && Array.isArray(request.data)) {
                console.log('✅ Encontrados datos en request.data con', request.data.length, 'elementos');
                sells = request.data;
            }
            
            console.log('📋 Total de ventas procesadas:', sells.length);
            
            if (sells.length === 0) {
                console.log('⚠️ No se encontraron ventas, generando Excel vacío...');
                
                // Generar Excel vacío pero válido
                createEmptyExcel(startDate, endDate);
                return;
            }
            
            // Crear Excel manualmente con los datos
            createExcelFromSells(sells, startDate, endDate);
            
        } catch (error) {
            console.error('❌ Error procesando datos:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error procesando datos',
                text: `Error: ${error.message}`,
                confirmButtonText: 'Entendido'
            });
        }
    }, function(error) {
        console.error('❌ Error en la petición principal:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar al servidor para obtener las ventas',
            confirmButtonText: 'Entendido'
        });
    });
}

// Función para crear Excel desde los datos de ventas
function createExcelFromSells(sells, startDate, endDate) {
    console.log('📝 Creando Excel con', sells.length, 'ventas');
    
    // Crear contenido Excel XML real con estilos
    let excelContent = `<?xml version="1.0"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Title>Reporte de Ventas - Fagotto ERP</Title>
  <Author>Sistema Fagotto ERP</Author>
  <Created>${new Date().toISOString()}</Created>
 </DocumentProperties>
 
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>8000</WindowHeight>
  <WindowWidth>15000</WindowWidth>
  <WindowTopX>0</WindowTopX>
  <WindowTopY>0</WindowTopY>
 </ExcelWorkbook>
 
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#000000"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  
  <!-- Estilo para el título principal -->
  <Style ss:ID="TitleStyle">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="18" ss:Color="#FFFFFF" ss:Bold="1"/>
   <Interior ss:Color="#1f4e79" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
  </Style>
  
  <!-- Estilo para headers de columna -->
  <Style ss:ID="HeaderStyle">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="12" ss:Color="#FFFFFF" ss:Bold="1"/>
   <Interior ss:Color="#2e74b5" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  
  <!-- Estilo para datos normales -->
  <Style ss:ID="DataStyle">
   <Alignment ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="10"/>
   <Interior ss:Color="#f8f9fa" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  
  <!-- Estilo para datos alternos -->
  <Style ss:ID="DataAltStyle">
   <Alignment ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="10"/>
   <Interior ss:Color="#e9ecef" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  
  <!-- Estilo para montos -->
  <Style ss:ID="MoneyStyle">
   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1"/>
   <Interior ss:Color="#d4edda" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <NumberFormat ss:Format="&quot;$&quot;#,##0"/>
  </Style>
  
  <!-- Estilo para totales -->
  <Style ss:ID="TotalStyle">
   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="12" ss:Color="#FFFFFF" ss:Bold="1"/>
   <Interior ss:Color="#28a745" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
   <NumberFormat ss:Format="&quot;$&quot;#,##0"/>
  </Style>
  
  <!-- Estilo para información -->
  <Style ss:ID="InfoStyle">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#495057" ss:Italic="1"/>
   <Interior ss:Color="#fff3cd" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
 </Styles>

 <Worksheet ss:Name="Reporte de Ventas">
  <Table>
   <!-- Título principal -->
   <Row ss:Height="30">
    <Cell ss:MergeAcross="5" ss:StyleID="TitleStyle">
     <Data ss:Type="String">📊 REPORTE DE VENTAS - FAGOTTO ERP</Data>
    </Cell>
   </Row>
   
   <!-- Información del período -->
   <Row ss:Height="25">
    <Cell ss:MergeAcross="5" ss:StyleID="InfoStyle">
     <Data ss:Type="String">Período: ${startDate} al ${endDate} | Total de registros: ${sells.length}</Data>
    </Cell>
   </Row>
   
   <!-- Espacio -->
   <Row ss:Height="15"><Cell></Cell></Row>
   
   <!-- Headers de columnas -->
   <Row ss:Height="25">
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">🔢 ID Venta</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">💰 Total</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">💳 Método Pago</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">📅 Fecha</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">👤 Cliente</Data></Cell>
    <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">🛍️ Productos</Data></Cell>
   </Row>`;

    // Calcular totales
    let totalVentas = 0;
    const metodosContador = {};
    
    // Agregar datos con colores alternos
    sells.forEach((sell, index) => {
        const total = parseFloat(sell.total) || 0;
        totalVentas += total;
        
        const metodo = sell.payment_method || 'Sin especificar';
        metodosContador[metodo] = (metodosContador[metodo] || 0) + total;
        
        const styleId = index % 2 === 0 ? 'DataStyle' : 'DataAltStyle';
        const products = (sell.products || []).map(p => p.name || p).join('; ') || 'N/A';
        const client = (sell.client || 'Cliente General').toString().replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
        });
        
        excelContent += `
   <Row ss:Height="20">
    <Cell ss:StyleID="${styleId}"><Data ss:Type="Number">${sell.id || (index + 1)}</Data></Cell>
    <Cell ss:StyleID="MoneyStyle"><Data ss:Type="Number">${total}</Data></Cell>
    <Cell ss:StyleID="${styleId}"><Data ss:Type="String">${metodo}</Data></Cell>
    <Cell ss:StyleID="${styleId}"><Data ss:Type="String">${sell.created_at || 'N/A'}</Data></Cell>
    <Cell ss:StyleID="${styleId}"><Data ss:Type="String">${client}</Data></Cell>
    <Cell ss:StyleID="${styleId}"><Data ss:Type="String">${products.replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
        })}</Data></Cell>
   </Row>`;
    });

    // Agregar resumen de totales
    excelContent += `
   <!-- Espacio -->
   <Row ss:Height="15"><Cell></Cell></Row>
   
   <!-- Resumen por método de pago -->
   <Row ss:Height="25">
    <Cell ss:MergeAcross="5" ss:StyleID="HeaderStyle">
     <Data ss:Type="String">💳 RESUMEN POR MÉTODOS DE PAGO</Data>
    </Cell>
   </Row>`;

    Object.keys(metodosContador).forEach((metodo, index) => {
        const monto = metodosContador[metodo];
        const porcentaje = ((monto / totalVentas) * 100).toFixed(1);
        const styleId = index % 2 === 0 ? 'DataStyle' : 'DataAltStyle';
        
        excelContent += `
   <Row ss:Height="20">
    <Cell ss:StyleID="${styleId}"><Data ss:Type="String">${metodo}</Data></Cell>
    <Cell ss:StyleID="MoneyStyle"><Data ss:Type="Number">${monto}</Data></Cell>
    <Cell ss:StyleID="${styleId}"><Data ss:Type="String">${porcentaje}%</Data></Cell>
    <Cell ss:StyleID="${styleId}"><Data ss:Type="String">-</Data></Cell>
    <Cell ss:StyleID="${styleId}"><Data ss:Type="String">-</Data></Cell>
    <Cell ss:StyleID="${styleId}"><Data ss:Type="String">-</Data></Cell>
   </Row>`;
    });

    // Total general
    excelContent += `
   <!-- Espacio -->
   <Row ss:Height="10"><Cell></Cell></Row>
   
   <!-- Total general -->
   <Row ss:Height="30">
    <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">🎯 TOTAL GENERAL</Data></Cell>
    <Cell ss:StyleID="TotalStyle"><Data ss:Type="Number">${totalVentas}</Data></Cell>
    <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">${sells.length} ventas</Data></Cell>
    <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">Promedio: $${Math.round(totalVentas / sells.length)}</Data></Cell>
    <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">Período completo</Data></Cell>
    <Cell ss:StyleID="TotalStyle"><Data ss:Type="String">✅ COMPLETADO</Data></Cell>
   </Row>
   
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
    
    console.log('💾 Archivo Excel (.xls) descargado exitosamente');
    
    Swal.fire({
        icon: 'success',
        title: '¡📊 Excel Generado!',
        html: `
            <div style="text-align: left; padding: 15px;">
                <h4 style="color: #28a745; margin-bottom: 10px;">✅ Archivo Excel generado exitosamente</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin: 5px 0;"><strong>📋 Total ventas:</strong> $${totalVentas.toLocaleString()}</li>
                    <li style="margin: 5px 0;"><strong>🛒 Cantidad:</strong> ${sells.length} transacciones</li>
                    <li style="margin: 5px 0;"><strong>💳 Promedio:</strong> $${Math.round(totalVentas / sells.length).toLocaleString()}</li>
                    <li style="margin: 5px 0;"><strong>🎨 Formato:</strong> Excel con colores y estilos</li>
                </ul>
            </div>
        `,
        timer: 6000,
        showConfirmButton: true,
        confirmButtonText: 'Perfecto! 🎉'
    });
}

// Función para crear Excel vacío pero válido
function createEmptyExcel(startDate, endDate) {
    console.log('📝 Creando Excel vacío para el período', startDate, '-', endDate);
    
    // Crear contenido Excel XML válido pero sin datos
    let excelContent = `<?xml version="1.0"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Title>Reporte de Ventas Vacío - Fagotto ERP</Title>
  <Author>Sistema Fagotto ERP</Author>
  <Created>${new Date().toISOString()}</Created>
 </DocumentProperties>
 
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>8000</WindowHeight>
  <WindowWidth>15000</WindowWidth>
 </ExcelWorkbook>
 
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Font ss:FontName="Calibri" ss:Size="11"/>
  </Style>
  
  <Style ss:ID="TitleStyle">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="18" ss:Color="#FFFFFF" ss:Bold="1"/>
   <Interior ss:Color="#1f4e79" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
   </Borders>
  </Style>
  
  <Style ss:ID="HeaderStyle">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="12" ss:Color="#FFFFFF" ss:Bold="1"/>
   <Interior ss:Color="#dc3545" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  
  <Style ss:ID="InfoStyle">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Font ss:FontName="Calibri" ss:Size="12" ss:Color="#721c24"/>
   <Interior ss:Color="#f8d7da" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
 </Styles>

 <Worksheet ss:Name="Sin Datos">
  <Table>
   <Row ss:Height="30">
    <Cell ss:MergeAcross="5" ss:StyleID="TitleStyle">
     <Data ss:Type="String">📊 REPORTE DE VENTAS - FAGOTTO ERP</Data>
    </Cell>
   </Row>
   
   <Row ss:Height="25">
    <Cell ss:MergeAcross="5" ss:StyleID="HeaderStyle">
     <Data ss:Type="String">⚠️ SIN DATOS DISPONIBLES</Data>
    </Cell>
   </Row>
   
   <Row ss:Height="15"><Cell></Cell></Row>
   
   <Row ss:Height="20">
    <Cell ss:MergeAcross="5" ss:StyleID="InfoStyle">
     <Data ss:Type="String">Período consultado: ${startDate} al ${endDate}</Data>
    </Cell>
   </Row>
   
   <Row ss:Height="20">
    <Cell ss:MergeAcross="5" ss:StyleID="InfoStyle">
     <Data ss:Type="String">No se encontraron ventas para este período</Data>
    </Cell>
   </Row>
   
   <Row ss:Height="20">
    <Cell ss:MergeAcross="5" ss:StyleID="InfoStyle">
     <Data ss:Type="String">Posibles causas: Sin ventas registradas, filtros muy restrictivos, problemas de conexión</Data>
    </Cell>
   </Row>
   
  </Table>
 </Worksheet>
</Workbook>`;
    
    // Crear y descargar archivo Excel
    const blob = new Blob([excelContent], {
        type: 'application/vnd.ms-excel'
    });
    
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `reporte_sin_datos_${startDate}_${endDate}.xls`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    
    console.log('💾 Archivo Excel vacío descargado');
    
    Swal.fire({
        icon: 'warning',
        title: '⚠️ Sin Datos Disponibles',
        html: `
            <div style="text-align: left; padding: 15px;">
                <p><strong>📅 Período:</strong> ${startDate} al ${endDate}</p>
                <p><strong>📊 Resultado:</strong> No se encontraron ventas</p>
                <p><strong>💾 Archivo:</strong> Excel generado con información del estado</p>
                <hr>
                <p><em>Intenta con un rango de fechas diferente o verifica que haya ventas registradas.</em></p>
            </div>
        `,
        confirmButtonText: 'Entendido'
    });
}

// Función para crear reporte básico desde contadores
function createBasicReport(countersData, startDate, endDate) {
    console.log('📊 Creando reporte básico desde contadores...');
    
    let csvContent = "data:text/csv;charset=utf-8,";
    
    // Headers para reporte de contadores
    csvContent += "Concepto,Valor\n";
    csvContent += `Periodo,${startDate} - ${endDate}\n`;
    csvContent += "\n";
    
    try {
        // Extraer datos de contadores
        let counters = null;
        for (const app in countersData) {
            if (countersData[app] && countersData[app].original && countersData[app].original.counters) {
                counters = countersData[app].original.counters;
                break;
            }
        }
        
        if (counters) {
            console.log('✅ Encontrados contadores:', counters);
            
            // Agregar todos los campos disponibles
            Object.keys(counters).forEach(key => {
                if (counters[key] !== undefined && counters[key] !== null) {
                    const value = typeof counters[key] === 'number' && key !== 'orders' && key !== 'quantityTotal' && key !== 'typeProducts' 
                        ? `$${counters[key]}` 
                        : counters[key];
                    csvContent += `${key},${value}\n`;
                }
            });
        } else {
            console.log('❌ No se encontraron contadores en la respuesta');
            csvContent += "Error,No se encontraron datos de contadores\n";
        }
        
    } catch (error) {
        console.error('❌ Error procesando contadores:', error);
        csvContent += `Error,${error.message}\n`;
    }
    
    // Crear y descargar archivo
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `reporte_contadores_${startDate}_${endDate}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    console.log('💾 Archivo de contadores descargado exitosamente');
    
    Swal.fire({
        icon: 'info',
        title: '¡Reporte de Contadores Generado!',
        text: 'Se generó un reporte básico con los datos disponibles de contadores',
        timer: 4000,
        showConfirmButton: true,
        confirmButtonText: 'Entendido'
    });
}

// Función de prueba para debugging
window.testExcelExport = function() {
    console.log('🧪 PRUEBA DE EXCEL EXPORT');
    
    // Verificar configuración
    console.log('⚙️ Configuración actual:');
    console.log('- Server URL:', typeof serverURL !== 'undefined' ? serverURL : 'NO DEFINIDO');
    console.log('- Serial:', _store().serial.get());
    
    // Probar la descarga
    downloadCustomExcel();
};

// Función para probar endpoints disponibles
window.testEndpoints = function() {
    console.log('🔍 PROBANDO TODOS LOS ENDPOINTS DISPONIBLES');
    
    const startDate = getNowDate();
    const endDate = getNowDate() + ' 23:59:59';
    
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
    
    // Probar diferentes endpoints
    const endpoints = [
        `/web/getAppSells?id=${id}&startDate=${startDate}&endDate=${endDate}`,
        `/web/getAppSells?id=${id}&startDate=${startDate}&endDate=${endDate}&${paymentParams}`,
        `/web/getAppCounters?id=${id}&startDate=${startDate}&endDate=${endDate}&${paymentParams}`,
        `/web/getAppSellsByHour?id=${id}&startDate=${startDate}&endDate=${endDate}`,
        `/web/getAppTopSells?id=${id}&startDate=${startDate}&endDate=${endDate}`
    ];
    
    endpoints.forEach((endpoint, index) => {
        console.log(`\n🔗 Probando endpoint ${index + 1}: ${endpoint}`);
        
        __conection({
            url: generarURLApi(endpoint),
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function (response) {
            console.log(`✅ Respuesta del endpoint ${index + 1}:`, response);
            console.log(`📊 Tipo:`, typeof response, 'Array:', Array.isArray(response));
            if (response && typeof response === 'object') {
                console.log(`🔑 Keys:`, Object.keys(response));
            }
        });
    });
};

// Función para probar con diferentes rangos de fechas
window.testWithDifferentDates = function() {
    console.log('📅 PROBANDO CON DIFERENTES RANGOS DE FECHAS');
    
    const today = new Date();
    const dateRanges = [
        // Hoy específico
        {
            start: '2025-08-08',
            end: '2025-08-08 23:59:59',
            name: 'Hoy (2025-08-08)'
        },
        // Ayer específico
        {
            start: '2025-08-07',
            end: '2025-08-07 23:59:59',
            name: 'Ayer (2025-08-07)'
        },
        // Últimos 2 días
        {
            start: '2025-08-07',
            end: '2025-08-08 23:59:59',
            name: 'Últimos 2 días (7-8 agosto)'
        },
        // Últimos 7 días
        {
            start: new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            end: today.toISOString().split('T')[0] + ' 23:59:59',
            name: 'Últimos 7 días'
        },
        // Todo agosto
        {
            start: '2025-08-01',
            end: '2025-08-31 23:59:59',
            name: 'Todo Agosto 2025'
        }
    ];
    
    dateRanges.forEach((range, index) => {
        setTimeout(() => {
            console.log(`\n📅 === PROBANDO ${range.name} ===`);
            console.log(`🕐 Desde: ${range.start}`);
            console.log(`🕐 Hasta: ${range.end}`);
            
            const paymentParams = [
                'fastSell=true',
                'boleta=true', 
                'factura=true',
                'efectivo=true',
                'debito=true',
                'credito=true'
            ].join('&');
            
            const url = generarURLApi(`/web/getAppSells?id=${id}&startDate=${range.start}&endDate=${range.end}&${paymentParams}`);
            console.log(`🌐 URL: ${url}`);
            
            __conection({
                url: url,
                header: credentials(),
                dev: true,
                method: 'GET'
            }, {}, function (response) {
                console.log(`📊 === RESULTADO ${range.name} ===`);
                console.log('🔍 Respuesta completa:', response);
                console.log('🔍 Tipo:', typeof response);
                console.log('🔍 Es array?', Array.isArray(response));
                console.log('🔍 Keys:', Object.keys(response || {}));
                
                let totalVentas = 0;
                let ventasArray = [];
                
                // Intentar extraer ventas
                try {
                    for (const app in response) {
                        if (response[app] && response[app].original && Array.isArray(response[app].original)) {
                            ventasArray.push(...response[app].original);
                        }
                    }
                    
                    if (Array.isArray(response)) {
                        ventasArray = response;
                    }
                    
                    totalVentas = ventasArray.length;
                } catch (error) {
                    console.error('❌ Error extrayendo ventas:', error);
                }
                
                console.log(`📋 Total ventas encontradas para ${range.name}: ${totalVentas}`);
                
                if (totalVentas > 0) {
                    console.log('🎉 ¡ENCONTRADAS VENTAS! Detalles:');
                    ventasArray.slice(0, 3).forEach((venta, i) => {
                        console.log(`  Venta ${i + 1}:`, {
                            id: venta.id,
                            total: venta.total,
                            fecha: venta.created_at,
                            metodo: venta.payment_method
                        });
                    });
                } else {
                    console.log('❌ No se encontraron ventas para este período');
                }
                
                console.log('─'.repeat(50));
            }, function(error) {
                console.error(`❌ Error en petición ${range.name}:`, error);
            });
            
        }, index * 2000); // Esperar 2 segundos entre cada prueba
    });
};

// Función para descargar Excel con últimos 30 días garantizado
window.downloadExcelUltimos30Dias = function() {
    console.log('📅 Descargando Excel con los últimos 30 días...');
    
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
    
    const startDate = thirtyDaysAgo.toISOString().split('T')[0];
    const endDate = today.toISOString().split('T')[0] + ' 23:59:59';
    
    console.log(`🔍 Buscando ventas desde ${startDate} hasta ${endDate}`);
    
    Swal.fire({
        title: 'Generando Excel últimos 30 días...',
        text: 'Buscando ventas en un período más amplio',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

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
    
    const url = generarURLApi(`/web/getAppSells?id=${id}&startDate=${startDate}&endDate=${endDate}&${paymentParams}`);
    console.log('🌐 URL 30 días:', url);

    __conection({
        url: url,
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        console.log('📊 Datos últimos 30 días:', request);
        
        try {
            let sells = [];
            
            // Intentar diferentes formas de extraer datos
            for (const app in request) {
                if (request[app] && request[app].original && Array.isArray(request[app].original)) {
                    sells.push(...request[app].original);
                } else if (request[app] && Array.isArray(request[app])) {
                    sells.push(...request[app]);
                }
            }
            
            if (Array.isArray(request)) {
                sells = request;
            }
            
            if (request && request.data && Array.isArray(request.data)) {
                sells = request.data;
            }
            
            console.log(`📋 Total ventas encontradas en 30 días: ${sells.length}`);
            
            if (sells.length > 0) {
                createExcelFromSells(sells, startDate.split(' ')[0], endDate.split(' ')[0]);
            } else {
                createEmptyExcel(startDate.split(' ')[0], endDate.split(' ')[0]);
            }
            
        } catch (error) {
            console.error('❌ Error procesando datos 30 días:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error procesando datos',
                text: `Error: ${error.message}`,
                confirmButtonText: 'Entendido'
            });
        }
    }, function(error) {
        console.error('❌ Error en petición 30 días:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar al servidor',
            confirmButtonText: 'Entendido'
        });
    });
};

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