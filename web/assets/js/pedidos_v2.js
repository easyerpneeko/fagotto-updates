$(document).ready(function () {
    // _name.text(_store().session.user().get("username")) //obtenemos el nombre del usuario
    getPedidos();
    loadSucursalesList();
    
    // Obtener información de la franquicia si hay un id en la URL
    console.log("🔍 DEBUG - ID de la URL:", id);
    if (id) {
        console.log("✅ DEBUG - Llamando a getApp()");
        getApp();
    } else {
        console.log("❌ DEBUG - No hay ID en la URL, no se llama getApp()");
    }
    
    // Auto-refresh de notificaciones cada 30 segundos
    setInterval(function() {
        loadSucursalesList();
        console.log('🔔 Notificaciones actualizadas automáticamente');
    }, 30000); // 30 segundos
})

const app_name = document.getElementById('app-name');
var startDateValue;
var endDateValue;
const urlParams = new URLSearchParams(window.location.search);
let id = urlParams.get('id'); // Cambiar a let para poder actualizarla
const waiters = [];
window.currentFranquiciaName = 'N/A'; // Variable global para almacenar el nombre de la franquicia


async function getData(startDate, endDate) {
    activateLoader();
    await getPedidos(startDate, endDate);
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
        console.log("🔍 DEBUG getApp() - Respuesta completa:", request);
        if (request && request[0] && request[0].name) {
            window.currentFranquiciaName = request[0].name;
            console.log("✅ DEBUG getApp() - Nombre guardado:", request[0].name);
            if (app_name) {
                app_name.innerHTML = request[0].name;
            }
        } else {
            console.log("❌ DEBUG getApp() - No se pudo obtener el nombre");
        }
    });
}

function getThisWeek() {
    const currentDate = new Date();
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
    const day = String(currentDate.getDate()).padStart(2, '0');

    // Obtener el dÃ­a de la semana (0-6)
    const currentDayOfWeek = currentDate.getDay();

    // Calcular la fecha del primer dÃ­a de la semana (lunes)
    const firstDayOfWeek = new Date(currentDate);
    firstDayOfWeek.setDate(day - currentDayOfWeek + 1);

    const firstDay = String(firstDayOfWeek.getDate()).padStart(2, '0');
    const firstMonth = String(firstDayOfWeek.getMonth() + 1).padStart(2, '0');

    // Calcular la fecha del Ãºltimo dÃ­a de la semana (domingo)
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

async function getPedidos(startDate = null, endDate = null, page) {
    // Usar selectedSucursalId si está disponible, sino usar id de URL
    const currentId = selectedSucursalId || id;
    
    if (currentId) {
        var params = `?params=true&page=${page || 1}`;
        var url = "";
        if (startDate && endDate) {
            url = generarURLApi(`/web/getAppRequests${params}&id=${currentId}&startDate=${startDate}&endDate=${endDate}`);
        } else {
            url = generarURLApi(`/web/getAppRequests${params}&id=${currentId}`);
        }

        await __conection({
            url: url,
            header: credentials(),
            dev: true,
            method: 'GET'

        }, {}, function (request) {
            console.log("Apps pedidos:", request);
            let apps = request;

            const tablasContainer = document.getElementById('tablas-container');
            tablasContainer.innerHTML = "";

            for (const app in apps) {
                let pedidos = apps[app].original.items;
                
                if (pedidos && pedidos.length > 0) {
                    let fila = `<div class="table-responsive">
                                    <h3 class="mt-3 mb-2">${app}</h3>
                                    <table class="table table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Sucursal</th>
                                                    <th scope="col">Nombre</th>
                                                    <th scope="col">Telefono</th>
                                                    <th scope="col">Metodo de pago</th>
                                                    <th scope="col">Comentario</th>
                                                    <th scope="col">Fecha</th>
                                                    <th scope="col">Estado</th>
                                                    <th scope="col">Estado de pago</th>
                                                    <th scope="col">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody> `;

                    for (const pedido in pedidos) {
                        // console.log(pedidos[pedido].contact_name);
                        let pedido_status;

                        switch (pedidos[pedido].status) {
                            case 'nuevo': pedido_status = '<i class="fa fa-star-of-life icon" aria-hidden="true" style="font-size: 2rem; color: green;"></i>';
                                break;
                            case 'espera': pedido_status = '<i class="fa fa-hourglass-half icon" aria-hidden="true" style="font-size: 2rem; color: orange;"></i>';
                                break;
                            case 'aprobado': pedido_status = '<i class="fa fa-check icon" aria-hidden="true" style="font-size: 2rem; color: green;"></i>';
                                break;
                            case 'rechazado': pedido_status = '<i class="fa fa-ban icon" aria-hidden="true" style="font-size: 2rem; color: red;"></i>';
                                break;
                        }
                        // 🚨 VERIFICAR SI ES PEDIDO DE EMERGENCIA
                        const esEmergencia = pedidos[pedido].emergency && parseFloat(pedidos[pedido].emergency) > 0;
                        const emergenciaStyle = esEmergencia ? 'style="background-color: #ffeaa7; border-left: 4px solid #e17055;"' : '';
                        const emergenciaIcon = esEmergencia ? '<span title="Pedido de Emergencia">🚨</span> ' : '';
                        
                        if (pedidos[pedido].print != 1) {
                            fila += `<tr ${emergenciaStyle}>
                                        <td><b>${emergenciaIcon}${pedidos[pedido].id}</b></td>
                                        <td><b>${app}</b></td>
                                        <td>${pedidos[pedido].contact_name}</td>
                                        <td>${pedidos[pedido].contact_phone}</td>
                                        <td>${pedidos[pedido].paymode}</td>
                                        <td>${pedidos[pedido].comment}</td>
                                        <td>${pedidos[pedido].created_at}</td>
                                        <td>${pedido_status}</td>
                                        <td>${pedidos[pedido].status_payment}</td>
                                        <td>
                                            <button class='btn btn-success' onclick="aprobarPedido(${pedidos[pedido].app_id},${pedidos[pedido].id})"><i class="fa-solid fa-check"></i></button>
                                            <button class='btn btn-danger' onclick="rechazarPedido(${pedidos[pedido].app_id},${pedidos[pedido].id})"><i class="fa-solid fa-ban"></i></button>
                                            <button class='btn btn-info' data-bs-toggle="modal" data-bs-target="#productsModal" 
                                                onclick="verPedido(${pedidos[pedido].app_id},${pedidos[pedido].id}),mirarPedido(${pedidos[pedido].app_id},${pedidos[pedido].id})"><i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button onclick="openModalCliente(${pedidos[pedido].id})" class='btn btn-secondary' data-bs-toggle="modal" data-bs-target="#clientCreate">
                                                <i class="fa-solid fa-print"></i>
                                            </button>
                                        </td>
                                    </tr>`;
                        } else {
                            fila += `<tr>
                                        <td><b>${pedidos[pedido].id}</b></td>
                                        <td><b>${app}</b></td>
                                        <td>${pedidos[pedido].contact_name}</td>
                                        <td>${pedidos[pedido].contact_phone}</td>
                                        <td>${pedidos[pedido].paymode}</td>
                                        <td>${pedidos[pedido].comment}</td>
                                        <td>${pedidos[pedido].created_at}</td>
                                        <td>${pedido_status}</td>
                                        <td>${pedidos[pedido].status_payment}</td>
                                        <td>
                                            <button class='btn btn-success' onclick="aprobarPedido(${pedidos[pedido].app_id},${pedidos[pedido].id})"><i class="fa-solid fa-check"></i></button>
                                            <button class='btn btn-danger' onclick="rechazarPedido(${pedidos[pedido].app_id},${pedidos[pedido].id})"><i class="fa-solid fa-ban"></i></button>
                                            <button class='btn btn-info' data-bs-toggle="modal" data-bs-target="#productsModal" 
                                                onclick="verPedido(${pedidos[pedido].app_id},${pedidos[pedido].id}),mirarPedido(${pedidos[pedido].app_id},${pedidos[pedido].id})"><i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button onclick="verFacturaPDF(${pedidos[pedido].id})" class='btn btn-info' title="Ver PDF de Factura">
                                                <i class="fa-solid fa-file-pdf"></i> Ver Factura
                                            </button>
                                            <button onclick="openModalCancelarFactura(${pedidos[pedido].id})" class='btn btn-warning' data-bs-toggle="modal" data-bs-target="#cancelarFacturaModal">
                                                <i class="fa-solid fa-times-circle"></i> Cancelar Factura
                                            </button>
                                        </td>
                                    </tr>`;
                        }



                    }
                    fila += `</tbody>
                            </table>
                        </div>`

                    tablasContainer.innerHTML += fila;
                } else {
                    tablasContainer.innerHTML += `<div class="alert alert-warning">No hay pedidos para ${app}</div>`;
                }
                // LÃ³gica de paginaciÃ³n
                // const totalPages = Math.ceil(apps.length / itemsPerPage);
                let paginationHtml = '<ul class="pagination d-flex col-12 justify-content-center">';
                currentPage = page;
                // Crear los controles de paginaciÃ³n
                for (let i = 1; i <= 10; i++) {
                    paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" onclick="getPedidos('${startDate}', '${endDate}', ${i})">${i}</a></li>`;
                }
                paginationHtml += '</ul>';

                // Mostrar los controles de paginaciÃ³n en la pÃ¡gina
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

function verPedido(app_id, id) {
    __conection({
        url: generarURLApi(`/local/request/view/${app_id}/${id}`),
        header: credentials(),
        dev: true,
        method: 'GET'

    }, {}, function (request) {
        const pedido = request;

        // 🚨 VERIFICAR SI ES PEDIDO DE EMERGENCIA
        const esEmergencia = pedido.emergency && parseFloat(pedido.emergency) > 0;
        console.log(`${esEmergencia ? '🚨 PEDIDO DE EMERGENCIA' : '📝 Pedido Normal'} - ID: ${id}`);
        
        // Hacer disponible globalmente para el PDF
        window.currentPedidoEsEmergencia = esEmergencia;
        window.currentPedidoFecha = pedido.created_at; // 📅 Guardar fecha del pedido para la guía

        const products = JSON.parse(pedido.products);
        console.log("🔍 PRODUCTOS RECIBIDOS DE LA API:", products);
        
        // Analizar cada producto individualmente
        products.forEach((product, index) => {
            console.log(`📦 Producto ${index + 1}:`, {
                name: product.name,
                quantity: product.quantity,
                category: product.category,
                id: product.id,
                esFocaccia: product.name.toLowerCase().includes('focaccia'),
                esCategoria3: product.category == 3
            });
        });
        
        // Guardar productos originales para el PDF
        productosOriginales = products;
        const productsModal = document.getElementById('productsModal')

        // const button = event.relatedTarget

        const modalTitle = productsModal.querySelector('.modal-title')


        modalTitle.textContent = `Pedido # - ${id}`;

        const tablaProducts = document.getElementById('table-products');
        tablaProducts.innerHTML = '';
        let i = 1;
        for (const product in products) {
            
            let fila = "";

            // FunciÃ³n para determinar la unidad correcta - USA unidad_medida DE LA DB
            function getUnidadProducto(product) {
                console.log("🔍 MODAL ANALISIS:", {
                    nombre: product.name,
                    cantidad: product.quantity,
                    unidad_medida: product.unidad_medida,
                    categoria: product.category,
                    id: product.id
                });
                
                // ✅ PRIORIDAD 1: Si viene unidad_medida de la base de datos, usarla
                if (product.unidad_medida && product.unidad_medida.trim() !== '') {
                    console.log("✅ USANDO UNIDAD DE DB:", product.name, "->", product.quantity + ' ' + product.unidad_medida);
                    return product.quantity + ' ' + product.unidad_medida;
                }
                
                // FALLBACK: Lógica anterior por si no viene unidad_medida
                // FOCACCIAS
                const esFocacciaNombre = product.name.toLowerCase().includes('focaccia') && !product.name.toLowerCase().includes('pesto');
                const esCategoria3 = product.category == 3;
                const esFocacciaID = [11, 23, 27, 28].includes(product.id);
                const esPestoSalsaPorID = product.id == 19;
                const esPestoSalsa = product.name.toLowerCase().includes('pesto') && !product.name.toLowerCase().includes('focaccia');
                
                if ((esFocacciaNombre || esCategoria3 || esFocacciaID) && !esPestoSalsaPorID && !esPestoSalsa) {
                    const cantidadFinal = parseInt(product.quantity) || product.quantity;
                    console.log("🥖 FOCACCIA FALLBACK:", product.name, "->", cantidadFinal + ' unidades');
                    return cantidadFinal + ' unidades';
                }
                
                // SALSAS FALLBACK
                if (product.id == 19) {
                    return product.quantity + ' kg';
                }
                
                const esSalsa = product.name.toLowerCase().includes('bolonesa') ||
                               product.name.toLowerCase().includes('champinon') ||
                               product.name.toLowerCase().includes('alfredo') ||
                               product.name.toLowerCase().includes('mostaza');
                
                if (esSalsa && !product.name.toLowerCase().includes('focaccia')) {
                    return product.quantity + ' kg';
                }
                
                // Casos específicos fallback
                if (product.name === 'Botella de Huevos 1L') return product.quantity + ' botellas';
                if (product.name === 'Aceite Vegetal 5L') return product.quantity + ' unidades';
                if (product.name === 'Pliego (124 stickers)') return product.quantity + ' Pliego';
                
                const productosUnidades = ['Vaso', 'Sandwich', 'Aceite de oliva 5kg', 'Harina', 'Bolsa', 'papel mantequilla (Focaccia)', 'Papel Mantequilla (Bandeja)'];
                if (productosUnidades.includes(product.name)) {
                    return product.quantity + ' unidades';
                }
                
                // Por defecto kg
                console.log("⚡ FALLBACK - Producto va en KG:", product.name);
                return product.quantity + ' kg';
            }

            // TODOS los productos usan la función getUnidadProducto para unidades correctas
            const unidadCalculada = getUnidadProducto(products[product]);
            console.log("🔥 HTML GENERADO PARA:", products[product].name, "→", unidadCalculada);
            fila = `<tr>
                <td>${i}</td> 
                <td>${products[product].name.toUpperCase()}</td> 
                <td>${unidadCalculada}</td>  
            </tr>`;
            


            tablaProducts.innerHTML += fila;
            i++;
        }
        
        const listData = document.getElementById('data-pedido');

        // Crear indicador de emergencia
        const emergenciaHtml = esEmergencia ? 
            `<li class="list-group-item d-flex justify-content-between align-items-start" style="background-color: #ffeaa7; border-left: 4px solid #e17055;">
                <div class="ms-2 me-auto">
                    <div class="fw-bold text-danger">🚨 PEDIDO DE EMERGENCIA</div>
                    ¡Este pedido requiere atención prioritaria!
                </div>
            </li>` : '';

        const bodyList = `${emergenciaHtml}
                        <li class="list-group-item d-flex justify-content-between align-items-start">
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

        // Formatear para Chile (pesos chilenos)
        const formatter = new Intl.NumberFormat('es-CL', {
            style: 'currency',
            currency: 'CLP'
        });

        const total = document.getElementById('total');
        total.innerHTML = formatter.format(pedido.price);

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

function mirarPedido(app_id, id) {

    __conection({
        url: generarURLApi(`/local/request/watch/${app_id}/${id}`),
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

// Variable global para tracking de notificaciones
let lastNotificationCount = {};

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
                    
                    // Si este app coincide con el ID actual, guardar el nombre
                    if (id && app.id == id) {
                        window.currentFranquiciaName = app.name;
                        console.log("✅ DEBUG loadSucursalesList() - Nombre guardado desde lista:", app.name);
                    }
                });

                // console.log(apps);

            }
        );

    } catch (error) {
        console.error("Error fetching apps:", error);
        // Handle errors appropriately
    }

    // Detectar nuevos pedidos y mostrar notificación
    apps.forEach(app => {
        const previousCount = lastNotificationCount[app.id] || 0;
        if (app.pedidosNew > previousCount && previousCount > 0) {
            showNewOrderNotification(app.name, app.pedidosNew - previousCount);
        }
        lastNotificationCount[app.id] = app.pedidosNew;
    });

    //Renderizar Lista de sucursales
    let sucursalesList = document.getElementById('sucursales-list');
    sucursalesList.innerHTML = '';

    apps.forEach((app, index) => {
        let fila = "";
        if (app.pedidosNew > 0) {
            fila = `  <li class="nav-item list-group-item">
                            <a href="javascript:void(0)" onclick="selectSucursal(${app.id}, '${app.name}')" class="nav-link d-inline position-relative">
                                ${app.name} 
                                <span class="position-absolute start-100 translate-middle badge rounded-pill bg-danger">
                                   ${app.pedidosNew} 
                                </span>
                            </a>  
                        </li>`;
        } else {
            fila = `  <li class="nav-item list-group-item">
                            <a href="javascript:void(0)" onclick="selectSucursal(${app.id}, '${app.name}')" class="nav-link d-inline position-relative">
                                ${app.name}
                            </a>  
                        </li>`;
        }

        sucursalesList.innerHTML += fila;
    });
}

// Función para refrescar notificaciones manualmente
function refreshNotifications() {
    console.log('🔄 Actualizando notificaciones manualmente...');
    loadSucursalesList();
}

// Función para mostrar notificación de nuevos pedidos
function showNewOrderNotification(sucursalName, newOrdersCount) {
    // Crear notificación visual
    const notification = document.createElement('div');
    notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
    notification.style.cssText = `
        top: 20px; 
        right: 20px; 
        z-index: 9999; 
        width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-bell text-success me-2"></i>
            <div>
                <strong>¡Nuevos Pedidos!</strong><br>
                <small>${newOrdersCount} nuevo(s) pedido(s) en <strong>${sucursalName}</strong></small>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remover después de 10 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 10000);
    
    console.log(`🔔 Nuevos pedidos detectados: ${newOrdersCount} en ${sucursalName}`);
}

// Variable global para la sucursal seleccionada
let selectedSucursalId = null;

// Función para seleccionar sucursal sin recargar página
function selectSucursal(sucursalId, sucursalName) {
    selectedSucursalId = sucursalId;
    
    // Actualizar la variable id global
    id = sucursalId;
    
    // Actualizar el nombre de la franquicia actual para el PDF
    window.currentFranquiciaName = sucursalName;
    
    // Actualizar URL sin recargar página
    const newUrl = `${window.location.pathname}?id=${sucursalId}`;
    window.history.pushState({sucursalId: sucursalId}, '', newUrl);
    
    // Cargar pedidos de la sucursal seleccionada
    getPedidos();
    
    // Actualizar título
    const titleElement = document.querySelector('.d-flex.justify-content-between.flex-wrap h1');
    if (titleElement) {
        titleElement.textContent = `Pedidos - ${sucursalName}`;
    }
    
    console.log(`📍 Sucursal seleccionada: ${sucursalName} (ID: ${sucursalId})`);
    console.log(`✅ Nombre actualizado para PDF: ${window.currentFranquiciaName}`);
    console.log(`✅ ID actualizado: ${id}`);
}

let pedidoId = 0;
let pedidoIdCancelar = 0;

function openModalCliente(id){
    pedidoId = id;
    $('#clientCreate').modal('show');
}

function openModalCancelarFactura(id){
    pedidoIdCancelar = id;
    
    // Establecer fecha actual como fecha de cancelaciÃ³n
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_cancelacion').value = today;
    
    $('#cancelarFacturaModal').modal('show');
}

function closeModalCliente(){
    $('#clientCreate').modal('hide');
}

function closeModalCancelarFactura(){
    $('#cancelarFacturaModal').modal('hide');
}


function validateRUT() {
    const rutField = document.getElementById('rut');
    const rut = rutField.value.trim();
    // ExpresiÃ³n regular para formato de RUT chileno 
    const rutRegex = /^\d{8}-[kK0-9]$/;

    if (!rutRegex.test(rut)) {
        alert('El RUT ingresado no es vÃ¡lido. Debe tener 8 dÃ­gitos antes del guion y un dÃ­gito verificador (0-9 o K).');
        rutField.classList.add('is-invalid');
        return false;
    } else {
        rutField.classList.remove('is-invalid');
        return true;
    }
}

async function getClient() {

    let rut = document.getElementById('rut').value;
    const url = generarURLApi(`/local/client/${rut}`);

    if(validateRUT()){
        const response = __conection({
            url: url,
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function (request) {

            console.log(request);
            if (request.razon_social) {
                document.getElementById('razon_social').value = request.razon_social;
                document.getElementById('phone').value = request.phone;
                document.getElementById('direction').value = request.direction;
                document.getElementById('giro').value = request.giro;
                document.getElementById('city').value = request.city;
                document.getElementById('comuna').value = request.comuna;
                document.getElementById('giro').value = request.giro;
            }else{
                clearClientFields();
            }

        });
 
    }    

}

function clearClientFields() {
    document.getElementById('razon_social').value = '';
    document.getElementById('phone').value = '';
    document.getElementById('direction').value = '';
    document.getElementById('giro').value = '';
    document.getElementById('city').value = '';
    document.getElementById('comuna').value = '';
}

function validateForm() {
    let isValid = true;

    let requiredFields = ['rut', 'fecha_emision', 'fecha_vencimiento', 'forma_pago', 'razon_social','direction','giro','city','comuna'];
    let nroTransaccion = document.getElementById('nro_transaccion').value;

    requiredFields.forEach(function (fieldId) {
        let field = document.getElementById(fieldId);
        if (field && field.value.trim() === '') {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    if (nroTransaccion && isNaN(nroTransaccion)) {
        alert('El nÃºmero de transacciÃ³n debe ser un nÃºmero vÃ¡lido.');
        isValid = false;
    }
    if(!validateRUT()){
        isValid = false;
    }

    // If all validations pass, return true to proceed with form submission
    return isValid;
}

async function facturarPedido(){
    let app_id = id;
    let pedido_id = pedidoId;
    
    if(app_id && pedido_id && validateForm()){
        
        const rut = document.getElementById('rut').value;
        const fechaEmision = document.getElementById('fecha_emision').value;
        const fechaVencimiento = document.getElementById('fecha_vencimiento').value;
        const formaPago = document.getElementById('forma_pago').value;
        const razonSocial = document.getElementById('razon_social').value;
        const direction = document.getElementById('direction').value;
        
        // Asegurar que phone, observacion y nroTransaccion SIEMPRE sean strings
        const phoneInput = document.getElementById('phone');
        const phone = (phoneInput && phoneInput.value && phoneInput.value.trim() !== '') 
            ? String(phoneInput.value).trim() 
            : '000000000';  // Número por defecto como en la app
        
        const giro = document.getElementById('giro').value;
        const city = document.getElementById('city').value;
        const comuna = document.getElementById('comuna').value;
        
        const nroTransaccionInput = document.getElementById('nro_transaccion');
        const nroTransaccion = (nroTransaccionInput && nroTransaccionInput.value && nroTransaccionInput.value.trim() !== '') 
            ? String(nroTransaccionInput.value).trim() 
            : '0';  // 0 por defecto como en la app
        
        const observacionInput = document.getElementById('observacion');
        const observacion = (observacionInput && observacionInput.value && observacionInput.value.trim() !== '') 
            ? String(observacionInput.value).trim() 
            : 'Sin observaciones';
        
        console.log('📋 DEBUG FACTURACION - Datos a enviar:', {
            app_id: id,
            pedido_id: pedido_id,
            rut: rut,
            razon_social: razonSocial,
            phone: phone,
            phone_type: typeof phone,
            observacion: observacion,
            observacion_type: typeof observacion,
            nro_transaccion: nroTransaccion,
            nro_transaccion_type: typeof nroTransaccion
        });

        try {
            await __conection(
                {
                    url: generarURLApi(`/web/pedido/facturar`),
                    header: credentials(),
                    dev: true,
                    method: 'POST',
                },
                {
                    app_id: id,
                    pedido_id: pedido_id,

                    rut: rut,
                    razon_social : razonSocial,
                    phone : phone,  // Ya es string garantizado
                    direction : direction,
                    giro : giro,
                    city : city,
                    comuna : comuna,

                    fecha_emision:fechaEmision,
                    fecha_vencimiento:fechaVencimiento,
                    forma: formaPago,
                    observacion: observacion,  // Ya es string garantizado
                    nro_transaccion: nroTransaccion  // Ya es string garantizado
                },
                function (request) {
                    console.log('📋 Respuesta facturación completa:', request);
                    console.log('📋 dataEnviada99 (lo que recibió el backend):', request.dataEnviada99);
                    
                    // 🔍 DEBUG ULTRA DETALLADO - Ver TODAS las propiedades de la respuesta
                    console.log('🔍 ANÁLISIS COMPLETO DE RESPUESTA:');
                    console.log('  - ID:', request.id);
                    console.log('  - response_folio:', request.response_folio);
                    console.log('  - response_folio type:', typeof request.response_folio);
                    console.log('  - Todas las keys:', Object.keys(request));
                    console.log('  - JSON completo:', JSON.stringify(request, null, 2));
                    
                    // Buscar mensajes de error del backend
                    if (request.error) console.error('🚨 ERROR DEL BACKEND:', request.error);
                    if (request.message) console.log('📨 MENSAJE:', request.message);
                    if (request.sii_error) console.error('🚨 ERROR SII:', request.sii_error);
                    if (request.validation_error) console.error('🚨 ERROR VALIDACIÓN:', request.validation_error);
                    
                    // Validar que response_folio sea un string válido
                    if (request.response_folio && typeof request.response_folio === 'string' && request.response_folio.length > 0) {
                        console.log('✅ PDF recibido correctamente, generando...');
                        generatePDF(request.response_folio);
                        alert('✅ Factura generada exitosamente');
                    } else {
                        console.error('❌ Error: response_folio no es válido:', request.response_folio);
                        console.error('📋 Tipo recibido:', typeof request.response_folio);
                        
                        // Verificar si la factura se creó pero no se generó el PDF
                        if (request.id) {
                            alert('⚠️ La factura se registró correctamente (ID: ' + request.id + '), pero no se pudo generar el PDF automáticamente. Puede consultarla más tarde.');
                        } else {
                            alert('❌ Error: No se pudo generar el PDF. El servidor no devolvió un documento válido.');
                        }
                    }
                    
                    $('#clientCreate').modal('hide');
                    
                    // Recargar la lista de pedidos para reflejar los cambios
                    getPedidos();
                }
            );
    
        } catch (error) {
            console.error("Error fetching facturar pedidos:", error);
            // Handle errors appropriately
        }
    }else{
        alert('Falta un dato necesario');
    }
    
}


async function generatePDF(base64PDF) {
    try {
        // Validar que el parámetro sea un string
        if (!base64PDF || typeof base64PDF !== 'string') {
            console.error('❌ Error: base64PDF no es un string válido:', base64PDF);
            alert('Error: No se pudo generar el PDF. Datos inválidos.');
            return;
        }
        
        // Cadena base64 del PDF
        // Convertir la cadena base64 a binario
        const binary = atob(base64PDF.replace(/\s/g, ''));
        const len = binary.length;
        const buffer = new ArrayBuffer(len);
        const view = new Uint8Array(buffer);
        for (let i = 0; i < len; i++) {
            view[i] = binary.charCodeAt(i);
        }

        // Crear un blob a partir del binario
        const blob = new Blob([view], { type: 'application/pdf' });

        // Generar un UUID
        const uuid = crypto.randomUUID();

        // Nombre del archivo con UUID
        const fileName = `factura_${uuid}.pdf`;

        // Crear un enlace y abrir el PDF en una nueva pestaña
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.target = '_blank'; // Esto abrirá el PDF en una nueva pestaña
        a.download = fileName;
        a.click();

        // Revocar la URL del blob después de usarla
        window.URL.revokeObjectURL(url);
        
        console.log('✅ PDF generado exitosamente:', fileName);
    } catch (error) {
        console.error('❌ Error generando PDF:', error);
        alert('Error al generar el PDF: ' + error.message);
    }
}



async function procesarNotaCredito() {
    const fechaCancelacion = document.getElementById('fecha_cancelacion').value;
    
    if (!fechaCancelacion) {
        alert('❌ Error: Debe seleccionar una fecha de cancelación.');
        return;
    }

    // Confirmar la acción
    const confirmacion = confirm(
        `¿Estás seguro de que deseas cancelar la factura del pedido ${pedidoIdCancelar}?\n\n` +
        `Se generará una Nota de Crédito.`
    );
    
    if (!confirmacion) {
        return;
    }

    try {
        activateLoader();
        
        // Enviar como objeto normal, NO como FormData
        const datos = {
            cancelDate: fechaCancelacion
        };
        
        // NOTA DE CRÉDITO - Actualizado 2025-08-21 - V2
        await __conection(
            {
                url: generarURLApi(`/web/pedido/cancelar-factura/${pedidoIdCancelar}`),
                header: credentials(),
                dev: true,
                method: 'POST',
            },
            datos, // Objeto normal en lugar de FormData
            function (request) {
                desactivateLoader();
                console.log('Respuesta nota de crédito:', request);
                
                if (request.success) {
                    // Generar PDF si viene en la respuesta
                    if (request.response_folio) {
                        generatePDF(request.response_folio);
                    }
                    
                    alert('✅ Nota de Crédito generada exitosamente.');
                    $('#cancelarFacturaModal').modal('hide');
                    
                    // Recargar la lista de pedidos
                    getPedidos();
                } else {
                    alert('❌ Error: ' + (request.message || 'No se pudo generar la nota de crédito.'));
                }
            }
        );

    } catch (error) {
        desactivateLoader(); // Ensure loader is always deactivated
        console.error("Error al procesar nota de crédito:", error);
        alert('❌ Error de conexión al procesar la nota de crédito.');
    }
}

async function verFacturaPDF(pedidoId) {
    try {
        activateLoader();
        
        await __conection(
            {
                url: generarURLApi(`/local/sell/${pedidoId}/pdf`),
                header: credentials(),
                dev: true,
                method: 'GET',
            },
            {},
            function (request) {
                desactivateLoader();
                console.log('Respuesta PDF factura:', request);
                
                // El endpoint devuelve directamente el base64 del PDF
                if (request && typeof request === 'string' && request.startsWith('JVBERi0x')) {
                    // Es un PDF válido en base64 (comienza con JVBERi0x que es "%PDF-1" en base64)
                    generatePDF(request);
                } else if (request.success && request.data) {
                    // Formato alternativo con estructura success/data
                    generatePDF(request.data);
                } else {
                    alert('❌ Error: No se pudo obtener el PDF de la factura.');
                }
            }
        );

    } catch (error) {
        desactivateLoader(); // Ensure loader is always deactivated
        console.error("Error al obtener PDF de factura:", error);
        alert('❌ Error de conexión al obtener la factura.');
    }
}

// Función para exportar pedidos a Excel
function exportarExcel() {
    if (!id) {
        alert('❌ Error: No se ha seleccionado una sucursal.');
        return;
    }

    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    // Construir la URL con parámetros
    let url = generarURLApi(`/web/exportAppRequestsExcel?id=${id}`);
    
    if (startDate && endDate) {
        url += `&startDate=${startDate}&endDate=${endDate}`;
    } else if (startDate) {
        url += `&startDate=${startDate}`;
    } else if (endDate) {
        url += `&endDate=${endDate}`;
    }

    // Mostrar mensaje de carga
    const botonExcel = document.querySelector('button[onclick="exportarExcel()"]');
    if (!botonExcel) {
        alert('❌ Error: No se encontró el botón de exportar.');
        return;
    }

    const originalText = botonExcel.innerHTML;
    botonExcel.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
    botonExcel.disabled = true;

    // Agregar headers de autenticación y descargar
    console.log('🔗 URL Excel:', url);
    
    fetch(url, {
        method: 'GET',
        headers: credentials()
    })
    .then(response => {
        console.log('📋 Response status:', response.status);
        console.log('📋 Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`Error ${response.status}: ${response.statusText}`);
        }
        
        // Verificar que sea realmente un archivo Excel
        const contentType = response.headers.get('Content-Type');
        console.log('📄 Content-Type:', contentType);
        
        if (!contentType || !contentType.includes('spreadsheet')) {
            console.warn('⚠️ Advertencia: El contenido no parece ser un archivo Excel');
        }
        
        // Obtener nombre del archivo de los headers
        const disposition = response.headers.get('Content-Disposition');
        let filename = 'pedidos_export.xls';
        
        if (disposition) {
            console.log('📄 Content-Disposition header:', disposition);
            
            // Intentar extraer filename de diferentes formas
            if (disposition.includes('filename*=')) {
                // RFC 5987 encoded filename
                const match = disposition.match(/filename\*=UTF-8''([^;]+)/);
                if (match) {
                    filename = decodeURIComponent(match[1]);
                }
            } else if (disposition.includes('filename=')) {
                // Simple filename
                const match = disposition.match(/filename="?([^";\s]+)"?/);
                if (match) {
                    filename = match[1];
                }
            }
        } else {
            // Si no hay header, generar nombre basado en fecha
            const now = new Date();
            const dateStr = now.toISOString().slice(0, 10);
            filename = `pedidos_${dateStr}.xls`;
        }
        
        console.log('📁 Filename:', filename);
        
        return response.blob().then(blob => {
            console.log('📦 Blob size:', blob.size, 'bytes');
            console.log('📦 Blob type:', blob.type);
            return { blob, filename };
        });
    })
    .then(({ blob, filename }) => {
        // Crear URL del blob y descargar
        const blobUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(blobUrl);
        
        // Mostrar mensaje de éxito
        console.log('✅ Archivo Excel generado correctamente:', filename);
        
        // Mostrar toast de éxito si está disponible
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Archivo Excel generado y descargado correctamente.',
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            alert('✅ Archivo Excel generado y descargado correctamente.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Mostrar error más descriptivo
        let errorMessage = 'Error al generar el archivo Excel';
        if (error.message.includes('fetch')) {
            errorMessage = 'Error de conexión con el servidor';
        } else if (error.message.includes('500')) {
            errorMessage = 'Error interno del servidor';
        } else if (error.message.includes('404')) {
            errorMessage = 'Endpoint no encontrado';
        }
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage + ': ' + error.message
            });
        } else {
            alert('❌ ' + errorMessage + ': ' + error.message);
        }
    })
    .finally(() => {
        // Restaurar botón
        if (botonExcel) {
            botonExcel.innerHTML = originalText;
            botonExcel.disabled = false;
        }
    });
}

// Variable global para almacenar la información del pedido actual
let pedidoActualParaImprimir = null;
let productosOriginales = null;

// Función auxiliar para cargar imagen como base64
async function loadImageAsBase64(src) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        
        img.onload = function() {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);
            
            try {
                const base64 = canvas.toDataURL('image/png');
                console.log("✅ Imagen convertida a base64 exitosamente");
                resolve(base64);
            } catch (error) {
                console.error("❌ Error convirtiendo a base64:", error);
                reject(error);
            }
        };
        
        img.onerror = function() {
            console.error("❌ Error cargando imagen:", src);
            reject(new Error('No se pudo cargar la imagen'));
        };
        
        img.src = src + '?' + new Date().getTime(); // Cache buster
    });
}

// Función para imprimir el pedido actual en PDF con formato de Guía de Despacho FAGOTTO
async function imprimirPedidoPDF() {
    console.log("🚀 INICIANDO GENERACIÓN DE PDF");
    
    // Verificar dependencias críticas
    if (typeof window.jspdf === 'undefined') {
        console.error("❌ jsPDF no está cargado");
        alert("Error: jsPDF no está disponible");
        return;
    }
    
    try {
        // Crear el PDF usando jsPDF con tamaño A4 explícito
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('portrait', 'mm', 'a4'); // Forzar A4 portrait
        
        // Configuración de la página
        const pageWidth = pdf.internal.pageSize.width;  // 210mm para A4
        const pageHeight = pdf.internal.pageSize.height; // 297mm para A4
        const margin = 15;
        
        console.log(`📄 DIMENSIONES PDF: ${pageWidth}mm x ${pageHeight}mm`);
        
        // Obtener datos del modal actual
        const productTable = document.getElementById('table-products');
        const total = document.getElementById('total').textContent;
        const pedidoTitle = document.querySelector('#productsModalLabel').textContent;
        
        // Extraer número de pedido del título
        const numeroPedido = pedidoTitle.match(/\d+/) ? pedidoTitle.match(/\d+/)[0] : 'N/A';
        
        // ENCABEZADO - Logo profesional y título
        pdf.setFontSize(16);
        pdf.setFont("helvetica", "bold");
        
        // 🎨 LOGO PROFESIONAL FAGOTTO
        try {
            console.log("🔍 Cargando logo profesional...");
            const logoBase64 = await loadImageAsBase64('/assets/css/negro.png');
            
            // Agregar logo al PDF (posición x, y, ancho, alto)
            pdf.addImage(logoBase64, 'PNG', 15, 8, 30, 18);
            console.log("✅ Logo agregado exitosamente al PDF");
            
        } catch (error) {
            console.error("❌ Error cargando logo, usando fallback:", error);
            
            // Fallback profesional con estilo FAGOTTO
            pdf.setFillColor(0, 0, 0); // Fondo negro
            pdf.rect(15, 8, 30, 18, 'F'); // Rectángulo de fondo
            
            pdf.setTextColor(255, 255, 255); // Texto blanco
            pdf.setFontSize(14);
            pdf.setFont("helvetica", "bold");
            pdf.text("FAGOTTO", 18, 20);
            
            console.log("✅ Fallback logo aplicado");
        }
        
        // Título principal con pedido # 
        pdf.setTextColor(0, 0, 0);
        pdf.setFontSize(16);
        pdf.setFont("helvetica", "bold");
        pdf.text("Guía de despacho FAGOTTO", 50, 15);
        
        // Obtener el nombre de la franquicia de la variable global actualizada
        const franquiciaName = window.currentFranquiciaName || 'N/A';
        
        // Debug: verificar qué valor tiene la franquicia
        console.log("🔍 DEBUG PDF - Nombre de franquicia:", franquiciaName);
        console.log("🔍 DEBUG PDF - Variable global:", window.currentFranquiciaName);
        console.log("🔍 DEBUG PDF - ID actual:", id);
        
        // Pedido # y Franquicia debajo del título
        pdf.setFontSize(15);
        pdf.text(`Pedido #: ${numeroPedido}`, 50, 25);
        pdf.text(`Franquicia: ${franquiciaName}`, 50, 35);
        
        // 🚨 AGREGAR INDICADOR DE EMERGENCIA AL PDF
        let startYAdjust = 0; // Ajuste para el resto del contenido
        if (window.currentPedidoEsEmergencia) {
            pdf.setTextColor(255, 0, 0); // Rojo
            pdf.setFont("helvetica", "bold");
            pdf.setFontSize(14);
            pdf.text("🚨 PEDIDO DE EMERGENCIA - PRIORIDAD ALTA", 50, 45);
            pdf.setTextColor(0, 0, 0); // Volver a negro
            pdf.setFont("helvetica", "normal");
            startYAdjust = 10; // Mover el resto del contenido hacia abajo
        }
        
        // SECCIÓN DE TOTAL Y FECHA (COMPACTADA)
        let startY = 45 + startYAdjust;
        
        // Rectángulo para TOTAL (reemplaza Local)
        pdf.rect(margin, startY, 90, 12); // Más chico (12 en lugar de 15)
        pdf.setFontSize(15);
        pdf.setFont("helvetica", "bold");
        pdf.text("Total:", margin + 5, startY + 8);
        
        // Valor del total
        pdf.setFont("helvetica", "normal");
        const totalValue = document.getElementById('total') ? document.getElementById('total').textContent : '$0';
        pdf.text(totalValue, margin + 35, startY + 8);
        
        // Rectángulo para Fecha (bien alineado)
        pdf.rect(margin + 90, startY, 90, 12); // Más chico
        pdf.setFont("helvetica", "bold"); 
        pdf.text("Fecha:", margin + 95, startY + 8);
        
        // Fecha actual (bien posicionada)
        pdf.setFont("helvetica", "normal");
        // Usar la fecha del pedido en lugar de la fecha actual
        let fechaParaMostrar;
        if (window.currentPedidoFecha) {
            // Convertir la fecha del pedido a formato chileno
            const fechaPedido = new Date(window.currentPedidoFecha);
            fechaParaMostrar = fechaPedido.toLocaleDateString('es-CL');
            console.log("📅 Usando fecha del pedido:", window.currentPedidoFecha, "->", fechaParaMostrar);
        } else {
            // Fallback a fecha actual si no está disponible
            fechaParaMostrar = new Date().toLocaleDateString('es-CL');
            console.log("⚠️ Fecha del pedido no disponible, usando fecha actual:", fechaParaMostrar);
        }
        pdf.text(fechaParaMostrar, margin + 125, startY + 8);
        
        // TABLA DE PRODUCTOS (MÁS COMPACTA)
        startY += 15; // Menos espacio
        
        // Encabezados de tabla (más chicos)
        pdf.rect(margin, startY, 120, 10);
        pdf.rect(margin + 120, startY, 60, 10);
        
        pdf.setFontSize(15); // Texto más pequeño
        pdf.setFont("helvetica", "bold");
        pdf.text("PRODUCTO", margin + 5, startY + 7);
        pdf.text("CANT.", margin + 125, startY + 7);
        
        // Productos de la lista predefinida de FAGOTTO (sin tildes para evitar problemas)
        const productosFagotto = [
            'Bolonesa', 'Champinon', 'Camaron', 'Pesto', 'Alfredo', 'Pollo mostaza',
            'Queso', 'Huevos', 'Mezcla', 'Vasos', 'Bolsas', 'Papel M.',
            'Stickers', 'Aceite', 'F. Pesto', 'F. Aleato', 'F. Salami', 'F.Pollo / Pim'
        ];
        
        startY += 10; // Cabecera más chica
        const rowHeight = 9; // Filas más chicas
        
        // FUNCIÓN PDF - USA unidad_medida DE LA DB
        function getUnidadProducto(product) {
            console.log("🔍 PDF ANALISIS:", {
                nombre: product.name,
                cantidad: product.quantity,
                unidad_medida: product.unidad_medida,
                categoria: product.category,
                id: product.id
            });
            
            // ✅ PRIORIDAD 1: Si viene unidad_medida de la base de datos, usarla
            if (product.unidad_medida && product.unidad_medida.trim() !== '') {
                console.log("✅ PDF USANDO UNIDAD DE DB:", product.name, "->", product.quantity + ' ' + product.unidad_medida);
                return product.quantity + ' ' + product.unidad_medida;
            }
            
            // FALLBACK: Lógica anterior por si no viene unidad_medida
            // FOCACCIAS
            const esFocacciaNombre = product.name.toLowerCase().includes('focaccia') && !product.name.toLowerCase().includes('pesto');
            const esCategoria3 = product.category == 3;
            const esFocacciaID = [11, 23, 27, 28].includes(product.id);
            const esPestoSalsaPorID = product.id == 19;
            const esPestoSalsa = product.name.toLowerCase().includes('pesto') && !product.name.toLowerCase().includes('focaccia');
            
            if ((esFocacciaNombre || esCategoria3 || esFocacciaID) && !esPestoSalsaPorID && !esPestoSalsa) {
                const cantidadFinal = parseInt(product.quantity) || product.quantity;
                console.log("🥖 PDF FOCACCIA FALLBACK:", product.name, "->", cantidadFinal + ' unidades');
                return cantidadFinal + ' unidades';
            }
            
            // SALSAS FALLBACK
            if (product.id == 19) {
                return product.quantity + ' kg';
            }
            
            const esSalsa = product.name.toLowerCase().includes('bolonesa') ||
                           product.name.toLowerCase().includes('champinon') ||
                           product.name.toLowerCase().includes('alfredo') ||
                           product.name.toLowerCase().includes('mostaza');
            
            if (esSalsa && !product.name.toLowerCase().includes('focaccia')) {
                return product.quantity + ' kg';
            }
            
            // Casos específicos fallback
            if (product.name === 'Botella de Huevos 1L') return product.quantity + ' botellas';
            if (product.name === 'Aceite Vegetal 5L') return product.quantity + ' unidades';
            if (product.name === 'Pliego (124 stickers)') return product.quantity + ' Pliego';
            
            const productosUnidades = ['Vaso', 'Sandwich', 'Aceite de oliva 5kg', 'Harina', 'Bolsa', 'papel mantequilla (Focaccia)', 'Papel Mantequilla (Bandeja)'];
            if (productosUnidades.includes(product.name)) {
                return product.quantity + ' unidades';
            }
            
            // Por defecto kg
            console.log("⚡ PDF FALLBACK - Producto va en KG:", product.name);
            return product.quantity + ' kg';
        }
        
        // Usar los productos originales de la API en lugar del DOM
        const productosDelPedido = {};
        
        if (productosOriginales) {
            console.log("🔍 Productos originales para PDF:", productosOriginales);
            
            for (const key in productosOriginales) {
                const producto = productosOriginales[key];
                console.log("📦 Procesando producto:", producto);
                
                // Para producto con id=2 usar formato especial (gramos)
                let cantidadFormateada;
                if (producto.id == 2) {
                    cantidadFormateada = producto.quantity + " (" + Math.ceil(producto.price) + "gr)";
                } else {
                    cantidadFormateada = getUnidadProducto(producto);
                }
                
                productosDelPedido[producto.name.toLowerCase()] = cantidadFormateada;
                console.log("✅ Mapeado:", producto.name, "->", cantidadFormateada);
            }
        } else {
            console.warn("⚠️ No hay productos originales disponibles, intentando leer del DOM...");
            // Fallback: leer del DOM si no hay productos originales
            if (productTable) {
                const rows = productTable.querySelectorAll('tr');
                rows.forEach((row) => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length >= 3) {
                        const nombreProducto = cells[1].textContent.trim();
                        const cantidadFormateada = cells[2].textContent.trim();
                        productosDelPedido[nombreProducto.toLowerCase()] = cantidadFormateada;
                    }
                });
            }
        }
        
        // 🔥 GENERAR FILAS SOLO PARA LOS PRODUCTOS DEL PEDIDO ACTUAL
        let filaIndex = 0;
        
        if (productosOriginales && Array.isArray(productosOriginales)) {
            console.log("📋 GENERANDO PDF SOLO CON PRODUCTOS DEL PEDIDO:", productosOriginales.length);
            
            productosOriginales.forEach((producto) => {
                const y = startY + (filaIndex * rowHeight);
                
                // Rectángulos para cada fila
                pdf.rect(margin, y, 120, rowHeight);
                pdf.rect(margin + 120, y, 60, rowHeight);
                
                // Nombre del producto (texto más pequeño)
                pdf.setFont("helvetica", "normal");
                pdf.setFontSize(15);
                pdf.text(producto.name.toUpperCase(), margin + 3, y + 6);
                
                // Obtener cantidad con unidad correcta
                const cantidad = getUnidadProducto(producto);
                console.log(`📦 PDF PRODUCTO: ${producto.name} → ${cantidad}`);
                
                // Mostrar cantidad en el PDF
                pdf.setFont("helvetica", "bold");
                pdf.setFontSize(15);
                pdf.text(cantidad, margin + 125, y + 6);
                
                filaIndex++; // Incrementar índice de fila
            });
        } else {
            console.warn("⚠️ No hay productos originales para generar PDF");
        }
        
        // SECCIÓN INFERIOR - Recibe, Fecha, Hora, Firma
        const bottomY = startY + (filaIndex * rowHeight) + 20;
        
        // Verificar que no se salga de la página
        console.log(`📍 POSICIÓN INFERIOR: ${bottomY}mm (máximo: ${pageHeight - 20}mm)`);
        
        if (bottomY + 40 > pageHeight) {
            console.warn("⚠️ CONTENIDO SE SALE DE LA PÁGINA - Ajustando...");
        }
        
        // Rectángulo Recibe
        pdf.rect(margin, bottomY, 180, 15);
        pdf.setFont("helvetica", "bold");
        pdf.setFontSize(8);
        pdf.text("Recibe", margin + 5, bottomY + 10);
        pdf.line(margin + 30, bottomY + 10, margin + 170, bottomY + 10);
        
        // Rectángulo Fecha
        pdf.rect(margin, bottomY + 15, 60, 15);
        pdf.text("Fecha", margin + 5, bottomY + 25);
        pdf.text("____/____/____", margin + 25, bottomY + 25);
        
        // Rectángulo Hora  
        pdf.rect(margin + 60, bottomY + 15, 60, 15);
        pdf.text("Hora", margin + 65, bottomY + 25);
        pdf.text("____:____", margin + 85, bottomY + 25);
        
        // Rectángulo Firma
        pdf.rect(margin + 120, bottomY + 15, 60, 15);
        pdf.text("Firma", margin + 125, bottomY + 25);
        
        // FOOTER CON CRÉDITOS DE DESARROLLO 🚀
        const footerY = pageHeight - 20; // 20mm desde abajo
        
        // Texto de créditos - Solo letras centradas sin decoración
        pdf.setFont("helvetica", "normal");
        pdf.setFontSize(8);
        pdf.setTextColor(0, 0, 0); // Negro normal
        
        const creditoTexto = "";
        const desarrolladorTexto = "";
        
        // Centrar el texto en la página
        const textWidth1 = pdf.getTextWidth(creditoTexto);
        const textWidth2 = pdf.getTextWidth(desarrolladorTexto);
        const centerX = pageWidth / 2;
        
        pdf.text(creditoTexto, centerX - (textWidth1 / 2), footerY + 3);
        pdf.text(desarrolladorTexto, centerX - (textWidth2 / 2), footerY + 8);
        
        console.log("📝 FOOTER LIMPIO: Solo texto centrado");
        
        // Guardar el PDF
        const nombreArchivo = `GuiaDespacho_Pedido_${numeroPedido}_${new Date().getTime()}.pdf`;
        console.log("✅ PDF GENERADO EXITOSAMENTE:", nombreArchivo);
        pdf.save(nombreArchivo);
        
        // Mostrar mensaje de éxito
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '¡Guía de Despacho Generada!',
                text: `PDF generado: ${nombreArchivo}`,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            alert(`✅ Guía de Despacho generada: ${nombreArchivo}`);
        }
        
    } catch (error) {
        console.error('Error al generar PDF:', error);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al generar la Guía de Despacho: ' + error.message
            });
        } else {
            alert('❌ Error al generar la Guía de Despacho: ' + error.message);
        }
    }
}
