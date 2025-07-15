$(document).ready(function () {
    // _name.text(_store().session.user().get("username")) //obtenemos el nombre del usuario
    // requestLoadCounters()
    // var startDate = '2022-12-14 06:00:00';
    // var endDate = '2023-12-30 23:59:59';
    getApp();
    getCounters(startDate, endDate);
    getSellByHour();
    getTopSells(startDate, endDate);
})

const app_name = document.getElementById('app-name');
var startDateValue;
var endDateValue;
const urlParams = new URLSearchParams(window.location.search);
const id = urlParams.get('id');
const waiters=[];
const workshifts=[];
const products=[];
const expenses=[];

function calculate() {
    startDateValue = document.getElementById("startDate").value;
    endDateValue = document.getElementById("endDate").value;
    startDate = startDateValue + ' 06:00:00';
    endDate = endDateValue + ' 23:59:59';

    getApp();
    getCounters(startDate, endDate);
    getSellByHour();
    getTopSells(startDate, endDate);
    
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
    const url = URL(`/web/getApp?id=${id}`);

    __conection({
        url: url,
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        app_name.innerHTML = request[0].name;
    });
}

function getCounters(startDate, endDate) {

    __conection({
        url: URL(`/web/getAppCounters?id=${id}&startDate=${startDate}&endDate=${endDate}`),
        header: credentials(),
        dev: true,
        method: 'GET'

    }, {}, function (request) {
        console.log("counters", request);
        const counters = [];
        const labels = [];
        const counterData = [];

        for (const app in request) {
            counters.push(request[app].original.counters);
            waiters.push(request[app].original.waiters);
            workshifts.push(request[app].original.workshifts);
            products.push(request[app].original.products);
            expenses.push(request[app].original.expenses);
        }
        
        function getWaiters(){
            // console.log(waiters[0]);
            const names = [];
            const dataWaiters = [];

            for (const clave in waiters[0]) {
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
                      'rgb(255, 155, 86)',
                      'rgb(255, 150, 132)',
                      'rgb(54, 0, 235)',
                      'rgb(0, 255, 86)',
                      'rgb(255, 255, 132)',
                      'rgb(255, 0, 0)',
                      'rgb(0, 255, 86)',
                      'rgb(255, 255, 132)',
                      'rgb(255, 0, 0)',
                      'rgb(0, 255, 86)',
                      'rgb(255, 255, 132)',
                      'rgb(255, 0, 0)'
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
            tablaWaiters.innerHTML='';
            // console.log(waiter[0]);

            for (const waiter in waiters[0]) {
                // console.log(waiters[0][waiter]);
                const fila = `<tr>
                                    <td>${waiters[0][waiter].waiter}</td>
                                    <td>${waiters[0][waiter].orders}</td>
                                    <td>${waiters[0][waiter].total}</td>
                                    <td>${waiters[0][waiter].propina}</td>
                                </tr>`;
                 tablaWaiters.innerHTML += fila;
            }
        }
        getWaiters();

        function getWorkshift(){
            // console.log(workshifts[0]);

            const tablaWorkshift = document.getElementById('workshifts-table');
            tablaWorkshift.innerHTML='';
            // // console.log(waiter[0]);

            for (const workshift in workshifts[0]) {
                // console.log(waiters[0][waiter]);
                const fila = `<tr>
                                    <td>${workshifts[0][workshift].user.fullname}</td>
                                    <td>${workshifts[0][workshift].init_money}</td>
                                    <td>${workshifts[0][workshift].final_money}</td>
                                    <td>${workshifts[0][workshift].start_workshift}</td>
                                    <td>${workshifts[0][workshift].end_workshift}</td>
                                </tr>`;
                tablaWorkshift.innerHTML += fila;
            }
        }
        getWorkshift();

        function getProducts(){
            // console.log(products[0]);

            const tablaProducts = document.getElementById('products-table');
            tablaProducts.innerHTML='';
            // // console.log(waiter[0]);

            for (const product in products[0]) {
                // console.log(waiters[0][waiter]);
                const fila = `<tr>
                                    <td>${products[0][product][1]}</td>
                                    <td>${products[0][product][2]}</td>
                                    <td>${products[0][product][3] / products[0][product][2]}</td>
                                    <td>${products[0][product][4]}</td>
                                    <td>${products[0][product][3]}</td>
                                </tr>`;
                tablaProducts.innerHTML += fila;
            }
        }
        getProducts();

        function getExpenses(){
            console.log(expenses[0]);

            const tablaExpenses = document.getElementById('expenses-table');
            tablaExpenses.innerHTML='';
            // // console.log(waiter[0]);

            for (const expense in expenses[0]) {
                // console.log(waiters[0][waiter]);
                const fila = `<tr>
                                    <td>${expenses[0][expense].name}</td>
                                    <td>${expenses[0][expense].balance}</td>
                                </tr>`;
                     tablaExpenses.innerHTML += fila;
            }
        }
        getExpenses()

        const mapeoClaves = {
            "init_money": "Monto Inicial",
            "balanceTotal": "Saldo Total",
            "gananciaTotal": "Ganancia Total",
            "facturas": "Facturas",
            "boletas": "Boletas",
            "guia_despacho": "Guia Despacho",
            "fastSells": "Efectivo",
            "noSii": "Trasnbank",
            "debito": "Debito",
            "transferencia": "Transferencia",
            "cheque": "Cheque",
            "banco": "Banco",
            "amipass": "Amipass",
            "junaeb": "Junaeb",
            "multicaja": "Multicaja",
            "convenio_empresa": "Convenio Empresa",
            "edenred": "Edenred",
            "expenses_day": "Gastos Del Día",
            "totalToExpenses": "Total - Gastos"
            // Agrega aquí el resto de tus mapeos
        };

        for (const clave in counters[0]) {
            const valor = counters[0][clave];
            const claveTraducida = mapeoClaves[clave] || clave;

            labels.push(claveTraducida);
            counterData.push(valor);

        }

        var countersCanvas = document.getElementById('countersChart');

        var countersChart = new Chart(countersCanvas, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Contadores',
                    data: counterData,
                    lineTension: 0,
                    backgroundColor: [
                        'rgb(255, 255, 86)',
                        'rgb(255, 150, 132)',
                        'rgb(54, 0, 235)',
                        'rgb(255, 50, 86)',
                        'rgb(150, 0, 86)',
                        'rgb(50, 99, 132)',
                        'rgb(54, 162, 100)',
                        'rgb(255, 180, 86)',
                        'rgb(200, 0, 86)',
                        'rgb(50, 99, 132)',
                        'rgb(54, 162, 80)',
                        'rgb(150, 205, 86)',
                        'rgb(100, 0, 86)',
                        'rgb(54, 162, 80)',
                        'rgb(150, 205, 86)',
                        'rgb(100, 0, 86)',
                        'rgb(50, 99, 132)'
                    ],
                    borderColor: '#fff',
                    pointBackgroundColor: '#007bff',
                }],
            },
            options: {

            }
        })

    });
}

function getSellByHour() {

    __conection({
        url: URL(`/web/getAppSellsByHour?id=${id}`),
        header: credentials(),
        dev: true,
        method: 'GET'

    }, {}, function (request) {
        // console.log("byhour", request);

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

function getTopSells(startDate, endDate) {

    __conection({
        url: URL(`/web/getAppTopSells?id=${id}&startDate=${startDate}&endDate=${endDate}`),
        header: credentials(),
        dev: true,
        method: 'GET'

    }, {}, function (request) {
        // console.log("top sells", request);

        const topSells = [];
        for (const app in request) {
            topSells.push(request[app].original);
        }

        // console.log(topSells[0]);

        const labels = [];
        const data = [];

        for (const clave in topSells[0]) {
            if (topSells[0].totalSales){
                labels.push(topSells[0][clave].product_name);
                data.push(topSells[0][clave].total_quantity);
            }
        }
        labels.pop();
        data.pop();
        // console.log(labels);
        // console.log(data);
        
        var topSellsCanvas = document.getElementById('topSellsChart');

        var topSellsChart = new Chart(topSellsCanvas, {
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
