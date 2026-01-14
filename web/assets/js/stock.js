$(document).ready(function () {
    // _name.text(_store().session.user().get("username")) //obtenemos el nombre del usuario
    getProducts();
    getStockHistory();
    getPrecioHistory();
    getNombreHistory();
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
    await getPrecioHistory();
    await getNombreHistory();
    desactivateLoader();
}

let productIdToUpdate = null;
let currentStock = 0;
let productNameToUpdate = '';

// Variables para gestión de precio
let productIdToUpdatePrice = null;
let currentPrice = 0;
let productNameToUpdatePrice = '';

// Variables para gestión de nombre
let productIdToUpdateName = null;
let currentProductName = '';

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

function openModalEditPrice(productId, precio, productName = '') {
    productIdToUpdatePrice = productId;
    currentPrice = precio;
    productNameToUpdatePrice = productName;
    
    // Actualizar display
    document.getElementById('current-price-display').textContent = new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP'
    }).format(precio);
    
    // Establecer valor en input
    document.getElementById('precio_nuevo').value = precio;
    
    // Actualizar título del modal
    document.getElementById('modal-price-title-text').textContent = `Editar Precio - ${productName}`;
    
    $('#editPriceModal').modal('show');
}

function openModalEditName(productId, productName = '') {
    productIdToUpdateName = productId;
    currentProductName = productName;
    
    // Actualizar display
    document.getElementById('current-name-display').textContent = productName;
    
    // Establecer valor en input
    document.getElementById('nombre_nuevo').value = productName;
    
    $('#editNameModal').modal('show');
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
                                        <th scope="col" style="width: 30%;">
                                            <i class="fas fa-box-open"></i> Producto
                                        </th>
                                        <th scope="col" style="width: 15%;">
                                            <i class="fas fa-boxes"></i> Stock (Bolsas)
                                        </th>
                                        <th scope="col" style="width: 12%;">
                                            <i class="fas fa-balance-scale"></i> Unidad
                                        </th>
                                        <th scope="col" style="width: 15%;">
                                            <i class="fas fa-dollar-sign"></i> Precio
                                        </th>
                                        <th scope="col" style="width: 18%;" class="text-center">
                                            <i class="fas fa-edit"></i> Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>`;

            for (const index in products) {
                const stock = products[index].stock || 0;
                const unidad = products[index].unidad_medida || 'unidad';
                const precioPorUnidad = parseFloat(products[index].precio_por_unidad) || 0;
                const precioFormateado = new Intl.NumberFormat('es-CL', {
                    style: 'currency',
                    currency: 'CLP'
                }).format(precioPorUnidad);
                const kilosTotales = products[index].kilos_totales;
                const unidadVenta = products[index].unidad_venta || 0;
                const categoria = (products[index].categoria || '').toLowerCase();
                const nombreProducto = products[index].producto || '';
                
                // Determinar color del badge según nivel de stock
                let stockClass = 'success';
                if (stock <= 5) stockClass = 'danger';
                else if (stock <= 20) stockClass = 'warning';
                
                fila += `<tr>
                            <td class="text-muted"><b>${i}</b></td>
                            <td class="product-name">
                                <div class="d-flex flex-column gap-1">
                                    <div>
                                        <i class="fas fa-box text-primary"></i>
                                        <span class="ms-2"><strong>${nombreProducto}</strong></span>
                                    </div>
                                    <button class='btn btn-sm btn-outline-secondary' 
                                            onclick="openModalEditName(${products[index].id}, '${nombreProducto.replace(/'/g, "\\'")}')"
                                            style="padding: 0.2rem 0.5rem; font-size: 0.75rem; width: fit-content;">
                                        <i class="fa-solid fa-pencil"></i> Editar Nombre
                                    </button>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-${stockClass} fs-6">
                                    ${stock} bolsas
                                </span>
                            </td>
                            <td>
                                <span class="text-muted">
                                    <i class="fas fa-ruler"></i> ${unidad}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <span class="text-success fw-bold fs-6">${precioFormateado}</span>
                                    <button class='btn btn-sm btn-outline-primary' 
                                            onclick="openModalEditPrice(${products[index].id}, ${precioPorUnidad}, '${products[index].producto.replace(/'/g, "\\'")}')" 
                                            style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">
                                        <i class="fa-solid fa-dollar-sign"></i> Editar Precio
                                    </button>
                                </div>
                            </td>
                            <td class="text-center">
                                <button class='btn btn-edit-stock btn-sm' 
                                        onclick="openModalAddStock(${products[index].id}, ${stock}, '${products[index].producto.replace(/'/g, "\\'")}')" 
                                        data-stock="${stock}">
                                    <i class="fa-solid fa-boxes"></i> Editar Stock
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
                getPrecioHistory();
                getNombreHistory();
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

// ============== FUNCIONES PARA GESTIÓN DE PRECIO ==============

async function savePrecio() {
    if (productIdToUpdatePrice === null) {
        alert('No se ha seleccionado ningún producto.');
        return;
    }

    const precioInput = document.getElementById('precio_nuevo');
    const precioNuevo = parseFloat(precioInput.value);

    if (isNaN(precioNuevo) || precioNuevo < 0) {
        alert('Por favor, ingresa un precio válido (0 o mayor).');
        return;
    }

    console.log('Actualizando precio:', {
        productId: productIdToUpdatePrice,
        precioAnterior: currentPrice,
        precioNuevo: precioNuevo
    });

    try {
        const response = await __conection(
            {
                url: generarURLApi(`/local/pedidofinal/precio/${productIdToUpdatePrice}`),
                header: credentials(),
                dev: true,
                method: 'POST',
            },
            {
                _method: 'PUT',
                precio_por_unidad: precioNuevo,
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
                
                alert(request.message || 'Precio actualizado correctamente');
                getProducts();
                getPrecioHistory();
                getNombreHistory();
                $('#editPriceModal').modal('hide');
            }
        );

    } catch (error) {
        console.error("Error actualizando precio:", error);
        alert('Error al actualizar el precio. Por favor verifica tu conexión e intenta nuevamente.');
    }
}

async function getPrecioHistory() {
    const url = generarURLApi(`/local/pedidofinal/precio/history`);

    await __conection({
        url: url,
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        let changes = request;
        const historyPriceContainer = document.getElementById('history-price-container');
        
        if (!historyPriceContainer) {
            console.log('Contenedor de historial de precios no encontrado');
            return;
        }
        
        historyPriceContainer.innerHTML = "";

        if (changes.length > 0) {
            let tabla = `<div class="table-responsive">
                            <table class="history-table table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Fecha y Hora</th>
                                        <th style="width: 30%;">Producto</th>
                                        <th style="width: 15%;">Usuario</th>
                                        <th style="width: 17%;">Precio Anterior</th>
                                        <th style="width: 17%;">Precio Nuevo</th>
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

                const precioAnterior = new Intl.NumberFormat('es-CL', {
                    style: 'currency',
                    currency: 'CLP'
                }).format(change.precio_anterior);

                const precioNuevo = new Intl.NumberFormat('es-CL', {
                    style: 'currency',
                    currency: 'CLP'
                }).format(change.precio_nuevo);

                const diferencia = change.precio_nuevo - change.precio_anterior;
                let changeClass = diferencia > 0 ? 'increase' : diferencia < 0 ? 'decrease' : 'set';
                let changeIcon = diferencia > 0 ? '<i class="fas fa-arrow-up"></i>' : 
                                diferencia < 0 ? '<i class="fas fa-arrow-down"></i>' : 
                                '<i class="fas fa-equals"></i>';

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
                                <strong>${precioAnterior}</strong>
                            </td>
                            <td>
                                <span class="change-badge ${changeClass}">
                                    ${changeIcon} ${precioNuevo}
                                </span>
                            </td>
                        </tr>`;
            }

            tabla += `</tbody>
                        </table>
                    </div>`;

            historyPriceContainer.innerHTML = tabla;
        } else {
            historyPriceContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h4>Sin historial de precios</h4>
                    <p>Aún no hay cambios registrados en los precios</p>
                </div>
            `;
        }
    });
}

// ============== FUNCIONES PARA GESTIÓN DE NOMBRE ==============

async function saveNombre() {
    if (productIdToUpdateName === null) {
        alert('No se ha seleccionado ningún producto.');
        return;
    }

    const nombreInput = document.getElementById('nombre_nuevo');
    const nombreNuevo = nombreInput.value.trim();

    if (!nombreNuevo || nombreNuevo.length < 2) {
        alert('Por favor, ingresa un nombre válido (mínimo 2 caracteres).');
        return;
    }

    console.log('Actualizando nombre:', {
        productId: productIdToUpdateName,
        nombreAnterior: currentProductName,
        nombreNuevo: nombreNuevo
    });

    try {
        const response = await __conection(
            {
                url: generarURLApi(`/local/pedidofinal/nombre/${productIdToUpdateName}`),
                header: credentials(),
                dev: true,
                method: 'POST',
            },
            {
                _method: 'PUT',
                producto: nombreNuevo,
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
                
                alert(request.message || 'Nombre actualizado correctamente');
                getProducts();
                getNombreHistory();
                $('#editNameModal').modal('hide');
            }
        );

    } catch (error) {
        console.error("Error actualizando nombre:", error);
        alert('Error al actualizar el nombre. Por favor verifica tu conexión e intenta nuevamente.');
    }
}

async function getNombreHistory() {
    const url = generarURLApi(`/local/pedidofinal/nombre/history`);

    await __conection({
        url: url,
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        let changes = request;
        const historyNameContainer = document.getElementById('history-name-container');
        
        if (!historyNameContainer) {
            console.log('Contenedor de historial de nombres no encontrado');
            return;
        }
        
        historyNameContainer.innerHTML = "";

        if (changes.length > 0) {
            let tabla = `<div class="table-responsive">
                            <table class="history-table table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Fecha y Hora</th>
                                        <th style="width: 15%;">Usuario</th>
                                        <th style="width: 32%;">Nombre Anterior</th>
                                        <th style="width: 32%;">Nombre Nuevo</th>
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

                tabla += `<tr>
                            <td class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                ${formattedDate}
                            </td>
                            <td>
                                <span class="user-badge">
                                    <i class="fas fa-user"></i>
                                    ${change.usuario || 'Sistema'}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted">
                                    <i class="fas fa-arrow-right text-danger me-1"></i>
                                    ${change.nombre_anterior}
                                </span>
                            </td>
                            <td>
                                <span class="text-success fw-bold">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    ${change.nombre_nuevo}
                                </span>
                            </td>
                        </tr>`;
            }

            tabla += `</tbody>
                        </table>
                    </div>`;

            historyNameContainer.innerHTML = tabla;
        } else {
            historyNameContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h4>Sin historial de nombres</h4>
                    <p>Aún no hay cambios registrados en los nombres de productos</p>
                </div>
            `;
        }
    });
}