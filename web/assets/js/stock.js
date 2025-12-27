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
                            <table class="stock-table table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 5%;">#</th>
                                        <th scope="col" style="width: 25%;">
                                            <i class="fas fa-bottle-droplet"></i> Producto
                                        </th>
                                        <th scope="col" style="width: 12%;">
                                            <i class="fas fa-box"></i> Bolsas
                                        </th>
                                        <th scope="col" style="width: 12%;">
                                            <i class="fas fa-weight"></i> Kg Total
                                        </th>
                                        <th scope="col" style="width: 12%;">
                                            <i class="fas fa-wine-glass"></i> Vasos
                                        </th>
                                        <th scope="col" style="width: 15%;">
                                            <i class="fas fa-info-circle"></i> Info/Bolsa
                                        </th>
                                        <th scope="col" style="width: 12%;" class="text-center">
                                            <i class="fas fa-edit"></i> Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>`;

            for (const index in products) {
                if (products[index].category == 2) {
                    const sauceIcon = getSauceIcon(products[index].name);
                    
                    // Determinar configuración según tipo de salsa
                    const nameLower = products[index].name.toLowerCase();
                    let kgPorSet27Vasos; // Kg que pesan las bolsas necesarias para hacer 27 vasos
                    let bolsasPorSet27;  // Cuántas bolsas físicas se necesitan para 27 vasos
                    let kgPorBolsaIndividual; // Peso de 1 bolsa física
                    
                    if (nameLower.includes('pesto')) {
                        // PESTO: 1 bolsa = 27 vasos = 2.100 kg
                        kgPorBolsaIndividual = 2.100;
                        bolsasPorSet27 = 1;
                        kgPorSet27Vasos = 2.100;
                    } else if (nameLower.includes('bolognesa') || nameLower.includes('bolonesa') || nameLower.includes('boloñesa')) {
                        // BOLOÑESA: 2 bolsas = 27 vasos = 3.718 kg (1 bolsa = 1.859 kg)
                        kgPorBolsaIndividual = 1.859;
                        bolsasPorSet27 = 2;
                        kgPorSet27Vasos = 3.718;
                    } else {
                        // ALFREDO, CAMARÓN, CHAMPIÑÓN: 2 bolsas = 27 vasos = 4.158 kg (1 bolsa = 2.079 kg)
                        kgPorBolsaIndividual = 2.079;
                        bolsasPorSet27 = 2;
                        kgPorSet27Vasos = 4.158;
                    }
                    
                    // El stock representa "sets de 27 vasos"
                    const totalBolsasFisicas = products[index].stock * bolsasPorSet27;
                    const totalKg = (products[index].stock * kgPorSet27Vasos).toFixed(3);
                    const totalVasos = products[index].stock * 27; // Siempre 27 vasos por set
                    
                    console.log(`📦 ${products[index].name}: Stock=${products[index].stock} sets, Bolsas=${totalBolsasFisicas}, Kg=${totalKg}, Vasos=${totalVasos}`);
                    
                    fila += `<tr>
                                <td class="text-muted"><b>${i}</b></td>
                                <td class="product-name">
                                    ${sauceIcon}
                                    <span class="ms-2">${products[index].name}</span>
                                </td>
                                <td>
                                    <span class="stock-badge bags">
                                        <i class="fas fa-box"></i>
                                        ${totalBolsasFisicas}
                                    </span>
                                    <small class="text-muted d-block mt-1">(${products[index].stock} sets)</small>
                                </td>
                                <td>
                                    <span class="stock-badge" style="background: linear-gradient(135deg, #f093fb20 0%, #f5576c20 100%); color: #f5576c;">
                                        <i class="fas fa-weight-hanging"></i>
                                        ${totalKg} kg
                                    </span>
                                </td>
                                <td>
                                    <span class="stock-badge cups">
                                        <i class="fas fa-wine-glass"></i>
                                        ${totalVasos}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <div><strong>${kgPorBolsaIndividual} kg</strong>/bolsa física</div>
                                        <div><strong>${bolsasPorSet27}</strong> bolsas = 27 vasos</div>
                                        <div><strong>${kgPorSet27Vasos} kg</strong> por set</div>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <button class='btn btn-edit-stock btn-sm' 
                                            onclick="openModalAddStock(${products[index].id}, ${products[index].stock}, '${products[index].name.replace(/'/g, "\\'")}')" 
                                            data-stock="${products[index].stock}">
                                        <i class="fa-solid fa-edit"></i> Editar
                                    </button>
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
                url: generarURLApi(`/local/products/updateStock/${productIdToUpdate}`),
                header: credentials(),
                dev: true,
                method: 'POST',
            },
            {
                stock_added: stockToAdd
            },
            function (request) {
                console.log('Respuesta del servidor:', request);
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
    const url = generarURLApi(`/local/products/stockHistory`);

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
                                        <th style="width: 20%;">Usuario</th>
                                        <th style="width: 15%;">Anterior</th>
                                        <th style="width: 15%;">Nuevo</th>
                                        <th style="width: 15%;">Cambio</th>
                                    </tr>
                                </thead>
                                <tbody>`;

            for (const change of changes) {
                const date = new Date(change.created_at);
                const formattedDate = date.toLocaleString('es-CL', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const difference = change.new_value - change.old_value;
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
                                <span class="user-badge">
                                    <i class="fas fa-user"></i>
                                    ${change.user_name || 'Sistema'}
                                </span>
                            </td>
                            <td>
                                <strong>${change.old_value}</strong> bolsas
                            </td>
                            <td>
                                <strong>${change.new_value}</strong> bolsas
                            </td>
                            <td>
                                <span class="change-badge ${changeClass}">
                                    ${changeIcon} ${changeBadge} bolsas
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