$(document).ready(function () {
    // _name.text(_store().session.user().get("username")) //obtenemos el nombre del usuario
    getProducts();
})

async function getData(startDate, endDate) {
    activateLoader();
    await getProducts();
    desactivateLoader();
}

let productIdToUpdate = null;
let currentStock = 0;

function openModalAddStock(productId, stock) {
    productIdToUpdate = productId;
    currentStock = stock;
    $('#addStock').modal('show');
    document.getElementById('stock_added').value = "";
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
        let products = request;
        const tablasContainer = document.getElementById('tablas-container');
        tablasContainer.innerHTML = "";
        let i = 1;

        if (products.length > 0) {
            let fila = `<div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Bolsas</th>
                                        <th scope="col">Vasos</th>
                                        <th scope="col">Agregar Stock</th>
                                    </tr>
                                </thead>
                                <tbody>`;

            for (const index in products) {
                if (products[index].category == 2) {
                    fila += `<tr>
                                <td><b>${i}</b></td>
                                <td><b>${products[index].name}</b></td>
                                <td><b>${products[index].stock}</b></td>
                                <td><b>${products[index].vasos * products[index].stock}</b></td>
                                <td>
                                    <button class='btn btn-success' onclick="openModalAddStock(${products[index].id}, ${products[index].stock})" data-stock="${products[index].stock}"><i class="fa-solid fa-pencil"></i></button>
                                </td>
                            </tr>`;
                    i++;
                }
                
            }
            fila += `</tbody>
                        </table>
                    </div>`;

            tablasContainer.innerHTML += fila;

        } else {
            tablasContainer.innerHTML = "NO HAY PRODUCTOS";
        }
    });
}

async function addStock() {
    if (productIdToUpdate === null) {
        alert('No se ha seleccionado ningún producto.');
        return;
    }

    const stockAddedInput = document.getElementById('stock_added');
    const stockAddedValue = parseInt(stockAddedInput.value, 10);

    if (isNaN(stockAddedValue) || stockAddedValue < 1) {
        alert('Por favor, ingresa un número válido y positivo.');
        document.getElementById('stock_added').value = "";
        return;
    }

    try {
        const response = await __conection(
            {
                url: generarURLApi(`/local/products/updateStock/${productIdToUpdate}`),
                header: credentials(),
                dev: true,
                method: 'POST',
            },
            {
                stock_added: stockAddedValue
            },
            function (request) {
                console.log(request.message);
                alert(request.message);
                getProducts();
                $('#addStock').modal('hide');
            }
        );

    } catch (error) {
        console.error("Error fetching cobros:", error);
    }
}