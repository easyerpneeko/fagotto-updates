$(document).ready(function () {
    // _name.text(_store().session.user().get("username")) //obtenemos el nombre del usuario
    getThisDay();
    loadSucursalesList();
})

const app_name = document.getElementById('app-name');
var startDateValue;
var endDateValue;
const urlParams = new URLSearchParams(window.location.search);
const id = urlParams.get('id');
const waiters = [];


function getData(startDate, endDate) {
    // activateLoader();
    getPedidos();
    // deactivateLoader();
}
function getByDate() {
    const DateInput = document.getElementById('date');
    const DateValue = DateInput.value;
    const startDate = DateValue;
    const endDate = DateValue + ' 23:59:59';

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

function getPedidos(startDate, endDate, page) {

    var params = `?params=true&page=${page || 1}`;

    __conection({
        url: generarURLApi(`/getAppRequests${params}`),
        header: credentials(),
        dev: true,
        method: 'GET'

    }, {}, function (request) {
        console.log("Apps pedidos:", request);
        let apps = request;

        // const tablaPedidos = document.getElementById('pedidos-table');
        const tablasContainer = document.getElementById('tablas-container');
        tablasContainer.innerHTML = "";
        // tablaPedidos.innerHTML = '';
        // console.log(tablaPedidos);


        for (const app in apps) {
            // console.log(apps[app].original);
            let pedidos = apps[app].original.items;
            if (pedidos.length > 0) {
                let fila = `<div class="table-responsive">
                                <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Sucursal</th>
                                                <th scope="col">Nombre</th>
                                                <th scope="col">Telefono</th>
                                                <th scope="col">Metodo de pago</th>
                                                <th scope="col">Comentario</th>
                                                <th scope="col">Estado</th>
                                                <th scope="col">Estado de pago</th>
                                                <th scope="col">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody> `;

                for (const pedido in pedidos) {
                    // console.log(pedidos[pedido].contact_name);
                    const pedido_status = pedidos[pedido].status === 'aprobado' ?
                        '<i class="fa fa-check icon" aria-hidden="true" style="font-size: 2rem; color: green;"></i>' : '<i class="fa fa-ban icon" aria-hidden="true" style="font-size: 2rem; color: red;"></i>';


                    fila += `<tr>
                                    <td><b>${pedidos[pedido].id}</b></td>
                                    <td><b>${app}</b></td>
                                    <td>${pedidos[pedido].contact_name}</td>
                                    <td>${pedidos[pedido].contact_phone}</td>
                                    <td>${pedidos[pedido].paymode}</td>
                                    <td>${pedidos[pedido].comment}</td>
                                    <td class='}'>${pedido_status}</td>
                                    <td>${pedidos[pedido].status_payment}</td>
                                    <td>
                                        <button class='btn btn-success' onclick="aprobarPedido(${pedidos[pedido].app_id},${pedidos[pedido].id})"><i class="fa-solid fa-check"></i></button>
                                        <button class='btn btn-danger' onclick="rechazarPedido(${pedidos[pedido].app_id},${pedidos[pedido].id})"><i class="fa-solid fa-ban"></i></button>
                                        <button class='btn btn-info' data-bs-toggle="modal" data-bs-target="#productsModal" 
                                            onclick="verPedido(${pedidos[pedido].app_id},${pedidos[pedido].id})"><i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button class='btn btn-secondary' data-bs-toggle="modal" data-bs-target="#newGuiaDespachoModal">
                                            <i class="fa-solid fa-print"></i>
                                        </button>
                                    </td>
                                </tr>`;

                }
                fila += `</tbody>
                        </table>
                    </div>`

                tablasContainer.innerHTML += fila;
            }
            // Lógica de paginación
            // const totalPages = Math.ceil(apps.length / itemsPerPage);
            let paginationHtml = '<ul class="pagination d-flex col-12 justify-content-center">';
            currentPage = page;
            // Crear los controles de paginación
            for (let i = 1; i <= 10; i++) {
                paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" onclick="getPedidos('${startDate}', '${endDate}', ${i})">${i}</a></li>`;
            }
            paginationHtml += '</ul>';

            // Mostrar los controles de paginación en la página
            document.getElementById('pagination').innerHTML = paginationHtml;
        }

    });
}

function verPedido(app_id, id) {
    __conection({
        url: generarURLApi(`/local/request/view/${app_id}/${id}`),
        header: credentials(),
        dev: true,
        method: 'GET'

    }, {}, function (request) {
        const pedido = request;

        const products = JSON.parse(pedido.products);
        console.log(products);
        const productsModal = document.getElementById('productsModal')

        // const button = event.relatedTarget

        const modalTitle = productsModal.querySelector('.modal-title')


        modalTitle.textContent = `Pedido # - ${id}`;

        const tablaProducts = document.getElementById('table-products');
        tablaProducts.innerHTML = '';

        for (const product in products) {
            // console.log(products[product].name);
            const fila = `<tr>
                                <td>${products[product].name}</td> 
                                <td>${products[product].name !== 'Vaso' && products[product].name !== 'Huevo' && products[product].name !== 'Harina' && products[product].name !== 'Botella de Huevos 1L' ? `${products[product].quantity} KL` : products[product].quantity}</td>  
                        </tr>`;
            tablaProducts.innerHTML += fila;
        }

        const listData = document.getElementById('data-pedido');

        const bodyList = `<li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Nombre</div>
                                ${pedido.contact_name}
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Telefono de contacto</div>
                                ${pedido.contact_phone}
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Tipo de pago</div>
                                ${pedido.paymode}
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Comentario</div>
                                ${pedido.comment}
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Reseña</div>
                                ${pedido.review}
                            </div>
                        </li>`;
        listData.innerHTML = bodyList;

        const total = document.getElementById('total');
        total.innerHTML = pedido.price;


    });


}
function aprobarPedido(app_id, id) {

    __conection({
        url: generarURLApi(`/local/request/approve/${app_id}/${id}`),
        header: credentials(),
        dev: true,
        method: 'PUT'

    }, {}, function (request) {

        console.log(request);

        getPedidos();

    });
}
function rechazarPedido(app_id, id) {

    __conection({
        url: generarURLApi(`/local/request/decline/${app_id}/${id}`),
        header: credentials(),
        dev: true,
        method: 'PUT'

    }, {}, function (request) {

        console.log(request);

        getPedidos();
    });
}

async function loadSucursalesList() {
    let apps= []; // Array para almacenar los datos de id y name

    try {
        await __conection(
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
        
        const fila = `<li class="nav-item">
                    <a class="nav-link active" href="/pages/pedidos.html?id=${app.id}">
                        <span data-feather="file-text"></span>
                        ${app.name}
                    </a>
                    </li>`;
        sucursalesList.innerHTML += fila;
    });
}