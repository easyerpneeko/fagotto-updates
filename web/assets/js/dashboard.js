// ========== FUNCIONES DE INDICADOR DE FILTRO ==========
function mostrarFiltroActivo(fecha, tipo = 'fecha') {
    const indicador = document.getElementById('filtro-activo');
    const textoFiltro = document.getElementById('texto-filtro');
    
    if (!indicador || !textoFiltro) return;
    
    let mensaje = '';
    
    if (tipo === 'fecha') {
        // Convertir fecha YYYY-MM-DD a formato legible
        const fechaObj = new Date(fecha + 'T00:00:00');
        const opciones = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            weekday: 'long'
        };
        const fechaFormateada = fechaObj.toLocaleDateString('es-CL', opciones);
        mensaje = `Filtrando datos del ${fechaFormateada}`;
    } else if (tipo === 'hoy') {
        mensaje = 'Mostrando datos de hoy';
    } else if (tipo === 'semana') {
        mensaje = 'Mostrando datos de esta semana';
    } else if (tipo === 'mes') {
        mensaje = 'Mostrando datos de este mes';
    }
    
    textoFiltro.textContent = mensaje;
    indicador.style.display = 'block';
    
    // Agregar animación de entrada
    indicador.style.opacity = '0';
    indicador.style.transform = 'translateY(-10px)';
    
    setTimeout(() => {
        indicador.style.transition = 'all 0.3s ease';
        indicador.style.opacity = '1';
        indicador.style.transform = 'translateY(0)';
    }, 100);
    
    console.log('Mostrando filtro activo:', mensaje);
}

function ocultarFiltroActivo() {
    const indicador = document.getElementById('filtro-activo');
    if (indicador) {
        indicador.style.display = 'none';
    }
}

// ========== FUNCIONES ORIGINALES ==========
$(document).ready(function () {
    // _name.text(_store().session.user().get("username")) //obtenemos el nombre del usuario
    getThisDay();

    
    // iniciarConteo();

})

let counterData;
let labels;

const TIEMPO_RECARGA = 420000; // 7 minutos en milisegundos
const TIEMPO_ACTUALIZACION = 1000; // 1 segundo

let tiempoRestante = TIEMPO_RECARGA;
let intervalo;

// Función para actualizar la cuenta regresiva
function actualizarConteo() {
  tiempoRestante -= TIEMPO_ACTUALIZACION;
  const minutos = Math.floor(tiempoRestante / 60000);
  const segundos = Math.floor((tiempoRestante % 60000) / 1000);
  const divConteo = document.getElementById("divConteo");
  divConteo.textContent = `La página se recargará automáticamente en: ${minutos}m ${segundos}s`;

  // Recargar la página cuando el tiempo llegue a 0
  if (tiempoRestante <= 0) {
    clearInterval(intervalo);
    location.reload();  // Recarga la página
  }
}

// Inicializar el contador
intervalo = setInterval(actualizarConteo, TIEMPO_ACTUALIZACION);

// // Agregar el script al HTML
// window.onload = function() {
//   iniciarConteo();
// };


async function getData(startDate,endDate){
    console.log('=== INICIANDO getData ===');
    console.log('startDate:', startDate);
    console.log('endDate:', endDate);
    
    try {
        activateLoader();
        console.log('Loader activado');
        
        console.log('Llamando a requestLoadCounters...');
        await requestLoadCounters(startDate, endDate);
        console.log('requestLoadCounters completado');
        
        console.log('Llamando a loadSucursalesList...');
        await loadSucursalesList();
        console.log('loadSucursalesList completado');
        
        console.log('Llamando a getFacturacionData...');
        await getFacturacionData(startDate, endDate);
        console.log('getFacturacionData completado');
        
        console.log('Llamando a getProducts...');
        getProducts();
        console.log('getProducts iniciado');
        
        desactivateLoader();
        console.log('Loader desactivado - getData completado exitosamente');
    } catch (error) {
        console.error('Error en getData:', error);
        desactivateLoader();
        alert('Error al cargar los datos: ' + error.message);
    }
}
function getByDate(){
    console.log('=== INICIANDO getByDate ===');
    
    const DateInput = document.getElementById('date');
    const DateValue = DateInput.value;
    
    console.log('Input de fecha encontrado:', DateInput);
    console.log('Valor de fecha:', DateValue);
    
    if (!DateValue) {
        console.error('No se ha seleccionado una fecha');
        alert('Por favor selecciona una fecha antes de buscar');
        return;
    }
    
    // Mostrar indicador de filtro personalizado
    mostrarFiltroActivo(DateValue, 'fecha');
    
    const startDate = DateValue;
    const endDate = DateValue + ' 23:59:59';
    
    console.log('Fecha inicio:', startDate);
    console.log('Fecha fin:', endDate);
    console.log('Llamando a getData...');
    
    try {
        getData(startDate, endDate);
        console.log('getData ejecutado correctamente');
    } catch (error) {
        console.error('Error en getData:', error);
        alert('Error al cargar los datos: ' + error.message);
        ocultarFiltroActivo(); // Ocultar indicador si hay error
    }
}
async function loadSucursalesList() {
    let apps;
  
    requestGetApps();
  
    function requestGetApps() {
      apps = []; // Array para almacenar los datos de id y name
  
      __conection(
        {
          url: generarURLApi(`/getApps`),
          header: credentials(),
          dev: true,
          method: 'POST'
        },
        {},
        function (request) {
  
          request.forEach((app, index) => {
            apps[index] = {
              id: app.id,
              name: app.name
            };
          });
  
          renderSucursalesList();
        }
      );
    }
  
    function renderSucursalesList() {
      let sucursalesList = document.getElementById('sucursales-list');
      let sucursalesListMobile = document.getElementById('sucursales-list-mobile');
      
      // Clear both lists
      sucursalesList.innerHTML = '';
      if (sucursalesListMobile) {
        sucursalesListMobile.innerHTML = '';
      }
  
      apps.forEach((app, index) => {
        // Desktop version
        const fila = `<li class="nav-item">
                        <a class="nav-link active" href="/pages/sucursal.html?id=${app.id}">
                          <span data-feather="file-text"></span>
                          ${app.name}
                        </a>
                      </li>`;
        sucursalesList.innerHTML += fila;

        // Mobile version
        if (sucursalesListMobile) {
          const filaMobile = `<a class="nav-link-sub" href="/pages/sucursal.html?id=${app.id}">
                                <i class="fas fa-store"></i>
                                ${app.name}
                              </a>`;
          sucursalesListMobile.innerHTML += filaMobile;
        }
      });
    }
}

function getThisDay() {
    mostrarFiltroActivo('', 'hoy');
    
    // TEMPORAL: Usar ayer en lugar de hoy para evitar error 500
    var yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    var startDate = yesterday.toISOString().split('T')[0];
    var endDate = startDate + ' 23:59:59';

    getData(startDate,endDate);
}

function getThisWeek() {
    mostrarFiltroActivo('', 'semana');
    
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
    console.log(lastDayOfWeekFormatted);
    getData(firstDayOfWeekFormatted,lastDayOfWeekFormatted);
}

function getThisMonth() {
  // Mostrar indicador de filtro activo para el mes
  mostrarFiltroActivo('', 'mes');
  
  // Mostrar el mensaje solo para getThisMonth
  const loaderMessage = document.getElementById('loaderMessage');
  loaderMessage.classList.remove('d-none'); // Muestra el mensaje

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
  
    if (typeof counter !== 'undefined') {
      var content = [];
  
      // if (typeof counter.init_money !== 'undefined') {
      //   content.push('Dinero inicial: ' + counter.init_money);
      // }

      if (typeof counter.facturas !== 'undefined') {
        content.push('Facturas: ' + counter.facturas);
      }
      if (typeof counter.boleta !== 'undefined') {
        content.push('Boletas: ' + counter.boleta);
      }
      if (typeof counter.fastSells !== 'undefined') {
        content.push('Ventas rápidas: ' + counter.fastSells);
      }
      if (typeof counter.efectivo !== 'undefined') {
        content.push('Efectivo: ' + counter.efectivo);
      }
      // if (typeof counter.boleta_local !== 'undefined') {
      //   content.push('Boleta Local: ' + counter.boleta_local);
      // }
      if (typeof counter.debito !== 'undefined') {
        content.push('Débito: ' + counter.debito);
      }
      if (typeof counter.credito !== 'undefined') {
        content.push('Crédito: ' + counter.credito);
      }
      if (typeof counter.transferencia !== 'undefined') {
        content.push('Transferencia: ' + counter.transferencia);
      }
      if (typeof counter.cheque !== 'undefined') {
        content.push('Cheque: ' + counter.cheque);
      }
      if (typeof counter.banco !== 'undefined') {
        content.push('Banco: ' + counter.banco);
      }
      if (typeof counter.amipass !== 'undefined') {
        content.push('Amipass: ' + counter.amipass);
      }
      if (typeof counter.rappi !== 'undefined') {
        content.push('Rappi: '+ counter.rappi);
      }
      if (typeof counter.uber !== 'undefined') {
        content.push('Uber: '+ counter.uber);
      }
      if (typeof counter.multicaja !== 'undefined') {
        content.push('Multicaja: ' + counter.multicaja);
      }
      if (typeof counter.convenio_empresa !== 'undefined') {
        content.push('Convenio Empresa: ' + counter.convenio_empresa);
      }
      if (typeof counter.edenred !== 'undefined') {
        content.push('Edenred: ' + counter.edenred);
      }
      if (typeof counter.sodexo !== 'undefined') {
        content.push('Sodexo: '+ counter.sodexo);
      }
      if (typeof counter.expenses_day !== 'undefined') {
        content.push('Gastos del día: ' + counter.expenses_day);
      }
      if (typeof counter.gananciaTotal !== 'undefined') {
        content.push('Ganancia total: ' + counter.gananciaTotal);
      }
      if (typeof counter.balanceTotal !== 'undefined') {
        content.push('Balance total: ' + counter.balanceTotal);
      }
      // if (typeof counter.totalToExpenses !== 'undefined') {
      //   content.push('Total para gastos: ' + counter.totalToExpenses);
      // }
  
      if (content.length > 0) {
        tooltipEl.innerHTML = content.join('<br>');
      } else {
        tooltipEl.innerHTML = 'No se encontró información para este punto';
      }
    } else {
      tooltipEl.innerHTML = 'No se encontró información para este punto';
    }
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
    $('.loading-chart').show();
    $.post("getAppCounters", {
        startDate: startDate,
        endDate: endDate
    }, function(data) {
        console.log(data);
        fillChartCounters(data);
        $('.loading-chart').hide();
    }, 'json').fail(function(xhr, status, error) {
        console.error('Error en getAppCounters:', error);
        console.error('Respuesta:', xhr.responseText);
        $('.loading-chart').hide();
        // Mostrar mensaje amigable al usuario
        alert('Error temporal en el servidor. Contacta al administrador.');
    });

function cargarCounterEnTabla(request) {
    const tbody = document.getElementById('tabla-counters-tbody');
    tbody.innerHTML = '';
    let totalSucursales = 0;
    let iva = 0.19;

    for (const app in request) {
      let appIdName = app.split(',',2);

      if(appIdName[0] != 79 && appIdName[0] != 86 && appIdName[0] != 98 && appIdName[0] != 109 && appIdName[0] != 106){    // quiero omitir 109 y 106 


        const fila = `<tr>
                          <td>${appIdName[1]}</td>
                          <td>${formatearMontoChile(request[app].original.counters.expenses_day.toFixed(0))}</td>
                          <td>${formatearMontoChile((request[app].original.counters.balanceTotal / (1 + iva)).toFixed(0))}</td>
                          <td>${formatearMontoChile(request[app].original.counters.balanceTotal.toFixed(0))}</td>
                      </tr>`;

        tbody.innerHTML += fila;
        totalSucursales += request[app].original.counters.balanceTotal;
      }
        
    }
    const ultimaFila = `<tr>
                            <td colspan="2"><strong>Saldo Total de Sucursales: </strong></td>
                            <td><strong>${formatearMontoChile((totalSucursales / (1 + iva)).toFixed(0))}</strong></td>
                            <td><strong>${formatearMontoChile(totalSucursales)}</strong></td>                            
                        </tr>`;
    tbody.innerHTML += ultimaFila;
    // ________________________________________________________________________________

    const tbodyF = document.getElementById('tabla-counters-fagotto-tbody');
    tbodyF.innerHTML = '';
    let totalSucursalesF = 0;
    for (const app in request) {
      let appIdName = app.split(',',2);

      if(appIdName[0] === '86' || appIdName[0] === '106' || appIdName[0] === '109' || appIdName[0] === '98'){
        
        const filaF = `<tr>
                          <td>${appIdName[1]}</td>
                          <td>${formatearMontoChile(request[app].original.counters.expenses_day.toFixed(0))}</td>
                          <td>${formatearMontoChile((request[app].original.counters.balanceTotal / (1 + iva)).toFixed(0))}</td>
                          <td>${formatearMontoChile(request[app].original.counters.balanceTotal.toFixed(0))}</td>
                      </tr>`;
        
        tbodyF.innerHTML += filaF;
        totalSucursalesF += request[app].original.counters.balanceTotal;
      }
    }
    const ultimaFilaF = `<tr>
                            <td colspan="2"><strong>Saldo Total de Sucursales: </strong></td>
                            <td><strong>${formatearMontoChile((totalSucursalesF / (1 + iva)).toFixed(0))}</strong></td>
                            <td><strong>${formatearMontoChile(totalSucursalesF)}</strong></td>                            
                        </tr>`;
    tbodyF.innerHTML += ultimaFilaF;
    
    // ________________________________________________________________________________
    // const tbodyFac = document.getElementById('tabla-counters-facturacion-tbody');
    // tbodyFac.innerHTML = '';
    // let totalSucursalesFac = 0;
    // for (const app in request) {
    //   let appIdName = app.split(',',2);

    //   if(appIdName[0] === '79'){
    //     const filaFac = `<tr>
    //                         <td>${appIdName[1]}</td>
    //                         <td>$${(request[app].original.counters.expenses_day).toFixed(0)}</td>
    //                         <td>$${(request[app].original.counters.balanceTotal).toFixed(0)}</td>
                            
    //                     </tr>`;
    //     tbodyFac.innerHTML += filaFac;
    //     totalSucursalesFac += request[app].original.counters.balanceTotal;
    //   }
    // }
    // const ultimaFilaFac = `<tr>
    //                         <td colspan="2">Saldo Total de Sucursales: </td>
    //                         <td>$${totalSucursalesFac}</td>                            
    //                     </tr>`;
    // tbodyFac.innerHTML += ultimaFilaFac;

}

function requestLoadTopSells(startDate, endDate) {
    __conection({
        url: generarURLApi(`/getAppTopSells?startDate=${startDate}&endDate=${endDate}`),
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        // console.log("Top sells", request);

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

function getFacturacionData(startDate, endDate) {
  let facturas;

  requestGetFacturacion();

  async function requestGetFacturacion() {
      await __conection({
          url: generarURLApi(`/getAppFacturacion?startDate=${startDate}&endDate=${endDate}`),
          header: credentials(),
          dev: true,
          method: 'GET'
      }, {}, function (request) {
          console.log('facturacion', request);

          facturas = request; // Asignar la respuesta a la variable facturas

          renderTablaFacturacion();
      });
  }

  function renderTablaFacturacion() {
      let tablaFacturacion = document.getElementById('tabla-counters-facturacion-tbody');
      
      // Limpiar la tabla existente
      tablaFacturacion.innerHTML = '';

      // Añadir los encabezados
      tablaFacturacion.innerHTML += `
          <thead>
              <tr>
                  <th scope="col">Razon Social</th>
                  <th scope="col">RUT</th>
                  <th scope="col">Monto</th>
                  <th scope="col">PDF</th>
              </tr>
          </thead>
      `;

      // Función para formatear el monto
      function formatearMontoChile(monto) {
          return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(monto);
      }

      // Variable para almacenar la sumatoria de todos los montos totales
      let montoTotalFacturado = 0;

      // Iterar sobre los datos de facturas y añadir filas a la tabla
      facturas.forEach((factura) => {
          montoTotalFacturado += parseFloat(factura.MntTotal); // Sumar el monto total al acumulador

          const fila = `<tr>
                          <td>${factura.RznSocRecep}</td>
                          <td>${factura.RUTRecep}</td>
                          <td>${formatearMontoChile(factura.MntTotal)}</td>
                          <td><a href="${factura.pdf}" target='_blank'>Descargar</a></td>
                        </tr>`;
          tablaFacturacion.innerHTML += fila;
      });

      // Añadir la fila de total facturado al final de la tabla
      const filaTotal = `<tr>
                            <td colspan="2"><strong>Total Facturado</strong></td>
                            <td colspan="2"><strong>${formatearMontoChile(montoTotalFacturado)}</strong></td>
                         </tr>`;
      tablaFacturacion.innerHTML += filaTotal;
  }
}

async function getProducts() {

  var url = "";

  url = generarURLApi(`/local/products/index`);

  await __conection({
      url: url,
      header: credentials(),
      dev: true,
      method: 'GET'

  }, {}, function (request) {
      console.log("Apps products:", request);

      const tablasContainer = document.getElementById('tablas-container-salsas');
      tablasContainer.innerHTML = "";
      let i = 1;
      let products = request;
      if (products.length > 0) {
          let fila = `<div class="table-responsive">
                              <table class="table table-striped table-sm">
                                      <thead>
                                          <tr>
                                              <th scope="col">#</th>
                                              <th scope="col">Nombre</th>
                                              <th scope="col">Bolsas</th>
                                              <th scope="col">Vasos</th>
                                          </tr>
                                      </thead>
                                      <tbody> `;

          for (const index in products) {
              if (products[index].category == 2) {

                  fila += `<tr>
                              <td><b>${i}</b></td>
                              <td><b>${products[index].name}</b></td>
                              <td><b>${products[index].stock}</b></td>
                              <td><b>${products[index].vasos * products[index].stock}</b></td>
                          </tr>`;
                  i++;
              }
          }
          fila += `</tbody>
                      </table>
                  </div>`

          tablasContainer.innerHTML += fila;

      } else {
          const tablasContainer = document.getElementById('tablas-container');
          tablasContainer.innerHTML = "NO HAY PRODUCTOS";
      }
  });


}

