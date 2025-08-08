$(document).ready(function () {
    // _name.text(_store().session.user().get("username")) //obtenemos el nombre del usuario
    getProducts();
    loadSucursalesList();
    llenarSelectCategorias();
})

const app_name = document.getElementById('app-name');
var startDateValue;
var endDateValue;
const urlParams = new URLSearchParams(window.location.search);
const id = urlParams.get('id');

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

async function getProducts(startDate = null, endDate = null, page) {
    if (id) {
        var params = `?params=true&page=${page || 1}`;
        var url = "";
        
        var category = document.getElementById('category').value;
        console.log(category);

        if (category != "null") {
            url = generarURLApi(`/web/getAppProducts${params}&id=${id}&categoryOfProduct=${category}`);
        } else {
            url = generarURLApi(`/web/getAppProducts${params}&id=${id}`);
        }

        await __conection({
            url: url,
            header: credentials(),
            dev: true,
            method: 'GET'

        }, {}, function (request) {
            console.log("Apps products:", request);
            let apps = request;

            const tablasContainer = document.getElementById('tablas-container');
            tablasContainer.innerHTML = "";
            
            
            
            for (const app in apps) {
                // console.log(apps[app].original);
                let products = apps[app].original.items;

                if (products.length > 0) {
                    let fila = `<div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Nombre</th>
                                                    <th scope="col">Precio</th>
                                                    <th scope="col">Categoria</th>
                                                    <th scope="col">Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody> `;

                    for (const index in products) {

                        fila += `<tr>
                                    <td><b>${products[index].id}</b></td>
                                    <td>${products[index].name}</td>
                                    <td>${products[index].price}</td>
                                    <td>${products[index].category_name}</td>
                                    <td>${products[index].stock}</td>
                                </tr>`;
                    }
                    fila += `</tbody>
                            </table>
                        </div>`

                    tablasContainer.innerHTML += fila;
                } else {
                    const tablasContainer = document.getElementById('tablas-container');
                    tablasContainer.innerHTML = "NO HAY PRODUCTOS";
                }
                // Lógica de paginación
                // const totalPages = Math.ceil(apps.length / itemsPerPage);
                let paginationHtml = '<ul class="pagination d-flex col-12 justify-content-center">';
                currentPage = page;
                // Crear los controles de paginación
                for (let i = 1; i <= 10; i++) {
                    paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" onclick="getProducts('${startDate}', '${endDate}', ${i})">${i}</a></li>`;
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
                            <a href="/pages/stock_products.html?id=${app.id}" type="button" class="nav-link d-inline position-relative">
                                ${app.name} 
                                <span class="position-absolute start-100 translate-middle badge rounded-pill bg-danger">
                                   ${app.pedidosNew} 
                                </span>
                            </a>  
                        </li>`;
        } else {
            fila = `  <li class="nav-item list-group-item">
                            <a href="/pages/stock_products.html?id=${app.id}" type="button" class="nav-link d-inline position-relative">
                                ${app.name}
                            </a>  
                        </li>`;
        }

        sucursalesList.innerHTML += fila;
    });
}

async function llenarSelectCategorias(productos, idSelect = 'category') {
    var params = `?params=true`;
    let categorias;

    // Obtener el elemento select
    let categorySelect = document.getElementById('category');

    // Verificar si el elemento select existe
    if (!categorySelect) {
        console.error(`No se encontró el elemento select con el ID: ${idSelect}`);
        return;
    }

    try {
        await __conection(
            {
                url: generarURLApi(`/web/getAppCategories${params}&id=${id}`),
                header: credentials(),
                dev: true,
                method: 'GET'
            },
            {},
            function (request) {
                let apps = request;

                for (const app in apps) {
                    let categorias = apps[app].original;

                    if (categorias && categorias.length > 0) {
                        let fragmento = document.createDocumentFragment(); // Usar un fragmento para mejor rendimiento

                        for (const index in categorias) {
                            let option = document.createElement('option');
                            option.value = categorias[index].id;
                            option.textContent = categorias[index].name;
                            option.classList.add('text-capitalize');
                            fragmento.appendChild(option);
                        }

                        categorySelect.appendChild(fragmento); // Agregar todas las opciones al select al final
                    }
                }
            }
        );

    } catch (error) {
        console.error("Error fetching apps:", error);
        // Manejar los errores apropiadamente
    }
}