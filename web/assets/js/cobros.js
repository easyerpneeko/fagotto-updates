$(document).ready(function () {
    // _name.text(_store().session.user().get("username")) //obtenemos el nombre del usuario
    loadSucursalesList();
    getCobros(1);
    getDenominaciones(1);
})

//Variables
const urlParams = new URLSearchParams(window.location.search);
const id = urlParams.get('id');
let apps = []; // Array para almacenar los datos de id y name
let DenominacionesJSON = [];
let denominaciones = [];

async function loadSucursalesList() {
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
                        // pedidosNew: app.pedidosNew
                    };
                });
            }
        );

    } catch (error) {
        console.error("Error fetching apps:", error);
        // Handle errors appropriately
    }

}

function openNewDenominacionModal() {
    $('#newDenominacionModal').modal('show');
}

function openNewCobroModal() {

    let select_sucursales = document.getElementById('select-sucursales');
    let fila = "";

    for (let index in apps) {

        fila += `<option value="${apps[index].id}">${apps[index].name}</option>`;
    }
    select_sucursales.innerHTML = fila;

    // _________________________________________________________________________________
    let select_denominaciones = document.getElementById('select-denominaciones');
    let option = "";

    for (let index in denominaciones) {

        option += `<option value="${denominaciones[index].id}">${denominaciones[index].description}</option>`;
    }

    select_denominaciones.innerHTML = option;

    $('#newCobroModal').modal('show');
}

async function getDenominaciones(page) {
    var params = `?params=true&page=${page || 1}`;

    var url = generarURLApi(`/web/getDenominaciones${params}`);

    await __conection({
        url: url,
        header: credentials(),
        dev: true,
        method: 'GET'

    }, {}, function (request) {
        console.log("Apps denominaciones:", request);
        let app = request;

        denominaciones = app.items;
    });
}

async function openDenominaciones(page) {

    const tablasContainer = document.getElementById('tablas-container');
    tablasContainer.innerHTML = "";

    if (denominaciones.length > 0) {
        let fila = `<div class="table-responsive"  style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Descripcion</th>
                                                <th scope="col">Monto</th>
                                                <th scope="col">%</th>
                                                <th scope="col">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody> `;

        for (const denominacion in denominaciones) {
            fila += `<tr>
                                <td>${denominaciones[denominacion].id}</td>
                                <td>${denominaciones[denominacion].description}</td>
                                <td>$ ${denominaciones[denominacion].amount}</td>
                                <td>${denominaciones[denominacion].percentage}</td>
                                <td>
                                    <button class='btn btn-danger' onclick="deleteDenominacion(${denominaciones[denominacion].id})">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>`;

        }
        fila += `</tbody>
                        </table>
                    </div>`

        tablasContainer.innerHTML += fila;
    } else {
        const tablasContainer = document.getElementById('tablas-container');
        tablasContainer.innerHTML = "NO HAY COBROS";
    }
    // Lógica de paginación
    // const totalPages = Math.ceil(apps.length / itemsPerPage);
    let paginationHtml = '<ul class="pagination d-flex col-12 justify-content-center">';
    currentPage = page;
    // Crear los controles de paginación
    for (let i = 1; i <= 10; i++) {
        paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" onclick="getDenominaciones(${i})">${i}</a></li>`;
    }
    paginationHtml += '</ul>';

    // Mostrar los controles de paginación en la página
    document.getElementById('pagination').innerHTML = paginationHtml;

    $('#denominacionesModal').modal('show');
}

async function newDenominacion() {
    console.log('denominacion');
    let description = document.getElementById('description').value;
    let percentage = document.getElementById('percentage').value;
    let amount = document.getElementById('amount').value;

    let formDenominaciones = true;

    if(validateForm(formDenominaciones)){
        try {
            await __conection(
                {
                    url: generarURLApi(`/web/newDenominacion`),
                    header: credentials(),
                    dev: true,
                    method: 'POST',
                },
                {
                    description: description,
                    percentage: percentage,
                    amount: amount
                },
                function (request) {
                    getDenominaciones(1);
                    document.getElementById('description').value = "";
                    percentage = document.getElementById('percentage').value = 1;
                    document.getElementById('amount').value = 1;
    
                    $('#newDenominacionModal').modal('hide');
    
    
                }
            );
    
        } catch (error) {
            console.error("Error fetching cobros:", error);
            // Handle errors appropriately
        }
    }
    
}

function addItemCobro() {
    console.log('add');

    var selectElement = document.getElementById('select-denominaciones');
    var selectedValue = selectElement.options[selectElement.selectedIndex].value;

    console.log(selectedValue); // Esto imprimirá el valor seleccionado en la consola
    console.log(denominaciones); // Esto imprimirá el valor seleccionado en la consola

    // Busca el elemento en el array
    var encontrado = DenominacionesJSON.find(elemento => elemento.id == selectedValue);

    // Si el elemento existe, incrementa la cantidad, si no, agrégalo al array
    if (encontrado) {
        encontrado.cantidad = (encontrado.cantidad || 1) + 1; // Incrementa la cantidad
    } else {
        // Si no existe, búscalo en el array denominaciones para agregarlo con cantidad = 1
        var nuevoElemento = denominaciones.find(elemento => elemento.id == selectedValue);
        if (nuevoElemento) {
            nuevoElemento.cantidad = 1;
            DenominacionesJSON.push(nuevoElemento);
        }
    }

    updateTableItems();
    // console.log(DenominacionesJSON);
}

function updateTableItems() {
    let tabla_items = document.getElementById('body-table-items');
    tabla_items.innerHTML = "";
    let fila = "";

    for (let index in DenominacionesJSON) {

        fila += `<tr>
                    <th scope="row">${parseInt(index) + 1}</th>
                    <td>${DenominacionesJSON[index].description || ''}</td>
                    <td>${DenominacionesJSON[index].percentage || ''}</td>
                    <td>${DenominacionesJSON[index].amount || ''}</td>
                    <td>
                        <button class='btn btn-danger' onclick="removeItemCobro(${DenominacionesJSON[index].id})">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                 </tr>`;
    }

    tabla_items.innerHTML = fila;
}

async function newCobro() {
    console.log('new cobro');

    let rut_emisor = document.getElementById('rut_emisor').value;
    let rut_receptor = document.getElementById('rut_receptor').value;
    let date_pago = document.getElementById('date_pago').value;
    let date_vencimiento = document.getElementById('date_vencimiento').value;

    let select_sucursales = document.getElementById('select-sucursales');
    let appId = select_sucursales.options[select_sucursales.selectedIndex].value;

    let select_paymode = document.getElementById('select-forma-pago');
    let paymode = select_paymode.options[select_paymode.selectedIndex].value;
    
    let direccion = document.getElementById('direccion').value;
    
    let total_ventas = document.getElementById('total_ventas').value;

    let denominaciones = JSON.stringify(DenominacionesJSON);

    if (validateForm()) {
        try {
            await __conection(
                {
                    url: generarURLApi(`/web/newCobro`),
                    header: credentials(),
                    dev: true,
                    method: 'POST',
                },
                {
                    rut_emisor: rut_emisor,
                    rut_receptor: rut_receptor,
                    date_pago: date_pago,
                    date_vencimiento: date_vencimiento,
                    direccion: direccion,
                    paymode: paymode,
                    total_ventas:total_ventas,
                    appId: appId,
                    denominaciones: denominaciones
                },
                function (request) {
                    // getDenominaciones();

                    document.getElementById('rut_emisor').value = "";
                    document.getElementById('rut_receptor').value = "";
                    document.getElementById('date_pago').value = "";
                    document.getElementById('date_vencimiento').value;
                    document.getElementById('direccion').value="";
                    document.getElementById('total_ventas').value = "";

                    DenominacionesJSON = [];

                    updateTableItems();

                    $('#newCobroModal').modal('hide');

                    getCobros();

                }
            );

        } catch (error) {
            console.error("Error fetching cobros:", error);
            // Handle errors appropriately
        }
    } else {
        
    }
}

function removeItemCobro(value) {
    console.log('remove');

    // Busca el índice del elemento en el array
    var index = DenominacionesJSON.findIndex(elemento => elemento.id == value);

    // Si el elemento existe, elimínalo del array
    if (index !== -1) {
        DenominacionesJSON.splice(index, 1);
    }

    updateTableItems();
    // console.log(DenominacionesJSON);
}

async function deleteDenominacion(id) {

    try {
        await __conection(
            {
                url: generarURLApi(`/web/denominacion/${id}/delete`),
                header: credentials(),
                dev: true,
                method: 'GET',
            },
            {},
            function (request) {
                $('#denominacionesModal').modal('hide');
                getDenominaciones(1);
            }
        );

    } catch (error) {
        console.error("Error fetching cobros:", error);
        // Handle errors appropriately
    }
}

async function getCobros(page) {

    var params = `?params=true&page=${page || 1}`;
    
    var url = generarURLApi(`/web/getCobros${params}`);

    await __conection({
        url: url,
        header: credentials(),
        dev: true,
        method: 'GET'

    }, {}, function (request) {
        console.log("Apps cobros:", request);
        let app = request;

        const tablasContainer = document.getElementById('tablas-cobros-container');
        tablasContainer.innerHTML = "";

        cobros = app.items;

        if (cobros.length > 0) {
            let fila = `<div class="table-responsive">
                                <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Estado</th>
                                                <th scope="col">Emisor</th>
                                                <th scope="col">Receptor</th>
                                                <th scope="col">Total</th>
                                                <th scope="col">Descargar</th>
                                            </tr>
                                        </thead>
                                        <tbody> `;

            for (const cobro in cobros) {
                fila += `<tr>   
                                <td scope="col">${cobros[cobro].id}</td>
                                <td>${cobros[cobro].status}</td>
                                <td>${cobros[cobro].rut_emisor}</td>
                                <td>${cobros[cobro].rut_receptor}</td>
                                <td>$ ${cobros[cobro].total}</td>
                                <td>
                                    <button class='btn btn-danger' onclick="descargarPDF(${cobros[cobro].id})">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </button>
                                </td>
                            </tr>`;

            }
            fila += `</tbody>
                        </table>
                    </div>`

            tablasContainer.innerHTML += fila;
        } else {
            const tablasContainer = document.getElementById('tablas-cobros-container');
            tablasContainer.innerHTML = "NO HAY COBROS";
        }
        // Lógica de paginación
        // const totalPages = Math.ceil(apps.length / itemsPerPage);
        let paginationHtml = '<ul class="pagination d-flex col-12 justify-content-center">';
        currentPage = page;
        // Crear los controles de paginación
        for (let i = 1; i <= 10; i++) {
            paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" onclick="getCobros(${i})">${i}</a></li>`;
        }
        paginationHtml += '</ul>';

        // Mostrar los controles de paginación en la página
        document.getElementById('pagination-cobros').innerHTML = paginationHtml;
    });
}

async function descargarPDF(id) {
    console.log('id', id);
    await __conection_blob({
        url: generarURLApi(`/web/cobro/${id}/download`),
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (response) {
        // Crear un blob a partir de la respuesta
        const blob = new Blob([response], { type: 'application/pdf' });
        // Crear una URL para el blob
        const url = window.URL.createObjectURL(blob);
        // Crear un enlace y simular un clic para abrir el PDF en una nueva pestaña
        const a = document.createElement('a');
        a.href = url;
        a.target = '_blank'; // Esto abrirá el PDF en una nueva pestaña
        a.click();
        // Revocar la URL del blob después de usarla
        window.URL.revokeObjectURL(url);
    });
}

function validateForm(formD = false) {
    let isValid = true;

    if (!formD) {
        var requiredFields = ['select-sucursales', 'rut_emisor', 'rut_receptor', 'date_pago', 'date_vencimiento','direccion','total_ventas'];
        requiredFields.forEach(function (fieldId) {
            var field = document.getElementById(fieldId);
            if (field && field.value.trim() === '') {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        let itemsTable = document.getElementById('body-table-items');
        if (itemsTable && itemsTable.rows.length === 0) {
            alert('Debe agregar al menos un item.');
            isValid = false;
        }

        return isValid;
    } else {
        var requiredFields = ['description'];
        requiredFields.forEach(function (fieldId) {
            var field = document.getElementById(fieldId);
            if (field && field.value.trim() === '') {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        let amount = document.getElementById('amount');
        if (!amount.value || amount.value.trim() === '' || parseFloat(amount.value) <= 0) {
            amount.classList.add('is-invalid');
            isValid = false;
        } else {
            amount.classList.remove('is-invalid');
        }

        return isValid;
    }
}
