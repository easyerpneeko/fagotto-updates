$(document).ready(function () {
    // _name.text(_store().session.user().get("username")) //obtenemos el nombre del usuario
    getProducts();
    getStockHistory();
})

// Mapeo de salsas a iconos
function getSauceIcon(productName) {
    const name = productName.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''); // Normaliza y quita tildes
    
    // Iconos específicos por tipo de salsa
    if (name.includes('pesto')) return '<i class="fas fa-leaf text-success"></i>';
    if (name.includes('tomate') || name.includes('pomodoro')) return '<i class="fas fa-tomato text-danger"></i>';
    if (name.includes('alfredo')) return '<i class="fas fa-cheese text-warning"></i>';
    if (name.includes('bolognesa') || name.includes('bolonesa') || name.includes('carne')) return '<i class="fas fa-drumstick-bite text-danger"></i>';
    if (name.includes('carbonara')) return '<i class="fas fa-bacon text-warning"></i>';
    if (name.includes('arrabiata') || name.includes('picante')) return '<i class="fas fa-fire text-danger"></i>';
    if (name.includes('aglio') || name.includes('ajo')) return '<i class="fas fa-garlic text-warning"></i>';
    if (name.includes('champinon') || name.includes('champi') || name.includes('funghi') || name.includes('hongo')) return '<i class="fas fa-seedling text-warning"></i>';
    if (name.includes('pollo') || name.includes('chicken')) return '<i class="fas fa-drumstick-bite text-warning"></i>';
    if (name.includes('crema') && !name.includes('pollo')) return '<i class="fas fa-cheese text-warning"></i>';
    if (name.includes('quattro') || name.includes('queso')) return '<i class="fas fa-cheese text-warning"></i>';
    if (name.includes('marinara') || name.includes('marisco')) return '<i class="fas fa-fish text-info"></i>';
    if (name.includes('camaron') || name.includes('gamba')) return '<i class="fas fa-shrimp text-danger"></i>';
    if (name.includes('rosada') || name.includes('rosa')) return '<i class="fas fa-heart text-danger"></i>';
    if (name.includes('napolitana')) return '<i class="fas fa-pizza-slice text-danger"></i>';
    if (name.includes('puttanesca')) return '<i class="fas fa-olive text-success"></i>';
    if (name.includes('vongole') || name.includes('almeja')) return '<i class="fas fa-fish text-info"></i>';
    if (name.includes('amatriciana')) return '<i class="fas fa-bacon text-danger"></i>';
    
    // Icono por defecto
    return '<i class="fas fa-pepper-hot text-danger"></i>';
}

async function getData(startDate, endDate) {
    activateLoader();
    await getProducts();
    await getStockHistory();
    desactivateLoader();
}

let productIdToUpdate = null;
let currentStock = 0;
let productNameToUpdate = '';

function openModalAddStock(productId, stock, productName = '') {
    productIdToUpdate = productId;
    currentStock = stock;
    productNameToUpdate = productName;
    
    // Actualizar displays de stock actual
    document.getElementById('current-stock-display').textContent = stock;
    document.getElementById('current-stock-display2').textContent = stock;
    
    // Limpiar inputs
    document.getElementById('stock_added').value = "";
    document.getElementById('stock_set').value = stock;
    
    // Activar la pestaña de agregar por defecto
    document.getElementById('add-tab').click();
    
    $('#addStock').modal('show');
}

async function getProducts() {
    var url = "";
    url = generarURLApi(`/local/pedidofinal/stock`);

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
                            <table class="stock-table table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 5%;">#</th>
                                        <th scope="col" style="width: 25%;">
                                            <i class="fas fa-box-open"></i> Producto
                                        </th>
                                        <th scope="col" style="width: 12%;">
                                            <i class="fas fa-boxes"></i> Stock (Bolsas)
                                        </th>
                                        <th scope="col" style="width: 12%;">
                                            <i class="fas fa-weight"></i> Kilos Totales
                                        </th>
                                        <th scope="col" style="width: 12%;">
                                            <i class="fas fa-balance-scale"></i> Unidad
                                        </th>
                                        <th scope="col" style="width: 12%;">
                                            <i class="fas fa-dollar-sign"></i> Precio
                                        </th>
                                        <th scope="col" style="width: 12%;" class="text-center">
                                            <i class="fas fa-edit"></i> Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>`;

            for (const index in products) {
                const stock = products[index].stock || 0;
                const unidad = products[index].unidad_medida || 'unidad';
                const precio = products[index].precio ? `$${parseFloat(products[index].precio).toLocaleString('es-CL')}` : 'N/A';
                const kilosTotales = products[index].kilos_totales;
                const unidadVenta = products[index].unidad_venta || 0;
                const categoria = (products[index].categoria || '').toLowerCase();
                const nombreProducto = (products[index].producto || '').toLowerCase();
                
                // Verificar si es una salsa (por categoría o nombre)
                const esSalsa = categoria.includes('salsa') || nombreProducto.includes('salsa');
                
                // Determinar color del badge según nivel de stock
                let stockClass = 'success';
                if (stock <= 5) stockClass = 'danger';
                else if (stock <= 20) stockClass = 'warning';
                
                // Mostrar kilos totales SOLO si es salsa y tiene kilosTotales
                let kilosDisplay = '';
                if (esSalsa && kilosTotales) {
                    kilosDisplay = `<span class="badge bg-info fs-6">
                                        <i class="fas fa-weight-hanging"></i> ${kilosTotales.toLocaleString('es-CL')} kg
                                    </span>
                                    <br>
                                    <small class="text-muted">(${stock} x ${unidadVenta}kg)</small>`;
                } else {
                    kilosDisplay = '<span class="text-muted">N/A</span>';
                }
                
                fila += `<tr>
                            <td class="text-muted"><b>${i}</b></td>
                            <td class="product-name">
                                <i class="fas fa-box text-primary"></i>
                                <span class="ms-2"><strong>${products[index].producto}</strong></span>
                            </td>
                            <td>
                                <span class="badge bg-${stockClass} fs-6">
                                    ${stock} bolsas
                                </span>
                            </td>
                            <td>
                                ${kilosDisplay}
                            </td>
                            <td>
                                <span class="text-muted">
                                    <i class="fas fa-ruler"></i> ${unidad}
                                </span>
                            </td>
                            <td>
                                <span class="text-success fw-bold">${precio}</span>
                            </td>
                            <td class="text-center">
                                <button class='btn btn-edit-stock btn-sm' 
                                        onclick="openModalAddStock(${products[index].id}, ${stock}, '${products[index].producto.replace(/'/g, "\\'")}')" 
                                        data-stock="${stock}">
                                    <i class="fa-solid fa-edit"></i> Editar
                                </button>
                            </td>
                        </tr>`;
                i++;
            }
            fila += `</tbody>
                        </table>
                    </div>`;

            tablasContainer.innerHTML += fila;

        } else {
            tablasContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>No hay productos disponibles</h4>
                    <p>No se encontraron productos de categoría salsas en el inventario</p>
                </div>
            `;
        }
    });
}

async function saveStock() {
    if (productIdToUpdate === null) {
        alert('No se ha seleccionado ningún producto.');
        return;
    }

    // Determinar si estamos en modo agregar o establecer
    const addTab = document.getElementById('add-tab');
    const isAddMode = addTab.classList.contains('active');

    let stockValue;
    let stockToAdd;
    let newTotalStock;

    if (isAddMode) {
        // Modo agregar
        const stockAddedInput = document.getElementById('stock_added');
        stockValue = parseInt(stockAddedInput.value, 10);

        if (isNaN(stockValue) || stockValue < 1) {
            alert('Por favor, ingresa un número válido y positivo.');
            document.getElementById('stock_added').value = "";
            return;
        }
        stockToAdd = stockValue;
        newTotalStock = currentStock + stockValue;
    } else {
        // Modo establecer
        const stockSetInput = document.getElementById('stock_set');
        const newStockValue = parseInt(stockSetInput.value, 10);

        if (isNaN(newStockValue) || newStockValue < 0) {
            alert('Por favor, ingresa un número válido (0 o mayor).');
            return;
        }

        // Calcular la diferencia para agregar/restar
        stockToAdd = newStockValue - currentStock;
        newTotalStock = newStockValue;
        
        // Confirmación si va a reducir stock
        if (stockToAdd < 0) {
            const confirmar = confirm(`¿Estás seguro de reducir el stock de ${currentStock} a ${newStockValue} bolsas?\nSe restará ${Math.abs(stockToAdd)} bolsas.`);
            if (!confirmar) {
                return;
            }
        }
    }

    console.log('Actualizando stock:', {
        productId: productIdToUpdate,
        currentStock: currentStock,
        stockToAdd: stockToAdd,
        newTotal: newTotalStock,
        mode: isAddMode ? 'agregar' : 'establecer'
    });

    try {
        const response = await __conection(
            {
                url: generarURLApi(`/local/pedidofinal/stock/${productIdToUpdate}`),
                header: credentials(),
                dev: true,
                method: 'POST',
            },
            {
                _method: 'PUT',
                stock: isAddMode ? stockToAdd : newTotalStock,
                tipo: isAddMode ? 'agregar' : 'establecer',
                user: _store().session.user().get("username") || 'Usuario'
            },
            function (request) {
                console.log('Respuesta del servidor:', request);
                
                // Si hay error, mostrar detalle
                if (request.error) {
                    console.error('Error de validación:', request.message);
                    alert('Error: ' + request.error + '\nDetalles: ' + JSON.stringify(request.message));
                    return;
                }
                
                alert(request.message || 'Stock actualizado correctamente');
                getProducts();
                getStockHistory();
                $('#addStock').modal('hide');
            }
        );

    } catch (error) {
        console.error("Error actualizando stock:", error);
        alert('Error al actualizar el stock. Por favor verifica tu conexión e intenta nuevamente.');
    }
}

async function getStockHistory() {
    const url = generarURLApi(`/local/pedidofinal/stock/history`);

    await __conection({
        url: url,
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        let changes = request;
        const historyContainer = document.getElementById('history-container');
        historyContainer.innerHTML = "";

        if (changes.length > 0) {
            let tabla = `<div class="table-responsive">
                            <table class="history-table table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Fecha y Hora</th>
                                        <th style="width: 25%;">Producto</th>
                                        <th style="width: 15%;">Usuario</th>
                                        <th style="width: 12%;">Anterior</th>
                                        <th style="width: 12%;">Nuevo</th>
                                        <th style="width: 16%;">Cambio</th>
                                    </tr>
                                </thead>
                                <tbody>`;

            for (const change of changes) {
                const date = new Date(change.fecha);
                const formattedDate = date.toLocaleString('es-CL', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const difference = change.stock_agregado;
                let changeBadge = '';
                let changeIcon = '';
                let changeClass = '';

                if (difference > 0) {
                    changeBadge = `+${difference}`;
                    changeIcon = '<i class="fas fa-arrow-up"></i>';
                    changeClass = 'increase';
                } else if (difference < 0) {
                    changeBadge = `${difference}`;
                    changeIcon = '<i class="fas fa-arrow-down"></i>';
                    changeClass = 'decrease';
                } else {
                    changeBadge = '0';
                    changeIcon = '<i class="fas fa-equals"></i>';
                    changeClass = 'set';
                }

                tabla += `<tr>
                            <td class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                ${formattedDate}
                            </td>
                            <td>
                                <strong>${change.producto_nombre}</strong>
                            </td>
                            <td>
                                <span class="user-badge">
                                    <i class="fas fa-user"></i>
                                    ${change.usuario || 'Sistema'}
                                </span>
                            </td>
                            <td>
                                <strong>${change.stock_anterior}</strong>
                            </td>
                            <td>
                                <strong>${change.stock_nuevo}</strong>
                            </td>
                            <td>
                                <span class="change-badge ${changeClass}">
                                    ${changeIcon} ${changeBadge}
                                </span>
                            </td>
                        </tr>`;
            }

            tabla += `</tbody>
                        </table>
                    </div>`;

            historyContainer.innerHTML = tabla;
        } else {
            historyContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h4>Sin historial</h4>
                    <p>Aún no hay cambios registrados en el stock</p>
                </div>
            `;
        }
    });
}

// Mantener compatibilidad con función anterior
async function addStock() {
    await saveStock();
}