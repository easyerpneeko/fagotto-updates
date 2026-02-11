$(document).ready(function () {
    // _name.text(_store().session.user().get("username")) //obtenemos el nombre del usuario
    
    // ========== TEMPORIZADOR REAL DE CARGA ==========
    window.loadStartTime = performance.now();
    
    // Mensajes dinámicos del loader
    const loaderMessages = [
        { time: 0, text: "🏃‍♂️ Corriendo al servidor...", emoji: "🏃‍♂️" },
        { time: 1000, text: "🔍 Buscando tus datos...", emoji: "🔍" },
        { time: 2000, text: "📦 Empaquetando información...", emoji: "📦" },
        { time: 3000, text: "🚀 Trayendo los datos...", emoji: "🚀" },
        { time: 4000, text: "✨ Casi listo...", emoji: "✨" }
    ];
    
    let messageIndex = 0;
    window.loaderMessageInterval = setInterval(function() {
        const elapsed = performance.now() - window.loadStartTime;
        const statusElement = document.getElementById('loader-status');
        
        if (statusElement && messageIndex < loaderMessages.length) {
            if (elapsed >= loaderMessages[messageIndex].time) {
                statusElement.innerHTML = `<span style="display: inline-block; animation: bounceIn 0.5s;">${loaderMessages[messageIndex].emoji}</span> ${loaderMessages[messageIndex].text}`;
                messageIndex++;
            }
        }
    }, 100);
    
    window.loaderTimerInterval = setInterval(function() {
        const elapsed = ((performance.now() - window.loadStartTime) / 1000).toFixed(1);
        const timerElement = document.getElementById('loader-timer');
        if (timerElement) {
            timerElement.textContent = elapsed + 's';
            // Cambiar color según el tiempo
            if (elapsed < 3) {
                timerElement.style.color = '#10b981'; // Verde
            } else if (elapsed < 5) {
                timerElement.style.color = '#f59e0b'; // Amarillo
            } else {
                timerElement.style.color = '#ef4444'; // Rojo
            }
        }
    }, 100); // Actualizar cada 100ms para que sea más fluido
    
    // Inicializar campos de fecha con la fecha actual
    const today = getNowDate();
    document.getElementById('startDate').value = today;
    document.getElementById('endDate').value = today;
    
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
    activateLoader();
    await requestLoadCounters(startDate, endDate);
    await loadSucursalesList();
    await getFacturacionData(startDate, endDate);
    
    // Cargar mapa de calor global de salsas
    await loadGlobalSauceHeatmap();

    getProducts();
    desactivateLoader();
}
function getByDate(){
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    
    const startDateValue = startDateInput.value;
    const endDateValue = endDateInput.value;
    
    // Validar que ambas fechas estén seleccionadas
    if (!startDateValue || !endDateValue) {
        alert('Por favor selecciona ambas fechas (Desde y Hasta)');
        return;
    }
    
    // Validar que la fecha de inicio no sea mayor que la fecha final
    if (new Date(startDateValue) > new Date(endDateValue)) {
        alert('La fecha de inicio no puede ser mayor que la fecha final');
        return;
    }
    
    const startDate = startDateValue;
    const endDate = endDateValue + ' 23:59:59';

    // Quitar clase activa de todos los botones cuando se usa filtro personalizado
    updateActiveFilter('custom');

    getData(startDate, endDate);
}

function updateActiveFilter(activeFilter) {
    // Remover clase active de todos los botones
    const filterButtons = document.querySelectorAll('.btn-filter');
    filterButtons.forEach(button => {
        button.classList.remove('active');
    });
    
    // Agregar clase active al botón correspondiente
    if (activeFilter !== 'custom') {
        const activeButton = document.querySelector(`[data-period="${activeFilter}"]`);
        if (activeButton) {
            activeButton.classList.add('active');
        }
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
      
      // 🔍 DEBUG: Ver nombres de las sucursales
      console.log('🏪 Nombres de sucursales:', apps.map(app => app.name));
  
      // 🔄 ORDENAR: Primero Fagotto, luego Trai i Pasti
      const appsOrdenadas = [...apps].sort((a, b) => {
        const aEsFagotto = a.name.toLowerCase().includes('fagotto');
        const bEsFagotto = b.name.toLowerCase().includes('fagotto');
        const aEsTraiPasti = a.name.toLowerCase().includes('trai i pasti');
        const bEsTraiPasti = b.name.toLowerCase().includes('trai i pasti');
        
        // Si ambos son Fagotto o ambos son Trai i Pasti, ordenar alfabéticamente
        if (aEsFagotto && bEsFagotto) {
          return a.name.localeCompare(b.name);
        }
        if (aEsTraiPasti && bEsTraiPasti) {
          return a.name.localeCompare(b.name);
        }
        
        // Fagotto primero
        if (aEsFagotto && !bEsFagotto) return -1;
        if (!aEsFagotto && bEsFagotto) return 1;
        
        // Trai i Pasti después
        if (aEsTraiPasti && !bEsTraiPasti) return 1;
        if (!aEsTraiPasti && bEsTraiPasti) return -1;
        
        // Por defecto, orden alfabético
        return a.name.localeCompare(b.name);
      });

      appsOrdenadas.forEach((app, index) => {
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
    var startDate = getNowDate();
    var endDate = getNowDate()+' 23:59:59';
    
    // Actualizar los campos de fecha
    document.getElementById('startDate').value = getNowDate();
    document.getElementById('endDate').value = getNowDate();
    
    // Actualizar botones activos
    updateActiveFilter('day');

    getData(startDate,endDate);
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
    
    // Actualizar los campos de fecha
    document.getElementById('startDate').value = firstDayOfWeekFormatted;
    document.getElementById('endDate').value = lastDayOfWeekFormatted;
    
    // Actualizar botones activos
    updateActiveFilter('week');
    
    console.log(firstDayOfWeekFormatted);
    console.log(lastDayOfWeekFormatted);
    getData(firstDayOfWeekFormatted,lastDayOfWeekFormatted);
}

function getThisMonth() {
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

  // Actualizar los campos de fecha
  document.getElementById('startDate').value = firstDayOfMonthFormatted;
  document.getElementById('endDate').value = lastDayOfMonthFormatted;
  
  // Actualizar botones activos
  updateActiveFilter('month');

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

async function requestLoadCounters(startDate, endDate) {
    await __conection({
        url: generarURLApi(`/getAppCounters?startDate=${startDate}&endDate=${endDate}`),
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, async function (request) {
        // console.log(startDate);
        // console.log(endDate);
        console.log("counters", request);

        // 🎫 CALCULAR TICKETS DE DELIVERY ANTES DE PROCESAR LOS DATOS
        if (window.calcularTicketsDelivery) {
            await window.calcularTicketsDelivery(request, startDate, endDate);
        } else {
            console.warn('⚠️ calcularTicketsDelivery no está disponible');
        }

        const gananciasTotales = [];
        labels = [];
        ids = []
        counterData = [];

        for (const app in request) {
            // Extraer solo el nombre sin el ID
            const appIdName = app.split(',', 2);
            const appName = appIdName[1] || app; // Si no hay coma, usa el app completo
            
            // � DEBUG: Ver qué nombres llegan
            console.log('🏪 App detectada:', appName);
            
            // �🚫 FILTRAR: Excluir "Facturación Fagotto" del gráfico
            // 🚫 Solo filtrar Facturación (no las sucursales)
            if (appName === 'Facturacion Fagotto' ||
                appName.toLowerCase() === 'facturacion fagotto' ||
                appName.toLowerCase().includes('billing')) {
                console.log('❌ FILTRADO:', appName);
                continue; // Saltar esta iteración
            }
            
            // 🏢 Separar por tipo de negocio y agregar al final los Trai i Pasti
            if (appName.toLowerCase().includes('trai i pasti')) {
                console.log('🍝 TRAI I PASTI (va al final):', appName);
                // Los agregamos después
            } else {
                console.log('🍕 FAGOTTO (va primero):', appName);
                labels.push(appName); // Solo el nombre
                gananciasTotales.push(request[app].original.counters.balanceTotal);
                counterData.push(request[app].original.counters);
            }
        }

        // Segundo bucle: Agregar Trai i Pasti al FINAL
        for (const app in request) {
            const appIdName = app.split(',', 2);
            const appName = appIdName[1] || app;
            
            // Solo agregar Trai i Pasti
            if (appName.toLowerCase().includes('trai i pasti') && 
                !appName.includes('Facturacion') &&
                !appName.includes('billing')) {
                console.log('🍝 Agregando al final:', appName);
                labels.push(appName);
                gananciasTotales.push(request[app].original.counters.balanceTotal);
                counterData.push(request[app].original.counters);
            }
        }

        // Generar colores degradados bonitos para cada barra
        const colors = generateGradientColors(labels.length);

        // console.log(gananciasTotales);
        // console.log(labels);
        // console.log(counterData);

        var sucursalesCanvas = document.getElementById('sucursalesChart');
        
        // Destruir gráfico anterior si existe
        if (window.sucursalesChartInstance) {
            window.sucursalesChartInstance.destroy();
        }

        window.sucursalesChartInstance = new Chart(sucursalesCanvas, {
            type: 'horizontalBar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Ventas Totales',
                    data: gananciasTotales,
                    backgroundColor: colors.backgrounds,
                    borderColor: colors.borders,
                    borderWidth: 2,
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                return '$' + value.toLocaleString('es-CL');
                            },
                            fontColor: '#64748b',
                            fontSize: 11
                        },
                        gridLines: {
                            color: 'rgba(226, 232, 240, 0.5)',
                            drawBorder: false,
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            fontColor: '#1e293b',
                            fontSize: 12,
                            fontStyle: '600'
                        },
                        gridLines: {
                            display: false
                        },
                        barPercentage: 0.7,
                        categoryPercentage: 0.8,
                    }]
                },
                legend: {
                    display: false
                },
                tooltips: {
                    enabled: true,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFontSize: 13,
                    titleFontColor: '#fff',
                    bodyFontColor: '#fff',
                    bodyFontSize: 12,
                    displayColors: false,
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    xPadding: 12,
                    yPadding: 12,
                    callbacks: {
                        label: function(tooltipItem) {
                            return 'Ventas: $' + tooltipItem.value.toLocaleString('es-CL');
                        }
                    }
                },
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                }
            }
        });
        cargarCounterEnTabla(request, startDate, endDate);
        loadSucursalesList(labels);
        
        // Actualizar KPIs usando los datos originales
        console.log('=== LLAMANDO ACTUALIZACION KPIs ===');
        console.log('Window dashboardKPIs:', window.dashboardKPIs);
        if (window.dashboardKPIs) {
            console.log('Ejecutando updateFromCounterData...');
            window.dashboardKPIs.updateFromCounterData(request);
        } else {
            console.error('dashboardKPIs no está disponible');
            // Intentar de nuevo después de un pequeño delay
            setTimeout(() => {
                if (window.dashboardKPIs) {
                    console.log('Reintentando actualización KPIs...');
                    window.dashboardKPIs.updateFromCounterData(request);
                }
            }, 500);
        }
    });
}

async function cargarCounterEnTabla(request, startDate, endDate) {
    const tbody = document.getElementById('tabla-counters-tbody');
    tbody.innerHTML = '';
    let totalVentaBruta = 0;
    let totalVentaDelivery = 0;
    let totalTransacciones = 0;
    let totalTicketsUber = 0;
    let totalTicketsRappi = 0;
    let totalTicketsPedidosYa = 0;

    // 🎯 Usar la fecha FINAL del rango seleccionado (no hoy)
    // Si filtras semana 2-8 Feb, usa el 8 (no el 9)
    const fechaReferencia = new Date(endDate || new Date());
    const mes = fechaReferencia.getMonth() + 1;
    const anio = fechaReferencia.getFullYear();
    const dia = fechaReferencia.getDate();
    
    console.log(`📅 Buscando metas para: ${dia}/${mes}/${anio} (fecha del rango: ${endDate})`);
    const metasPorLocal = await cargarMetasDiarias(mes, anio, dia);

    // IDs de Franquicias que deben aparecer en la primera tabla
    const idsFranquicias = [114, 95, 77, 108, 117, 113, 96, 102, 98];
    
    // Array temporal para ordenar alfabéticamente las franquicias
    const franquiciasData = [];

    for (const app in request) {
        let appIdName = app.split(',',2);
        const appId = parseInt(appIdName[0]);

        // Filtro para incluir solo las franquicias
        if(idsFranquicias.includes(appId)){ 

            const counters = request[app].original.counters;
            const localId = parseInt(appIdName[0]);
            
            // Calcular Venta Bruta (total de todas las ventas)
            const ventaBruta = parseFloat(counters.balanceTotal) || 0;
            
            // Calcular TC (ticket promedio) - necesitamos las órdenes totales
            const ordenes = parseInt(counters.orders) || 1;
            const ticketPromedio = ordenes > 0 ? ventaBruta / ordenes : 0;
            
            // Calcular Venta Delivery (suma de uber + rappi + pedidos_ya)
            const uber = parseFloat(counters.uber) || 0;
            const rappi = parseFloat(counters.rappi) || 0;
            const pedidosYa = parseFloat(counters.pedidos_ya) || 0;
            const ventaDelivery = uber + rappi + pedidosYa;
            
            // Obtener conteo de tickets por plataforma (del backend)
            const ticketsUber = parseInt(counters.uber_count) || 0;
            const ticketsRappi = parseInt(counters.rappi_count) || 0;
            const ticketsPedidosYa = parseInt(counters.pedidos_ya_count) || 0;
            const totalTicketsDelivery = ticketsUber + ticketsRappi + ticketsPedidosYa;
            
            // Crear texto de desglose de tickets
            let ticketsDesglose = '';
            if (totalTicketsDelivery > 0) {
                const partes = [];
                if (ticketsUber > 0) partes.push(`${ticketsUber} Uber 🚗`);
                if (ticketsRappi > 0) partes.push(`${ticketsRappi} Rappi 📱`);
                if (ticketsPedidosYa > 0) partes.push(`${ticketsPedidosYa} Pedidos Ya 🛵`);
                ticketsDesglose = `<div class="fw-bold text-primary">${totalTicketsDelivery} tickets</div>
                                   <div style="font-size: 0.75rem; color: #64748b;">${partes.join(' | ')}</div>`;
            } else {
                ticketsDesglose = '<span class="text-muted">0</span>';
            }
            
            // Calcular porcentaje delivery
            const porcentajeDelivery = ventaBruta > 0 ? ((ventaDelivery / ventaBruta) * 100) : 0;

            // 🎯 Obtener meta del local y calcular estado
            const metaLocal = metasPorLocal[localId];
            let metaHTML = '<span class="text-muted">Sin meta</span>';
            let tcMetaHTML = '<span class="text-muted">-</span>';
            let tcRealVsMetaHTML = '<span class="text-muted">-</span>';
            
            if (metaLocal) {
                const metaValor = parseInt(metaLocal.meta_diaria) || 0;
                const diferencia = ventaBruta - metaValor;
                const cumplida = diferencia >= 0;
                
                // T/C de la meta (número de tickets objetivo)
                const tcMeta = parseInt(metaLocal.ticket_promedio) || 0;
                if (tcMeta > 0) {
                    tcMetaHTML = `<span class="fw-bold text-primary">${tcMeta.toLocaleString('es-CL')}</span>`;
                    
                    // Comparar Tickets Reales vs Tickets Meta (no precio promedio)
                    const diferenciaTc = ordenes - tcMeta;
                    const cumpleTcMeta = diferenciaTc >= 0;
                    
                    if (cumpleTcMeta) {
                        tcRealVsMetaHTML = `<div>
                                                <div class="fw-bold text-success">✅ Meta superada</div>
                                                <div style="font-size: 0.85rem; color: #10b981;">
                                                    Meta T/C: ${tcMeta.toLocaleString('es-CL')}
                                                    <span class="badge bg-success">+${Math.abs(diferenciaTc).toFixed(0)}</span>
                                                </div>
                                            </div>`;
                    } else {
                        tcRealVsMetaHTML = `<div>
                                                <div style="font-size: 1.3rem; font-weight: bold; color: #f59e0b;">Faltan ${Math.abs(diferenciaTc).toFixed(0)}</div>
                                                <div style="font-size: 0.85rem; color: #9ca3af;">
                                                    Meta T/C: ${tcMeta.toLocaleString('es-CL')}
                                                </div>
                                            </div>`;
                    }
                }
                
                if (cumplida) {
                    metaHTML = `<div>
                                    <div class="fw-bold text-success">✅ Meta superada</div>
                                    <div style="font-size: 0.85rem; color: #10b981;">
                                        Meta: ${formatearMontoChile(metaValor)} 
                                        <span class="badge bg-success">+${formatearMontoChile(Math.abs(diferencia))}</span>
                                    </div>
                                </div>`;
                } else {
                    metaHTML = `<div>
                                    <div style="font-size: 1.3rem; font-weight: bold; color: #f59e0b;">Faltan ${formatearMontoChile(Math.abs(diferencia))}</div>
                                    <div style="font-size: 0.85rem; color: #9ca3af;">
                                        Meta: ${formatearMontoChile(metaValor)}
                                    </div>
                                </div>`;
                }
            }

            const fila = `<tr>
                              <td>${appIdName[1]}</td>
                              <td>${formatearMontoChile(ventaBruta.toFixed(0))}</td>
                              <td>${metaHTML}</td>
                              <td><span class="fw-bold text-info">${ordenes.toLocaleString('es-CL')}</span></td>
                              <td>${tcMetaHTML}</td>
                              <td>${tcRealVsMetaHTML}</td>
                              <td>${formatearMontoChile(ventaDelivery.toFixed(0))}</td>
                              <td>${ticketsDesglose}</td>
                              <td><strong>${porcentajeDelivery.toFixed(1)}%</strong></td>
                          </tr>`;

            // Guardar en array temporal para ordenar alfabéticamente
            franquiciasData.push({
                nombre: appIdName[1],
                fila: fila,
                ventaBruta: ventaBruta,
                ventaDelivery: ventaDelivery,
                ordenes: ordenes,
                ticketsUber: ticketsUber,
                ticketsRappi: ticketsRappi,
                ticketsPedidosYa: ticketsPedidosYa
            });
            
            totalVentaBruta += ventaBruta;
            totalVentaDelivery += ventaDelivery;
            totalTransacciones += ordenes;
            totalTicketsUber += ticketsUber;
            totalTicketsRappi += ticketsRappi;
            totalTicketsPedidosYa += ticketsPedidosYa;
        }
        
    }
    
    // Ordenar franquicias alfabéticamente por nombre
    franquiciasData.sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'));
    
    // Agregar filas ordenadas al tbody
    franquiciasData.forEach(item => {
        tbody.innerHTML += item.fila;
    });
    
    // Calcular totales generales
    const porcentajeTotalDelivery = totalVentaBruta > 0 ? ((totalVentaDelivery / totalVentaBruta) * 100) : 0;
    const totalTicketsDeliveryGeneral = totalTicketsUber + totalTicketsRappi + totalTicketsPedidosYa;
    
    // Crear desglose total de tickets
    const partesTotales = [];
    if (totalTicketsUber > 0) partesTotales.push(`${totalTicketsUber} Uber 🚗`);
    if (totalTicketsRappi > 0) partesTotales.push(`${totalTicketsRappi} Rappi 📱`);
    if (totalTicketsPedidosYa > 0) partesTotales.push(`${totalTicketsPedidosYa} Pedidos Ya 🛵`);
    const ticketsDesgloseTotales = `<div class="fw-bold text-warning">${totalTicketsDeliveryGeneral} tickets</div>
                                    <div style="font-size: 0.75rem;">${partesTotales.join(' | ')}</div>`;
    
    const ultimaFila = `<tr class="table-dark">
                            <td><strong>🤝 TOTALES FRANQUICIAS</strong></td>
                            <td><strong>${formatearMontoChile(totalVentaBruta.toFixed(0))}</strong></td>
                            <td><strong>-</strong></td>
                            <td><strong><span class="text-warning">${totalTransacciones.toLocaleString('es-CL')}</span></strong></td>
                            <td><strong>-</strong></td>
                            <td><strong>-</strong></td>
                            <td><strong>${formatearMontoChile(totalVentaDelivery.toFixed(0))}</strong></td>
                            <td><strong>${ticketsDesgloseTotales}</strong></td>
                            <td><strong>${porcentajeTotalDelivery.toFixed(1)}%</strong></td>                      
                        </tr>`;
    tbody.innerHTML += ultimaFila;
    // ________________________________________________________________________________

    // LOCALES PROPIOS
    const tbodyF = document.getElementById('tabla-counters-fagotto-tbody');
    tbodyF.innerHTML = '';
    let totalVentaBrutaF = 0;
    let totalVentaDeliveryF = 0;
    let totalTransaccionesF = 0;
    let totalTicketsUberF = 0;
    let totalTicketsRappiF = 0;
    let totalTicketsPedidosYaF = 0;

    // IDs de Locales Propios
    const idsLocalesPropios = [86, 58, 107, 59, 111, 97, 78, 116];

    for (const app in request) {
        let appIdName = app.split(',',2);
        const appId = parseInt(appIdName[0]);

        if(idsLocalesPropios.includes(appId)){
            
            const counters = request[app].original.counters;
            const localId = parseInt(appIdName[0]);
            
            // Calcular Venta Bruta (total de todas las ventas)
            const ventaBruta = parseFloat(counters.balanceTotal) || 0;
            
            // Calcular TC (ticket promedio) - necesitamos las órdenes totales
            const ordenes = parseInt(counters.orders) || 1;
            
            // Calcular Venta Delivery (suma de uber + rappi + pedidos_ya)
            const uber = parseFloat(counters.uber) || 0;
            const rappi = parseFloat(counters.rappi) || 0;
            const pedidosYa = parseFloat(counters.pedidos_ya) || 0;
            const ventaDelivery = uber + rappi + pedidosYa;
            
            // Obtener conteo de tickets por plataforma (del backend)
            const ticketsUber = parseInt(counters.uber_count) || 0;
            const ticketsRappi = parseInt(counters.rappi_count) || 0;
            const ticketsPedidosYa = parseInt(counters.pedidos_ya_count) || 0;
            const totalTicketsDelivery = ticketsUber + ticketsRappi + ticketsPedidosYa;
            
            // Crear texto de desglose de tickets
            let ticketsDesglose = '';
            if (totalTicketsDelivery > 0) {
                const partes = [];
                if (ticketsUber > 0) partes.push(`${ticketsUber} Uber 🚗`);
                if (ticketsRappi > 0) partes.push(`${ticketsRappi} Rappi 📱`);
                if (ticketsPedidosYa > 0) partes.push(`${ticketsPedidosYa} Pedidos Ya 🛵`);
                ticketsDesglose = `<div class="fw-bold text-primary">${totalTicketsDelivery} tickets</div>
                                   <div style="font-size: 0.75rem; color: #64748b;">${partes.join(' | ')}</div>`;
            } else {
                ticketsDesglose = '<span class="text-muted">0</span>';
            }
            
            // Calcular porcentaje delivery
            const porcentajeDelivery = ventaBruta > 0 ? ((ventaDelivery / ventaBruta) * 100) : 0;

            // 🎯 Obtener meta del local y calcular estado
            const metaLocal = metasPorLocal[localId];
            let metaHTML = '<span class="text-muted">Sin meta</span>';
            let tcMetaHTML = '<span class="text-muted">-</span>';
            let tcRealVsMetaHTML = '<span class="text-muted">-</span>';
            
            // Calcular ticket promedio real de este local
            const ticketPromedioF = ordenes > 0 ? ventaBruta / ordenes : 0;
            
            if (metaLocal) {
                const metaValor = parseInt(metaLocal.meta_diaria) || 0;
                const diferencia = ventaBruta - metaValor;
                const cumplida = diferencia >= 0;
                
                // T/C de la meta (número de tickets objetivo)
                const tcMeta = parseInt(metaLocal.ticket_promedio) || 0;
                if (tcMeta > 0) {
                    tcMetaHTML = `<span class="fw-bold text-primary">${tcMeta.toLocaleString('es-CL')}</span>`;
                    
                    // Comparar Tickets Reales vs Tickets Meta (no precio promedio)
                    const diferenciaTc = ordenes - tcMeta;
                    const cumpleTcMeta = diferenciaTc >= 0;
                    
                    if (cumpleTcMeta) {
                        tcRealVsMetaHTML = `<div>
                                                <div class="fw-bold text-success">✅ Meta superada</div>
                                                <div style="font-size: 0.85rem; color: #10b981;">
                                                    Meta T/C: ${tcMeta.toLocaleString('es-CL')}
                                                    <span class="badge bg-success">+${Math.abs(diferenciaTc).toFixed(0)}</span>
                                                </div>
                                            </div>`;
                    } else {
                        tcRealVsMetaHTML = `<div>
                                                <div style="font-size: 1.3rem; font-weight; bold; color: #f59e0b;">Faltan ${Math.abs(diferenciaTc).toFixed(0)}</div>
                                                <div style="font-size: 0.85rem; color: #9ca3af;">
                                                    Meta T/C: ${tcMeta.toLocaleString('es-CL')}
                                                </div>
                                            </div>`;
                    }
                }
                
                if (cumplida) {
                    metaHTML = `<div>
                                    <div class="fw-bold text-success">✅ Meta superada</div>
                                    <div style="font-size: 0.85rem; color: #10b981;">
                                        Meta: ${formatearMontoChile(metaValor)} 
                                        <span class="badge bg-success">+${formatearMontoChile(Math.abs(diferencia))}</span>
                                    </div>
                                </div>`;
                } else {
                    metaHTML = `<div>
                                    <div style="font-size: 1.3rem; font-weight: bold; color: #f59e0b;">Faltan ${formatearMontoChile(Math.abs(diferencia))}</div>
                                    <div style="font-size: 0.85rem; color: #9ca3af;">
                                        Meta: ${formatearMontoChile(metaValor)}
                                    </div>
                                </div>`;
                }
            }

            const filaF = `<tr>
                              <td>${appIdName[1]}</td>
                              <td>${formatearMontoChile(ventaBruta.toFixed(0))}</td>
                              <td>${metaHTML}</td>
                              <td><span class="fw-bold text-info">${ordenes.toLocaleString('es-CL')}</span></td>
                              <td>${tcMetaHTML}</td>
                              <td>${tcRealVsMetaHTML}</td>
                              <td>${formatearMontoChile(ventaDelivery.toFixed(0))}</td>
                              <td>${ticketsDesglose}</td>
                              <td><strong>${porcentajeDelivery.toFixed(1)}%</strong></td>
                          </tr>`;
            
            tbodyF.innerHTML += filaF;
            totalVentaBrutaF += ventaBruta;
            totalVentaDeliveryF += ventaDelivery;
            totalTransaccionesF += ordenes;
            totalTicketsUberF += ticketsUber;
            totalTicketsRappiF += ticketsRappi;
            totalTicketsPedidosYaF += ticketsPedidosYa;
        }
    }
    
    // Calcular totales generales para Fagotto
    const porcentajeTotalDeliveryF = totalVentaBrutaF > 0 ? ((totalVentaDeliveryF / totalVentaBrutaF) * 100) : 0;
    const totalTicketsDeliveryGeneralF = totalTicketsUberF + totalTicketsRappiF + totalTicketsPedidosYaF;
    
    // Crear desglose total de tickets para Fagotto
    const partesTotalesF = [];
    if (totalTicketsUberF > 0) partesTotalesF.push(`${totalTicketsUberF} Uber 🚗`);
    if (totalTicketsRappiF > 0) partesTotalesF.push(`${totalTicketsRappiF} Rappi 📱`);
    if (totalTicketsPedidosYaF > 0) partesTotalesF.push(`${totalTicketsPedidosYaF} Pedidos Ya 🛵`);
    const ticketsDesgloseTotalesF = `<div class="fw-bold text-warning">${totalTicketsDeliveryGeneralF} tickets</div>
                                     <div style="font-size: 0.75rem;">${partesTotalesF.join(' | ')}</div>`;
    
    const ultimaFilaF = `<tr class="table-dark">
                            <td><strong>🏪 TOTALES LOCALES PROPIOS</strong></td>
                            <td><strong>${formatearMontoChile(totalVentaBrutaF.toFixed(0))}</strong></td>
                            <td><strong>-</strong></td>
                            <td><strong><span class="text-warning">${totalTransaccionesF.toLocaleString('es-CL')}</span></strong></td>
                            <td><strong>-</strong></td>
                            <td><strong>-</strong></td>
                            <td><strong>${formatearMontoChile(totalVentaDeliveryF.toFixed(0))}</strong></td>
                            <td><strong>${ticketsDesgloseTotalesF}</strong></td>
                            <td><strong>${porcentajeTotalDeliveryF.toFixed(1)}%</strong></td>                      
                        </tr>`;
    tbodyF.innerHTML += ultimaFilaF;
    
    // ________________________________________________________________________________
    // TRAI I PASTI
    const tbodyT = document.getElementById('tabla-counters-trai-tbody');
    tbodyT.innerHTML = '';
    let totalVentaBrutaT = 0;
    let totalVentaDeliveryT = 0;
    let totalTransaccionesT = 0;
    let totalTicketsUberT = 0;
    let totalTicketsRappiT = 0;
    let totalTicketsPedidosYaT = 0;

    // IDs de Trai i Pasti
    const idsTraiPasti = [106, 109];

    for (const app in request) {
        let appIdName = app.split(',',2);
        const appId = parseInt(appIdName[0]);

        if(idsTraiPasti.includes(appId)){
            
            const counters = request[app].original.counters;
            const localId = parseInt(appIdName[0]);
            
            // Calcular Venta Bruta (total de todas las ventas)
            const ventaBruta = parseFloat(counters.balanceTotal) || 0;
            
            // Calcular TC (ticket promedio) - necesitamos las órdenes totales
            const ordenes = parseInt(counters.orders) || 1;
            
            // Calcular Venta Delivery (suma de uber + rappi + pedidos_ya)
            const uber = parseFloat(counters.uber) || 0;
            const rappi = parseFloat(counters.rappi) || 0;
            const pedidosYa = parseFloat(counters.pedidos_ya) || 0;
            const ventaDelivery = uber + rappi + pedidosYa;
            
            // Obtener conteo de tickets por plataforma (del backend)
            const ticketsUber = parseInt(counters.uber_count) || 0;
            const ticketsRappi = parseInt(counters.rappi_count) || 0;
            const ticketsPedidosYa = parseInt(counters.pedidos_ya_count) || 0;
            const totalTicketsDelivery = ticketsUber + ticketsRappi + ticketsPedidosYa;
            
            // Crear texto de desglose de tickets
            let ticketsDesglose = '';
            if (totalTicketsDelivery > 0) {
                const partes = [];
                if (ticketsUber > 0) partes.push(`${ticketsUber} Uber 🚗`);
                if (ticketsRappi > 0) partes.push(`${ticketsRappi} Rappi 📱`);
                if (ticketsPedidosYa > 0) partes.push(`${ticketsPedidosYa} Pedidos Ya 🛵`);
                ticketsDesglose = `<div class="fw-bold text-primary">${totalTicketsDelivery} tickets</div>
                                   <div style="font-size: 0.75rem; color: #64748b;">${partes.join(' | ')}</div>`;
            } else {
                ticketsDesglose = '<span class="text-muted">0</span>';
            }
            
            // Calcular porcentaje delivery
            const porcentajeDelivery = ventaBruta > 0 ? ((ventaDelivery / ventaBruta) * 100) : 0;

            // 🎯 Obtener meta del local y calcular estado
            const metaLocal = metasPorLocal[localId];
            let metaHTML = '<span class="text-muted">Sin meta</span>';
            let tcMetaHTML = '<span class="text-muted">-</span>';
            let tcRealVsMetaHTML = '<span class="text-muted">-</span>';
            
            // Calcular ticket promedio real de este local
            const ticketPromedioT = ordenes > 0 ? ventaBruta / ordenes : 0;
            
            if (metaLocal) {
                const metaValor = parseInt(metaLocal.meta_diaria) || 0;
                const diferencia = ventaBruta - metaValor;
                const cumplida = diferencia >= 0;
                
                // T/C de la meta (número de tickets objetivo)
                const tcMeta = parseInt(metaLocal.ticket_promedio) || 0;
                if (tcMeta > 0) {
                    tcMetaHTML = `<span class="fw-bold text-primary">${tcMeta.toLocaleString('es-CL')}</span>`;
                    
                    // Comparar Tickets Reales vs Tickets Meta (no precio promedio)
                    const diferenciaTc = ordenes - tcMeta;
                    const cumpleTcMeta = diferenciaTc >= 0;
                    
                    if (cumpleTcMeta) {
                        tcRealVsMetaHTML = `<div>
                                                <div class="fw-bold text-success">✅ Meta superada</div>
                                                <div style="font-size: 0.85rem; color: #10b981;">
                                                    Meta T/C: ${tcMeta.toLocaleString('es-CL')}
                                                    <span class="badge bg-success">+${Math.abs(diferenciaTc).toFixed(0)}</span>
                                                </div>
                                            </div>`;
                    } else {
                        tcRealVsMetaHTML = `<div>
                                                <div style="font-size: 1.3rem; font-weight: bold; color: #f59e0b;">Faltan ${Math.abs(diferenciaTc).toFixed(0)}</div>
                                                <div style="font-size: 0.85rem; color: #9ca3af;">
                                                    Meta T/C: ${tcMeta.toLocaleString('es-CL')}
                                                </div>
                                            </div>`;
                    }
                }
                
                if (cumplida) {
                    metaHTML = `<div>
                                    <div class="fw-bold text-success">✅ Meta superada</div>
                                    <div style="font-size: 0.85rem; color: #10b981;">
                                        Meta: ${formatearMontoChile(metaValor)} 
                                        <span class="badge bg-success">+${formatearMontoChile(Math.abs(diferencia))}</span>
                                    </div>
                                </div>`;
                } else {
                    metaHTML = `<div>
                                    <div style="font-size: 1.3rem; font-weight: bold; color: #f59e0b;">Faltan ${formatearMontoChile(Math.abs(diferencia))}</div>
                                    <div style="font-size: 0.85rem; color: #9ca3af;">
                                        Meta: ${formatearMontoChile(metaValor)}
                                    </div>
                                </div>`;
                }
            }

            const filaT = `<tr>
                              <td>${appIdName[1]}</td>
                              <td>${formatearMontoChile(ventaBruta.toFixed(0))}</td>
                              <td>${metaHTML}</td>
                              <td><span class="fw-bold text-info">${ordenes.toLocaleString('es-CL')}</span></td>
                              <td>${tcMetaHTML}</td>
                              <td>${tcRealVsMetaHTML}</td>
                              <td>${formatearMontoChile(ventaDelivery.toFixed(0))}</td>
                              <td>${ticketsDesglose}</td>
                              <td><strong>${porcentajeDelivery.toFixed(1)}%</strong></td>
                          </tr>`;
            
            tbodyT.innerHTML += filaT;
            totalVentaBrutaT += ventaBruta;
            totalVentaDeliveryT += ventaDelivery;
            totalTransaccionesT += ordenes;
            totalTicketsUberT += ticketsUber;
            totalTicketsRappiT += ticketsRappi;
            totalTicketsPedidosYaT += ticketsPedidosYa;
        }
    }
    
    // Calcular totales generales para Trai i Pasti
    const porcentajeTotalDeliveryT = totalVentaBrutaT > 0 ? ((totalVentaDeliveryT / totalVentaBrutaT) * 100) : 0;
    const totalTicketsDeliveryGeneralT = totalTicketsUberT + totalTicketsRappiT + totalTicketsPedidosYaT;
    
    // Crear desglose total de tickets para Trai i Pasti
    const partesTotalesT = [];
    if (totalTicketsUberT > 0) partesTotalesT.push(`${totalTicketsUberT} Uber 🚗`);
    if (totalTicketsRappiT > 0) partesTotalesT.push(`${totalTicketsRappiT} Rappi 📱`);
    if (totalTicketsPedidosYaT > 0) partesTotalesT.push(`${totalTicketsPedidosYaT} Pedidos Ya 🛵`);
    const ticketsDesgloseTotalesT = `<div class="fw-bold text-warning">${totalTicketsDeliveryGeneralT} tickets</div>
                                     <div style="font-size: 0.75rem;">${partesTotalesT.join(' | ')}</div>`;
    
    const ultimaFilaT = `<tr class="table-dark">
                            <td><strong>🍕 TOTALES TRAI I PASTI</strong></td>
                            <td><strong>${formatearMontoChile(totalVentaBrutaT.toFixed(0))}</strong></td>
                            <td><strong>-</strong></td>
                            <td><strong><span class="text-warning">${totalTransaccionesT.toLocaleString('es-CL')}</span></strong></td>
                            <td><strong>-</strong></td>
                            <td><strong>-</strong></td>
                            <td><strong>${formatearMontoChile(totalVentaDeliveryT.toFixed(0))}</strong></td>
                            <td><strong>${ticketsDesgloseTotalesT}</strong></td>
                            <td><strong>${porcentajeTotalDeliveryT.toFixed(1)}%</strong></td>                      
                        </tr>`;
    tbodyT.innerHTML += ultimaFilaT;
    
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

      // Función para formatear el monto
      function formatearMontoChile(monto) {
          return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(monto);
      }

      // Variable para almacenar la sumatoria de todos los montos totales
      let montoTotalFacturado = 0;

      // Verificar si hay facturas
      if (facturas && facturas.length > 0) {
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
                                <td colspan="2"><strong>Total Facturado </strong></td>
                                <td colspan="2"><strong>${formatearMontoChile(montoTotalFacturado)}</strong></td>
                             </tr>`;
          tablaFacturacion.innerHTML += filaTotal;
      } else {
          // Si no hay facturas, mostrar mensaje
          const filaSinDatos = `<tr>
                                   <td colspan="4" class="text-center">
                                       <i class="fas fa-info-circle"></i>
                                       No hay facturas para el período seleccionado
                                   </td>
                               </tr>`;
          tablaFacturacion.innerHTML = filaSinDatos;
      }

      // Actualizar la tarjeta KPI de Total Facturado
      const kpiTotalFacturado = document.getElementById('kpi-total-facturado');
      if (kpiTotalFacturado) {
          kpiTotalFacturado.textContent = formatearMontoChile(montoTotalFacturado);
      }
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
      
      // Si el contenedor no existe, salir sin error
      if (!tablasContainer) {
          console.log('⚠️ Elemento tablas-container-salsas no encontrado, omitiendo...');
          return;
      }
      
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

// ========== FUNCIÓN DE ORDENAMIENTO DE TABLAS ==========
function initTableSorting() {
    const table = document.getElementById('franquiciasTable');
    if (!table) return;
    
    const headers = table.querySelectorAll('thead th.sortable');
    
    headers.forEach(header => {
        header.addEventListener('click', function() {
            const column = parseInt(this.dataset.column);
            const type = this.dataset.type;
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Determinar dirección de ordenamiento
            let direction = 'asc';
            if (this.classList.contains('sort-asc')) {
                direction = 'desc';
            }
            
            // Remover clases de ordenamiento de todos los headers
            headers.forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            
            // Agregar clase al header actual
            this.classList.add(direction === 'asc' ? 'sort-asc' : 'sort-desc');
            
            // Ordenar filas
            rows.sort((a, b) => {
                let aValue = a.cells[column].textContent.trim();
                let bValue = b.cells[column].textContent.trim();
                
                if (type === 'number') {
                    // Eliminar símbolos de moneda y puntos de miles
                    aValue = parseFloat(aValue.replace(/[$.,]/g, '')) || 0;
                    bValue = parseFloat(bValue.replace(/[$.,]/g, '')) || 0;
                } else {
                    // Comparación de texto
                    aValue = aValue.toLowerCase();
                    bValue = bValue.toLowerCase();
                }
                
                if (direction === 'asc') {
                    return aValue > bValue ? 1 : aValue < bValue ? -1 : 0;
                } else {
                    return aValue < bValue ? 1 : aValue > bValue ? -1 : 0;
                }
            });
            
            // Reordenar filas en el DOM
            rows.forEach(row => tbody.appendChild(row));
        });
    });
}





/**
 * Formatea números con separadores de miles
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

/**
 * Genera colores degradados bonitos para el gráfico de barras
 */
function generateGradientColors(count) {
    const baseColors = [
        { bg: 'rgba(59, 130, 246, 0.8)', border: 'rgba(59, 130, 246, 1)' },      // Azul
        { bg: 'rgba(16, 185, 129, 0.8)', border: 'rgba(16, 185, 129, 1)' },      // Verde
        { bg: 'rgba(245, 158, 11, 0.8)', border: 'rgba(245, 158, 11, 1)' },      // Naranja
        { bg: 'rgba(139, 92, 246, 0.8)', border: 'rgba(139, 92, 246, 1)' },      // Púrpura
        { bg: 'rgba(236, 72, 153, 0.8)', border: 'rgba(236, 72, 153, 1)' },      // Rosa
        { bg: 'rgba(14, 165, 233, 0.8)', border: 'rgba(14, 165, 233, 1)' },      // Cyan
        { bg: 'rgba(168, 85, 247, 0.8)', border: 'rgba(168, 85, 247, 1)' },      // Violeta
        { bg: 'rgba(34, 197, 94, 0.8)', border: 'rgba(34, 197, 94, 1)' },        // Lima
        { bg: 'rgba(249, 115, 22, 0.8)', border: 'rgba(249, 115, 22, 1)' },      // Naranja oscuro
        { bg: 'rgba(6, 182, 212, 0.8)', border: 'rgba(6, 182, 212, 1)' },        // Teal
    ];
    
    const backgrounds = [];
    const borders = [];
    
    for (let i = 0; i < count; i++) {
        const colorIndex = i % baseColors.length;
        backgrounds.push(baseColors[colorIndex].bg);
        borders.push(baseColors[colorIndex].border);
    }
    
    return { backgrounds, borders };
}

// ========== MAPA DE CALOR DE SALSAS GLOBAL ==========
const GLOBAL_SALSAS_CONFIG = [
    { nombre: 'Boloñesa', keywords: ['bolonesa', 'boloñesa'], emoji: '🍝', gramaje: 125 },
    { nombre: 'Pesto', keywords: ['pesto'], emoji: '🌿', gramaje: 80 },
    { nombre: 'Alfredo', keywords: ['alfredo'], emoji: '🧀', gramaje: 140 },
    { nombre: 'Champiñón', keywords: ['champinon', 'champiñon', 'champiñón'], emoji: '🍄', gramaje: 140 },
    { nombre: 'Camarón', keywords: ['camaron', 'camarón'], emoji: '🦐', gramaje: 140 },
    { nombre: 'Pollo Mostaza', keywords: ['pollo mostaza', 'crema/pollo/mostaza', 'crema pollo mostaza'], emoji: '🍗', gramaje: 140 },
    { nombre: 'Cheddar', keywords: ['cheddar'], emoji: '🧀', gramaje: 140 }
];

// Cargar mapa de calor global de salsas
async function loadGlobalSauceHeatmap() {
    console.log('🌶️ Cargando mapa de calor global de salsas...');
    
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value + ' 23:59:59';
    
    // Primero obtener todas las apps
    return new Promise((resolve) => {
        __conection({
            url: generarURLApi(`/getApps`),
            header: credentials(),
            dev: true,
            method: 'POST'
        }, {}, async function(apps) {
            console.log('📱 Apps obtenidas para salsas:', apps.length);
            
            if (!apps || apps.length === 0) {
                console.error('❌ No se encontraron apps');
                resolve();
                return;
            }
            
            // Obtener ventas de cada app
            const allSales = [];
            let completedRequests = 0;
            
            apps.forEach(app => {
                const url = generarURLApi(`/web/getAppSells?id=${app.id}&startDate=${startDate}&endDate=${endDate}&boleta=true&factura=true&per_page=10000`);
                console.log(`🔗 Obteniendo ventas de ${app.name}:`, url);
                
                __conection({
                    url: url,
                    header: credentials(),
                    dev: true,
                    method: 'GET'
                }, {}, function(response) {
                    console.log(`✅ Ventas de ${app.name}:`, response);
                    allSales.push({ app: app.name, data: response });
                    
                    completedRequests++;
                    if (completedRequests === apps.length) {
                        // Todas las peticiones completadas
                        console.log('✅ Todas las ventas obtenidas:', allSales.length);
                        processGlobalSauceData(allSales, startDate, endDate);
                        resolve();
                    }
                }, function(error) {
                    console.error(`❌ Error obteniendo ventas de ${app.name}:`, error);
                    completedRequests++;
                    if (completedRequests === apps.length) {
                        processGlobalSauceData(allSales, startDate, endDate);
                        resolve();
                    }
                });
            });
        }, function(error) {
            console.error('❌ Error obteniendo apps:', error);
            resolve();
        });
    });
}

// Procesar datos de todas las sucursales
function processGlobalSauceData(allSales, startDate, endDate) {
    console.log('🔍 Procesando ventas globales para extraer datos de salsas...', allSales);
    
    // Extraer productos de todas las sucursales
    let productos = [];
    
    allSales.forEach(saleData => {
        const response = saleData.data;
        
        // Procesar cada app en la respuesta
        for (const appName in response) {
            const appData = response[appName];
            
            if (appData && appData.original) {
                let ventas = [];
                
                if (Array.isArray(appData.original)) {
                    ventas = appData.original;
                } else if (appData.original.data) {
                    ventas = appData.original.data;
                }
                
                console.log(`📦 Ventas de ${saleData.app}:`, ventas.length);
                
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
            }
        }
    });
    
    console.log(`📦 Total productos globales extraídos: ${productos.length}`);
    
    if (productos.length === 0) {
        console.log('⚠️ No hay productos en las ventas globales');
        return;
    }
    
    // Calcular KPIs globales
    calculateGlobalSauceKPIs(productos);
    
    // Procesar por día
    processGlobalSauceDataByDay(productos, startDate, endDate);
}

// Calcular KPIs globales
function calculateGlobalSauceKPIs(productos) {
    let salsaExtra = 0;
    let fettucine = 0;
    let bigoli = 0;
    let quesoExtra = 0;
    
    productos.forEach(producto => {
        const nombreLower = (producto.name || '').toLowerCase();
        const cantidad = parseInt(producto.quantity) || 1;
        
        const tieneSalsa = nombreLower.includes('alfredo') || 
                          nombreLower.includes('bolonesa') || nombreLower.includes('boloñesa') ||
                          nombreLower.includes('pesto') ||
                          nombreLower.includes('champinon') || nombreLower.includes('champiñon') ||
                          nombreLower.includes('camaron') || nombreLower.includes('camarón') ||
                          nombreLower.includes('pollo mostaza') ||
                          nombreLower.includes('cheddar');
        
        const esPasta = nombreLower.includes('pasta') ||
                       nombreLower.includes('fettucine') ||
                       nombreLower.includes('bigoli');
        
        if (tieneSalsa && !esPasta) {
            salsaExtra += cantidad;
        }
        
        if (nombreLower.includes('fettucine')) {
            fettucine += cantidad;
            console.log('✅ Fettucine encontrado:', producto.name, 'cantidad:', cantidad, 'total acumulado:', fettucine);
        }
        
        if (nombreLower.includes('bigoli')) {
            bigoli += cantidad;
            console.log('✅ Bigoli encontrado:', producto.name, 'cantidad:', cantidad, 'total acumulado:', bigoli);
        }
        
        if (nombreLower === 'queso extra' || nombreLower.includes('queso extra')) {
            quesoExtra += cantidad;
        }
    });
    
    console.log('📊 TOTALES FINALES - Fettucine:', fettucine, 'Bigoli:', bigoli);
    
    document.getElementById('globalSauceExtraTotal').textContent = salsaExtra;
    document.getElementById('globalFettucineTotal').textContent = fettucine;
    document.getElementById('globalBigoliTotal').textContent = bigoli;
    document.getElementById('globalQuesoExtraTotal').textContent = quesoExtra;
    
    // Actualizar TOTAL VENDIDO (Fettucine + Bigoli)
    const totalVendido = fettucine + bigoli;
    const elementoTotal = document.getElementById('globalTotalVendido');
    
    console.log('🍝 TOTAL VENDIDO calculado:', totalVendido);
    console.log('🎯 Elemento globalTotalVendido:', elementoTotal);
    
    if (elementoTotal) {
        elementoTotal.textContent = totalVendido;
        console.log('✅ TOTAL VENDIDO actualizado en DOM:', elementoTotal.textContent);
    } else {
        console.error('❌ No se encontró el elemento globalTotalVendido');
    }
}

// Procesar datos por día
function processGlobalSauceDataByDay(productos, startDate, endDate) {
    const fechaInicioStr = startDate.split(' ')[0];
    const fechaFinStr = endDate.split(' ')[0];
    
    const fechaInicio = new Date(fechaInicioStr + 'T12:00:00');
    const fechaFin = new Date(fechaFinStr + 'T12:00:00');
    
    const dias = [];
    const currentDate = new Date(fechaInicio);
    while (currentDate <= fechaFin) {
        dias.push(new Date(currentDate));
        currentDate.setDate(currentDate.getDate() + 1);
    }
    
    // Inicializar matriz
    const ventasPorSalsa = {};
    
    GLOBAL_SALSAS_CONFIG.forEach(salsa => {
        ventasPorSalsa[salsa.nombre] = {};
        dias.forEach(dia => {
            const diaKey = dia.getDate();
            ventasPorSalsa[salsa.nombre][diaKey] = 0;
        });
    });
    
    // Calcular totales de Fettucine y Bigoli
    let totalFettucine = 0;
    let totalBigoli = 0;
    
    // Procesar productos
    productos.forEach(producto => {
        const nombreProducto = (producto.name || '').toLowerCase();
        const fechaVenta = new Date(producto.created_at);
        const diaVenta = fechaVenta.getDate();
        const cantidad = parseInt(producto.quantity) || 1;
        
        // Contar Fettucine y Bigoli
        if (nombreProducto.includes('fettucine')) {
            totalFettucine += cantidad;
        }
        if (nombreProducto.includes('bigoli')) {
            totalBigoli += cantidad;
        }
        
        GLOBAL_SALSAS_CONFIG.forEach(salsa => {
            const coincide = salsa.keywords.some(keyword => 
                nombreProducto.includes(keyword.toLowerCase())
            );
            
            if (coincide && ventasPorSalsa[salsa.nombre][diaVenta] !== undefined) {
                ventasPorSalsa[salsa.nombre][diaVenta] += cantidad;
            }
        });
    });
    
    console.log('🍝 Total Fettucine:', totalFettucine, 'Total Bigoli:', totalBigoli, 'SUMA:', totalFettucine + totalBigoli);
    
    // Actualizar título
    updateGlobalSauceTitle(startDate, endDate);
    
    // Renderizar con los totales
    renderGlobalSauceHeatmap(ventasPorSalsa, dias, totalFettucine, totalBigoli);
}

// Actualizar título
function updateGlobalSauceTitle(startDate, endDate) {
    const titleElement = document.getElementById('globalSauceHeatmapTitle');
    if (!titleElement) return;
    
    const fechaInicio = new Date(startDate.split(' ')[0] + 'T12:00:00');
    const fechaFin = new Date(endDate.split(' ')[0] + 'T12:00:00');
    
    const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
    
    let textoFecha;
    if (fechaInicio.getTime() === fechaFin.getTime()) {
        textoFecha = fechaInicio.toLocaleDateString('es-ES', opciones);
    } else {
        const inicioStr = fechaInicio.toLocaleDateString('es-ES', opciones);
        const finStr = fechaFin.toLocaleDateString('es-ES', opciones);
        textoFecha = `${inicioStr} - ${finStr}`;
    }
    
    titleElement.textContent = `Mapa de Calor - Ventas de Salsas (${textoFecha})`;
}

// Renderizar tabla
function renderGlobalSauceHeatmap(ventasPorSalsa, dias, totalFettucine, totalBigoli) {
    const header = document.getElementById('globalSauceHeatmapHeader');
    const tbody = document.getElementById('globalSauceHeatmapBody');
    
    if (!header || !tbody) return;
    
    console.log('📊 Renderizando heatmap con totales - Fettucine:', totalFettucine, 'Bigoli:', totalBigoli);
    
    // Limpiar
    header.innerHTML = '<th class="sticky-col">Salsa</th>';
    tbody.innerHTML = '';
    
    // Headers de días
    const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    dias.forEach(dia => {
        const diaSemana = diasSemana[dia.getDay()];
        const diaMes = dia.getDate();
        header.innerHTML += `<th>${diaSemana}<br>${diaMes}</th>`;
    });
    header.innerHTML += '<th style="background: #ffc107; color: #000;">Total</th>';
    
    // Calcular máximo
    let maxVentas = 0;
    Object.values(ventasPorSalsa).forEach(salsaData => {
        Object.values(salsaData).forEach(cantidad => {
            if (cantidad > maxVentas) maxVentas = cantidad;
        });
    });
    
    let mejorSalsa = { nombre: '', total: 0 };
    
    // Primero calcular totales para cada salsa
    const salsasConTotales = GLOBAL_SALSAS_CONFIG.map(salsa => {
        let totalSalsa = 0;
        let totalGramos = 0;
        
        dias.forEach(dia => {
            const diaMes = dia.getDate();
            const cantidad = ventasPorSalsa[salsa.nombre][diaMes] || 0;
            const gramos = cantidad * salsa.gramaje;
            totalSalsa += cantidad;
            totalGramos += gramos;
        });
        
        if (totalSalsa > mejorSalsa.total) {
            mejorSalsa = { nombre: salsa.nombre, total: totalSalsa, emoji: salsa.emoji };
        }
        
        return {
            salsa,
            totalSalsa,
            totalGramos
        };
    });
    
    // Ordenar de mayor a menor por total de ventas
    salsasConTotales.sort((a, b) => b.totalSalsa - a.totalSalsa);
    
    // Ahora renderizar las filas ordenadas
    salsasConTotales.forEach(({ salsa, totalSalsa, totalGramos }) => {
        let html = `<tr>`;
        html += `<td class="sticky-col">${salsa.emoji} ${salsa.nombre} <span style="opacity: 0.7; font-size: 0.85rem;">(${salsa.gramaje}g)</span></td>`;
        
        dias.forEach(dia => {
            const diaMes = dia.getDate();
            const cantidad = ventasPorSalsa[salsa.nombre][diaMes] || 0;
            const gramos = cantidad * salsa.gramaje;
            
            const colorClass = getGlobalColorClass(cantidad, maxVentas);
            const pesoTexto = gramos >= 1000 ? `${(gramos / 1000).toFixed(1)}kg` : `${gramos}g`;
            
            html += `
                <td class="${colorClass}">
                    ${cantidad > 0 ? `${cantidad}<br><small style="opacity: 0.8;">${pesoTexto}</small>` : '-'}
                </td>
            `;
        });
        
        const totalPesoTexto = totalGramos >= 1000 ? `${(totalGramos / 1000).toFixed(1)}kg` : `${totalGramos}g`;
        html += `<td style="background: #fff3cd; font-weight: 800; font-size: 0.9rem;">${totalSalsa}<br><small style="opacity: 0.8;">${totalPesoTexto}</small></td>`;
        html += `</tr>`;
        
        tbody.innerHTML += html;
    });
    
    // Actualizar mejor salsa
    const topSauceElement = document.getElementById('globalTopSauce');
    if (topSauceElement && mejorSalsa.total > 0) {
        topSauceElement.innerHTML = `${mejorSalsa.emoji} <strong>${mejorSalsa.nombre}</strong>: ${mejorSalsa.total} unidades`;
    }
    
    // Actualizar TOTAL VENDIDO (Fettucine + Bigoli)
    const totalVendido = totalFettucine + totalBigoli;
    const elementoTotal = document.getElementById('globalTotalVendido');
    
    console.log('🍝 TOTAL VENDIDO calculado:', totalVendido, '(Fettucine:', totalFettucine, '+ Bigoli:', totalBigoli, ')');
    console.log('🎯 Elemento globalTotalVendido:', elementoTotal);
    
    if (elementoTotal) {
        elementoTotal.textContent = totalVendido;
        console.log('✅ TOTAL VENDIDO actualizado en DOM:', elementoTotal.textContent);
    } else {
        console.error('❌ No se encontró el elemento globalTotalVendido');
    }
}

function getGlobalColorClass(cantidad, maxVentas) {
    if (cantidad === 0) return 'sauce-cell-0';
    if (cantidad <= 5) return 'sauce-cell-1';
    if (cantidad <= 15) return 'sauce-cell-2';
    if (cantidad <= 30) return 'sauce-cell-3';
    if (cantidad <= 50) return 'sauce-cell-4';
    return 'sauce-cell-5';
}

// ========== FUNCIÓN PARA CARGAR METAS DIARIAS ==========
async function cargarMetasDiarias(mes, anio, dia) {
    return new Promise((resolve, reject) => {
        __conection({
            url: generarURLApi(`/web/metas-locales?mes=${mes}&anio=${anio}&dia=${dia}`),
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function(response) {
            const metas = response.data || response || [];
            const metasPorLocal = {};
            
            // Crear mapa de metas por aplication_id
            metas.forEach(meta => {
                if (meta.aplication_id) {
                    metasPorLocal[meta.aplication_id] = meta;
                }
            });
            
            console.log('📊 Metas cargadas:', metasPorLocal);
            resolve(metasPorLocal);
        }, function(error) {
            console.error('❌ Error al cargar metas:', error);
            resolve({}); // Retornar objeto vacío en caso de error
        });
    });
}
