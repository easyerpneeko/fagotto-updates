$(document).ready(function () {
    // _name.text(_store().session.user().get("username")) //obtenemos el nombre del usuario
    getPedidos();
    loadSucursalesList();
})

const app_name = document.getElementById('app-name');
var startDateValue;
var endDateValue;
const urlParams = new URLSearchParams(window.location.search);
const id = urlParams.get('id');
const waiters = [];


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
        app_name.innerHTML = request[0].name;
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
    if (id) {
        var params = `?params=true&page=${page || 1}`;
        var url = "";
        if (startDate && endDate) {
            url = generarURLApi(`/web/getAppRequests${params}&id=${id}&startDate=${startDate}&endDate=${endDate}`);
        } else {
            url = generarURLApi(`/web/getAppRequests${params}&id=${id}`);
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
                        if (pedidos[pedido].print != 1) {
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
                    const tablasContainer = document.getElementById('tablas-container');
                    tablasContainer.innerHTML = "NO HAY PEDIDOS";
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

        const products = JSON.parse(pedido.products);
        console.log(products);
        const productsModal = document.getElementById('productsModal')

        // const button = event.relatedTarget

        const modalTitle = productsModal.querySelector('.modal-title')


        modalTitle.textContent = `Pedido # - ${id}`;

        const tablaProducts = document.getElementById('table-products');
        tablaProducts.innerHTML = '';
        let i = 1;
        for (const product in products) {
            
            let fila = "";

            // FunciÃ³n para determinar la unidad correcta
            function getUnidadProducto(product) {
                // Casos especÃ­ficos con sus unidades
                if (product.name === 'Botella de Huevos 1L') {
                    return product.quantity + ' botellas';
                }
                if (product.name === 'Aceite Vegetal 5L') {
                    return product.quantity + ' unidades';
                }
                if (product.name === 'Pliego (124 stickers)') {
                    return product.quantity + ' Pliego';
                }
                
                // Productos que NO van en kg (van en unidades)
                const productosUnidades = [
                    'Vaso', 'Sandwich', 'Aceite de oliva 5kg', 'Harina', 'Bolsa',
                    'Focaccia Salame', 'Focaccia Pesto', 'Focaccia alleato', 
                    'Focaccia Pollo Pimenton', 'papel mantequilla (Focaccia)', 
                    'Papel Mantequilla (Bandeja)'
                ];
                
                if (productosUnidades.includes(product.name)) {
                    return product.quantity + ' unidades';
                }
                
                // Productos que van en kg
                return product.quantity + ' kg';
            }

            if(products[product].id == 2){
                 fila = `<tr>
                                <td>${i}</td> 
                                <td>${products[product].name.toUpperCase()}</td> 
                                <td>${products[product].quantity+" ("+Math.ceil(products[product].price)+"gr"+")"}</td>  
                        </tr>`;
            }else{
                fila = `<tr>
                    <td>${i}</td> 
                    <td>${products[product].name.toUpperCase()}</td> 
                    <td>${getUnidadProducto(products[product])}</td>  
                </tr>`;
            }
            


            tablaProducts.innerHTML += fila;
            i++;
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
                                <div class="fw-bold">ReseÃ±a</div>
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
                            <a href="/pages/pedidos.html?id=${app.id}" type="button" class="nav-link d-inline position-relative">
                                ${app.name} 
                                <span class="position-absolute start-100 translate-middle badge rounded-pill bg-danger">
                                   ${app.pedidosNew} 
                                </span>
                            </a>  
                        </li>`;
        } else {
            fila = `  <li class="nav-item list-group-item">
                            <a href="/pages/pedidos.html?id=${app.id}" type="button" class="nav-link d-inline position-relative">
                                ${app.name}
                            </a>  
                        </li>`;
        }

        sucursalesList.innerHTML += fila;
    });
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
        const phone = document.getElementById('phone').value;
        const giro = document.getElementById('giro').value;
        const city = document.getElementById('city').value;
        const comuna = document.getElementById('comuna').value;
        const nroTransaccion = document.getElementById('nro_transaccion').value;
        const observacion = document.getElementById('observacion').value;

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
                    phone : phone,
                    direction : direction,
                    giro : giro,
                    city : city,
                    comuna : comuna,

                    fecha_emision:fechaEmision,
                    fecha_vencimiento:fechaVencimiento,
                    forma: formaPago,
                    observacion: observacion,
                    nro_transaccion: nroTransaccion
                },
                function (request) {
                    console.log(request);
                    generatePDF(request.response_folio);
                    $('#clientCreate').modal('hide');
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

    // Crear un enlace y abrir el PDF en una nueva pestaÃ±a
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.target = '_blank'; // Esto abrirÃ¡ el PDF en una nueva pestaÃ±a
    a.download = fileName;
    a.click();

    // Revocar la URL del blob despuÃ©s de usarla
    window.URL.revokeObjectURL(url);
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
