$(document).ready(function () {
    // _name.text(_store().session.user().get("username")) //obtenemos el nombre del usuario
    getSells();
    loadSucursalesList();
    llenarSelectMetodosPago();
})

const app_name = document.getElementById('app-name');
var startDateValue;
var endDateValue;
const urlParams = new URLSearchParams(window.location.search);
const id = urlParams.get('id');
const waiters = [];


async function getData(startDate, endDate) {
    activateLoader();
    await getSells(startDate, endDate);
    desactivateLoader();
}

function getByDate() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    getData(startDate, endDate);
}

function getThisDay() {
    var startDate = getNowDate();
    var endDate = getNowDate() + ' 23:59:59';;

    getData(startDate, endDate);
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
    console.log(lastDayOfWeekFormatted);

    getData(firstDayOfWeekFormatted, lastDayOfWeekFormatted);
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

async function getSells(startDate = null, endDate = null, page) {
    if (id) {
        var params = `?params=true&page=${page || 1}&orderBy_date=desc`;

        var url = "";

        var typeSell = document.getElementById('typeSell').value;
        console.log(typeSell);

        if (startDate && endDate) {
            params += `&startDate=${startDate}&endDate=${endDate}&orderBy_date=desc`;
        }

        
        if (typeSell != "null") {
            params += `&paymode=${typeSell}`;
        }

        
        url = generarURLApi(`/web/getAppVentas${params}&id=${id}`);

        await __conection({
            url: url,
            header: credentials(),
            dev: true,
            method: 'GET'

        }, {}, function (request) {
            console.log("Apps ventas:", request);
            let apps = request;

            const tablasContainer = document.getElementById('tablas-container');
            tablasContainer.innerHTML = "";

            for (const app in apps) {
                // console.log(apps[app].original);
                let ventas = apps[app].original.items;
                
                // 🔍 DEBUG: Ver TODOS los campos de la primera venta
                if (ventas.length > 0) {
                    console.log("📋 Campos de una venta:", ventas[0]);
                    console.log("📋 Todas las propiedades:", Object.keys(ventas[0]));
                }
                if (ventas.length > 0) {
                    let fila = `<div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Folio</th>
                                                    <th scope="col">Fecha</th>
                                                    <th scope="col">Usuario</th>
                                                    <th scope="col">Cliente</th>
                                                    <th scope="col">Tipo</th>
                                                    <th scope="col">Total</th>
                                                    <th scope="col">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody> `;

                    for (const index in ventas) {
                        // Obtener el folio (solo viene en ventas con boleta)
                        const folio = ventas[index].sell_folio || null;
                        
                        // Destacar visualmente si tiene folio (es boleta electrónica)
                        const tieneFolio = folio !== null && folio !== undefined && folio !== '';
                        const folioHTML = tieneFolio 
                            ? `<span class="badge bg-success" title="Boleta Electrónica">📄 ${folio}</span>` 
                            : `<span class="text-muted">-</span>`;
                 
                        fila += `<tr ${tieneFolio ? 'class="table-success"' : ''}>
                                    <td><b>${ventas[index].id}</b></td>
                                    <td>${folioHTML}</td>
                                    <td>${ventas[index].created_at}</td>
                                    <td>${ventas[index].fullname}</td>
                                    <td>${ventas[index].client}</td>
                                    <td>${ventas[index].paymode}</td>
                                    <td>${ventas[index].total}</td>
                                    <td>Acciones</td>
                                </tr>`;
                    }
                    fila += `</tbody>
                            </table>
                        </div>`

                    tablasContainer.innerHTML += fila;
                } else {
                    const tablasContainer = document.getElementById('tablas-container');
                    tablasContainer.innerHTML = "NO HAY PEDIDOS";
                }
                // Lógica de paginación
                // const totalPages = Math.ceil(apps.length / itemsPerPage);
                let paginationHtml = '<ul class="pagination d-flex col-12 justify-content-center">';
                currentPage = page;
                // Crear los controles de paginación
                for (let i = 1; i <= 10; i++) {
                    paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" onclick="getSells('${startDate}', '${endDate}', ${i})">${i}</a></li>`;
                }
                paginationHtml += '</ul>';

                // Mostrar los controles de paginación en la página
                document.getElementById('pagination').innerHTML = paginationHtml;
            }
        });
    } else {
        // const tablasContainer = document.getElementById('tablas-container');
        // const footerContainer = document.getElementById('footer-container');
        // tablasContainer.innerHTML = "<----SELECCIONA UNA SUCURSAL EN EL MENU LATERAL IZQUIERDO";
        // footerContainer.innerHTML = " ";
    }

}

async function loadSucursalesList() {
    let apps = []; // Array para almacenar los datos de id y name

    try {
        await __conection(
            {
                url: generarURLApi(`/getAppsPedidos`),
                header: credentials(),
                dev: true,
                method: 'POST'
            },
            {},
            function (request) {
                console.log(request);
                
                request.forEach((app, index) => {
                    apps[index] = {
                        id: app.id,
                        name: app.name,
                        pedidosNew: app.pedidosNew
                    };
                });

                // console.log(apps);

            }
        );

    } catch (error) {
        console.error("Error fetching apps:", error);
        // Handle errors appropriately
    }

    //Renderizar Lista de sucursales
    let sucursalesList = document.getElementById('sucursales-list');
    sucursalesList.innerHTML = '';

    apps.forEach((app, index) => {
        let fila = "";
        if (app.pedidosNew > 0) {
            fila = `  <li class="nav-item list-group-item">
                            <a href="/pages/sells.html?id=${app.id}" type="button" class="nav-link d-inline position-relative">
                                ${app.name} 
                                <span class="position-absolute start-100 translate-middle badge rounded-pill bg-danger">
                                   ${app.pedidosNew} 
                                </span>
                            </a>  
                        </li>`;
        } else {
            fila = `  <li class="nav-item list-group-item">
                            <a href="/pages/sells.html?id=${app.id}" type="button" class="nav-link d-inline position-relative">
                                ${app.name}
                            </a>  
                        </li>`;
        }

        sucursalesList.innerHTML += fila;
    });
}

let pedidoId = 0;

function openModalCliente(id){
    pedidoId = id;
    $('#clientCreate').modal('show');
}
function closeModalCliente(){
    $('#clientCreate').modal('hide');
}

function llenarSelectMetodosPago(idSelect = 'typeSell') {
    const selectElement = document.getElementById(idSelect);
  
    if (!selectElement) {
      console.error(`No se encontró el elemento select con el ID: ${idSelect}`);
      return;
    }
  
    const metodosPago = [
      'debito',
      'transferencia',
      'cheque',
      'banco',
      'amipass',
      'multicaja',
      'edenred',
      'convenio_empresa',
      'sodexo',
      'efectivo',
      'credito',
      'guia_despacho',
      'rappi',
      'uber',
      'boleta',
      'boleta_local'
    ];
  
    // Limpiar las opciones existentes
    selectElement.innerHTML = '';
  
    // Crear y agregar la opción por defecto "Todas"
    const opcionTodas = document.createElement('option');
    opcionTodas.value = 'null';
    opcionTodas.textContent = 'Todas';
    opcionTodas.classList.add('text-capitalize');
    selectElement.appendChild(opcionTodas);
  
    // Crear y agregar las opciones de métodos de pago
    metodosPago.forEach(metodo => {
      const opcion = document.createElement('option');
      opcion.value = metodo;
      opcion.textContent = metodo.replace(/_/g, ' ').toLowerCase().replace(/(?:^|\s)\w/g, match => match.toUpperCase()); // Formatear el texto
      opcion.classList.add('text-capitalize');
      selectElement.appendChild(opcion);
    });
  }