$(document).ready(function () {
    // _name.text(_store().session.user().get("username")) //obtenemos el nombre del usuario
    var startDate = '2022-12-14 06:00:00';
    var endDate = '2023-12-30 23:59:59';
    requestLoadCounters(startDate, endDate);
    requestLoadTopSells(startDate, endDate);
    requestGetApps();
})

let counterData;
let labels;


function loadSucursalesList() {
    let apps;
  
    requestGetApps();
  
    function requestGetApps() {
      apps = []; // Array para almacenar los datos de id y name
  
      __conection(
        {
          url: URL(`/getApps`),
          header: credentials(),
          dev: true,
          method: 'POST'
        },
        {},
        function (request) {
          console.log("apps", request);
  
          request.forEach((app, index) => {
            apps[index] = {
              id: app.id,
              name: app.name
            };
          });
  
          console.log(apps);
          renderSucursalesList();
        }
      );
    }
  
    function renderSucursalesList() {
      let sucursalesList = document.getElementById('sucursales-list');
      sucursalesList.innerHTML = '';
  
      apps.forEach((app, index) => {
        const fila = `<li class="nav-item">
                        <a class="nav-link" href="/pages/sucursal.html?id=${app.id}">
                          <span data-feather="file-text"></span>
                          ${app.name}
                        </a>
                      </li>`;
        sucursalesList.innerHTML += fila;
      });
    }
  }

function calculate() {
    // let startDateValue = document.getElementById("startDate").value;
    // // Obtener el valor de endDate
    // let endDateValue = document.getElementById("endDate").value;
    // startDate = startDateValue + ' 06:00:00';
    // endDate = endDateValue + ' 23:59:59';

    // requestLoadCounters()
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

function customTooltips(tooltipModel) {
    var tooltipEl = document.getElementById('chartjs-tooltip');
    if (!tooltipEl) {
        tooltipEl = document.createElement('div');
        tooltipEl.id = 'chartjs-tooltip';
        tooltipEl.innerHTML = "<table></table>"
        this._chart.canvas.parentNode.appendChild(tooltipEl);
    }
    // Verificar si hay tooltip activo
    if (tooltipModel.opacity === 0) {
        tooltipEl.style.opacity = 0;
        return;
    }

    // Establecer el texto del tooltip
    if (tooltipModel.body) {
        var dataIndex = tooltipModel.dataPoints[0].index;
        var counter = counterData[dataIndex];
        // console.log(counter);
        var content = [
            'Init Money: ' + counter.init_money,
            'Balance Total: ' + counter.balanceTotal,
            'Ganancia Total: ' + counter.gananciaTotal,
            'Facturas: ' + counter.facturas,
            'Boletas: ' + counter.boletas,
            'Fast Sells: ' + counter.fastSells,
            'No SII: ' + counter.noSii,
            'Debito: ' + counter.debito,
            'Transferencia: ' + counter.transferencia,
            'Cheque: ' + counter.cheque,
            'Banco: ' + counter.banco,
            'Amipass: ' + counter.amipass,
            'Junaeb: ' + counter.junaeb,
            'Multicaja: ' + counter.multicaja,
            'Convenio Empresa: ' + counter.convenio_empresa,
            'Edenred: ' + counter.edenred,
            'Expenses Day: ' + counter.expenses_day,
            'Total To Expenses: ' + counter.totalToExpenses
        ];
        tooltipEl.innerHTML = content.join('<br>');
    }

    // Mostrar el tooltip personalizado
    tooltipEl.style.opacity = 1;
    tooltipEl.style.position = 'absolute';
    tooltipEl.style.left = tooltipModel.caretX + 'px';
    tooltipEl.style.top = tooltipModel.caretY + 'px';
    tooltipEl.style.fontFamily = tooltipModel._bodyFontFamily;
    tooltipEl.style.fontSize = tooltipModel.bodyFontSize + 'px';
    tooltipEl.style.fontStyle = tooltipModel._bodyFontStyle;
    tooltipEl.style.padding = tooltipModel.yPadding + 'px ' + tooltipModel.xPadding + 'px';
    tooltipEl.style.pointerEvents = 'none';
}



function requestLoadCounters(startDate, endDate) {
    __conection({
        url: URL(`/getAppCounters?startDate=${startDate}&endDate=${endDate}`),
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        // console.log("counters", request);

        const gananciasTotales = [];
        labels = [];
        ids = []
        counterData = [];

        for (const app in request) {
            labels.push(app); //Nombre de la app
            gananciasTotales.push(request[app].original.counters.gananciaTotal);
            counterData.push(request[app].original.counters);
        }

        // console.log(gananciasTotales);
        // console.log(labels);
        // console.log(counterData);

        var sucursalesCanvas = document.getElementById('sucursalesChart');

        var sucursalesChart = new Chart(sucursalesCanvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: gananciasTotales,
                    lineTension: 0,
                    backgroundColor: '#0d6efd',
                    borderColor: '#007bff',
                    borderWidth: 4,
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
                },
                tooltips: {
                    enabled: false,
                    mode: 'index',
                    position: 'nearest',
                    custom: customTooltips
                }
            }

        })
        cargarCounterEnTabla(request);
        loadSucursalesList(labels);
    });
}

function cargarCounterEnTabla(request) {
    const tbody = document.getElementById('tabla-counters-tbody');
    tbody.innerHTML = '';
    for (const app in request) {
        const fila = `<tr>
                            <td>${app}</td>
                            <td>${request[app].original.counters.init_money}</td>
                            <td>${request[app].original.counters.totalToExpenses}</td>
                            <td>${request[app].original.counters.balanceTotal}</td>
                            <td>${request[app].original.counters.gananciaTotal}</td>
                        </tr>`;
        tbody.innerHTML += fila;
    }
}

function requestLoadTopSells(startDate, endDate) {
    __conection({
        url: URL(`/getAppTopSells?startDate=${startDate}&endDate=${endDate}`),
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        console.log("Top sells", request);

        const totalSells = [];
        labels = [];

        for (const app in request) {
            labels.push(app); //Nombre de la app
            totalSells.push(request[app].original.totalSales);
        }

        // console.log(totalSells);
        // console.log(labels);


        var topSellsCanvas = document.getElementById('topSellsChart');

        var topSellsChart = new Chart(topSellsCanvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: totalSells,
                    lineTension: 0,
                    backgroundColor: '#0d6efd',
                    borderColor: '#007bff',
                    borderWidth: 4,
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
                },
                tooltips: {
                    enabled: false,
                    mode: 'index',
                    position: 'nearest',
                }
            }

        })
    });
}

function requestGetApps() {
    const apps = []; // Array para almacenar los datos de id y name
  
    __conection(
      {
        url: URL(`/getApps`),
        header: credentials(),
        dev: true,
        method: 'POST'
      },
      {},
      function (request) {
        console.log("apps", request);
  
        request.forEach((app, index) => {
          apps[index] = {
            id: app.id,
            name: app.name
          };
        });
  
        console.log(apps);
      }
    );
  }