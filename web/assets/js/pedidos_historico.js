// Obtener el ID de la sucursal desde la URL
const urlParams = new URLSearchParams(window.location.search);
let sucursalId = urlParams.get('id');

// Si no hay ID en la URL, intentar obtenerlo de sessionStorage
if (!sucursalId) {
    sucursalId = sessionStorage.getItem('currentSucursalId');
    if (sucursalId) {
        // Actualizar la URL con el ID recuperado
        window.history.replaceState({}, '', `?id=${sucursalId}`);
    }
}

// Guardar el ID en sessionStorage para futuras navegaciones
if (sucursalId) {
    sessionStorage.setItem('currentSucursalId', sucursalId);
}

let allPedidos = [];
let filteredPedidos = [];

// Cargar información de la sucursal al cargar la página
$(document).ready(function () {
    if (sucursalId) {
        loadSucursalInfo();
        // Establecer fechas por defecto (últimos 7 días)
        setDefaultDates();
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se ha especificado una sucursal',
            confirmButtonText: 'Volver'
        }).then(() => {
            // Intentar volver con el ID si existe
            const savedId = sessionStorage.getItem('currentSucursalId');
            if (savedId) {
                window.location.href = `./sucursal.html?id=${savedId}`;
            } else {
                window.location.href = './sucursal.html';
            }
        });
    }
});

// Establecer fechas por defecto
function setDefaultDates() {
    const now = new Date();
    // Primer día del mes actual
    const firstDayOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
    // Último día del mes actual
    const lastDayOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59);
    
    document.getElementById('startDate').value = formatDateForInput(firstDayOfMonth);
    document.getElementById('endDate').value = formatDateForInput(lastDayOfMonth);
}

// Formatear fecha para el input datetime-local
function formatDateForInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Cargar información de la sucursal
async function loadSucursalInfo() {
    const url = generarURLApi(`/web/getApp?id=${sucursalId}`);
    
    __conection({
        url: url,
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        if (request && request[0] && request[0].name) {
            document.getElementById('sucursal-name').textContent = request[0].name;
        }
    });
}

// Filtrar pedidos
async function filterPedidos() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!startDate || !endDate) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Por favor seleccione un rango de fechas',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    showLoader();
    await getPedidosHistoricos(startDate, endDate);
    hideLoader();
}

// Obtener pedidos históricos de la sucursal
async function getPedidosHistoricos(startDate, endDate) {
    // Formatear fechas para la API
    const formattedStartDate = startDate.replace('T', ' ') + ':00';
    const formattedEndDate = endDate.replace('T', ' ') + ':59';
    
    allPedidos = [];
    const pedidosIds = new Set(); // Para evitar duplicados
    
    // Hacer llamadas de 10 páginas para no saturar
    let page = 1;
    let hasMorePages = true;
    let emptyPagesCount = 0;
    const maxEmptyPages = 3; // Si encontramos 3 páginas vacías seguidas, paramos
    
    while (hasMorePages && page <= 100) { // Límite de 100 páginas por seguridad
        const params = `?params=true&page=${page}`;
        const url = generarURLApi(`/web/getAppRequests${params}&id=${sucursalId}&startDate=${formattedStartDate}&endDate=${formattedEndDate}`);
        
        await new Promise((resolve) => {
            __conection({
                url: url,
                header: credentials(),
                dev: false, // Desactivar logs para no saturar consola
                method: 'GET'
            }, {}, function (request) {
                let foundPedidos = false;
                
                if (request && typeof request === 'object') {
                    // La respuesta es un objeto donde cada key es un nombre de app
                    for (const appName in request) {
                        const app = request[appName];
                        
                        if (app.original && app.original.items && app.original.items.length > 0) {
                            for (const pedido of app.original.items) {
                                // Solo agregar si no es duplicado y pertenece a esta sucursal
                                if (!pedidosIds.has(pedido.id) && pedido.app_id == sucursalId) {
                                    pedidosIds.add(pedido.id);
                                    pedido.app_name = appName;
                                    pedido.app_id = pedido.app_id;
                                    allPedidos.push(pedido);
                                    foundPedidos = true;
                                }
                            }
                        }
                    }
                }
                
                // Control de páginas vacías
                if (!foundPedidos) {
                    emptyPagesCount++;
                    if (emptyPagesCount >= maxEmptyPages) {
                        hasMorePages = false;
                    }
                } else {
                    emptyPagesCount = 0; // Reiniciar contador si encontramos pedidos
                }
                
                resolve();
            });
        });
        
        // Actualizar display cada 10 páginas para dar feedback al usuario
        if (page % 10 === 0) {
            console.log(`📊 Procesadas ${page} páginas - ${allPedidos.length} pedidos encontrados`);
        }
        
        page++;
    }
    
    console.log(`✅ Total de pedidos encontrados para sucursal ${sucursalId}: ${allPedidos.length} (${page-1} páginas revisadas)`);
    
    // Ordenar por fecha más reciente primero
    allPedidos.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    
    filteredPedidos = [...allPedidos];
    displayPedidos(filteredPedidos);
    updateStatistics(filteredPedidos);
    
    // Mostrar botón de exportar si hay datos
    const btnExportar = document.getElementById('btnExportar');
    if (btnExportar) {
        btnExportar.style.display = filteredPedidos.length > 0 ? 'block' : 'none';
    }
}

// Mostrar pedidos en la tabla
function displayPedidos(pedidos) {
    const tbody = document.getElementById('pedidos-tbody');
    tbody.innerHTML = '';
    
    if (pedidos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="13" class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                    No se encontraron pedidos en el rango de fechas seleccionado
                </td>
            </tr>
        `;
        return;
    }
    
    for (const pedido of pedidos) {
        const total = parseFloat(pedido.price) || 0;
        
        // Parsear productos
        let productos = pedido.products;
        if (typeof productos === 'string') {
            try {
                productos = JSON.parse(productos);
            } catch (e) {
                console.error('Error al parsear productos:', e);
                productos = [];
            }
        }
        
        // Inicializar contadores para cada producto
        let alfredo = 0, bolonesa = 0, camaron = 0, champinon = 0, pesto = 0, vasos = 0;
        let otrosProductos = [];
        
        // Categorizar productos
        if (productos && Array.isArray(productos)) {
            for (const p of productos) {
                const nombre = (p.name || '').toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, ""); // Normalizar y quitar acentos
                const nombreOriginal = p.name || 'Producto';
                const cantidad = parseFloat(p.quantity) || 0;
                const categoria = p.category || 0;
                
                // Formatear cantidad
                let cantidadFormateada = cantidad;
                if (p.id == 2) {
                    cantidadFormateada = `${cantidad} (${Math.ceil(p.price)}gr)`;
                } else if (categoria !== 1 && categoria !== 3) {
                    cantidadFormateada = `${cantidad} Kg`;
                }
                
                // Clasificar por nombre (sin acentos para comparación)
                if (nombre.includes('alfredo')) {
                    alfredo = cantidadFormateada;
                } else if (nombre.includes('bolonesa')) {
                    bolonesa = cantidadFormateada;
                } else if (nombre.includes('camaron')) {
                    camaron = cantidadFormateada;
                } else if (nombre.includes('champinon')) {
                    champinon = cantidadFormateada;
                } else if (nombre.includes('pesto') && !nombre.includes('focaccia')) {
                    // Forzar Kg para salsa pesto
                    pesto = categoria !== 1 && categoria !== 3 ? cantidadFormateada : `${cantidad} Kg`;
                } else if (nombre.includes('vaso') || p.id === 1) {
                    vasos = cantidad;
                } else {
                    // Otros productos con su nombre original
                    otrosProductos.push(`${nombreOriginal}: ${cantidadFormateada}`);
                }
            }
        }
        
        const otrosHTML = otrosProductos.length > 0 
            ? `<small>${otrosProductos.join('<br>')}</small>` 
            : '-';
        
        const row = `
            <tr>
                <td style="white-space: nowrap;"><strong>#${pedido.id}</strong></td>
                <td style="white-space: nowrap;">${formatDate(pedido.created_at)}</td>
                <td>${pedido.contact_name || 'N/A'}</td>
                <td style="white-space: nowrap;">${pedido.contact_phone || 'N/A'}</td>
                <td style="white-space: nowrap;"><strong>$${formatNumber(total)}</strong></td>
                <td>${pedido.paymode || 'N/A'}</td>
                <td class="text-center">${alfredo || '-'}</td>
                <td class="text-center">${bolonesa || '-'}</td>
                <td class="text-center">${camaron || '-'}</td>
                <td class="text-center">${champinon || '-'}</td>
                <td class="text-center">${pesto || '-'}</td>
                <td class="text-center">${vasos || '-'}</td>
                <td style="max-width: 200px; font-size: 0.85rem;">${otrosHTML}</td>
            </tr>
        `;
        tbody.innerHTML += row;
    }
}

// Calcular total del pedido
function calculateTotal(products) {
    if (!products || products.length === 0) return 0;
    
    let total = 0;
    for (const product of products) {
        const price = parseFloat(product.price) || 0;
        const quantity = parseInt(product.quantity) || 0;
        total += price * quantity;
    }
    return total;
}

// Obtener badge de estado
function getStatusBadge(status) {
    const badges = {
        'nuevo': '<span class="badge badge-modern bg-info"><i class="fas fa-star"></i> Nuevo</span>',
        'espera': '<span class="badge badge-modern bg-warning"><i class="fas fa-hourglass-half"></i> En Espera</span>',
        'aprobado': '<span class="badge badge-modern bg-success"><i class="fas fa-check"></i> Aprobado</span>',
        'rechazado': '<span class="badge badge-modern bg-danger"><i class="fas fa-ban"></i> Rechazado</span>',
    };
    return badges[status] || `<span class="badge badge-modern bg-secondary">${status}</span>`;
}

// Obtener badge de pago
function getPaymentBadge(paymentStatus) {
    const badges = {
        'Pagado': '<span class="badge badge-modern bg-success">Pagado</span>',
        'Pendiente': '<span class="badge badge-modern bg-warning">Pendiente</span>',
        'Rechazado': '<span class="badge badge-modern bg-danger">Rechazado</span>',
    };
    return badges[paymentStatus] || `<span class="badge badge-modern bg-secondary">${paymentStatus}</span>`;
}

// Actualizar estadísticas
function updateStatistics(pedidos) {
    const totalPedidos = pedidos.length;
    let totalVasos = 0;
    let totalVentas = 0;
    
    console.log(`🔍 Analizando ${pedidos.length} pedidos...`);
    
    for (const pedido of pedidos) {
        // Sumar total de ventas
        totalVentas += parseFloat(pedido.price) || 0;
        
        // Si products es string, parsearlo
        let productos = pedido.products;
        if (typeof productos === 'string') {
            try {
                productos = JSON.parse(productos);
            } catch (e) {
                continue;
            }
        }
        
        // Contar vasos
        if (productos && Array.isArray(productos)) {
            for (const producto of productos) {
                const nombreProducto = (producto.name || '').toLowerCase();
                const idProducto = parseInt(producto.id) || 0;
                const cantidad = parseFloat(producto.quantity) || 0;
                
                // Vasos (ID 1)
                if (idProducto === 1 || nombreProducto.includes('vaso')) {
                    totalVasos += cantidad;
                }
            }
        }
    }
    
    console.log(`📊 Total Pedidos: ${totalPedidos}, Total Ventas: $${totalVentas}, Total Vasos: ${totalVasos}`);
    
    document.getElementById('total-pedidos').textContent = totalPedidos;
    document.getElementById('total-ventas').textContent = '$' + formatNumber(totalVentas);
    document.getElementById('total-vasos').textContent = formatNumber(totalVasos);
}

// Ver pedido - hacer llamada a la API para obtener detalles completos
function verPedido(app_id, id) {
    __conection({
        url: generarURLApi(`/local/request/view/${app_id}/${id}`),
        header: credentials(),
        dev: true,
        method: 'GET'
    }, {}, function (request) {
        const pedido = request;

        // Parsear productos
        const products = JSON.parse(pedido.products);
        console.log(products);
        
        const pedidoModal = document.getElementById('pedidoModal');
        const modalTitle = pedidoModal.querySelector('.modal-title');
        modalTitle.textContent = `Pedido # - ${id}`;

        // Llenar tabla de productos
        const tablaProducts = document.getElementById('table-products');
        tablaProducts.innerHTML = '';
        let i = 1;
        
        for (const product of products) {
            let fila = "";

            if (product.id == 2) {
                fila = `<tr>
                    <td>${i}</td> 
                    <td>${product.name.toUpperCase()}</td> 
                    <td>${product.quantity + " (" + Math.ceil(product.price) + "gr" + ")"}</td>  
                </tr>`;
            } else {
                fila = `<tr>
                    <td>${i}</td> 
                    <td>${product.name.toUpperCase()}</td> 
                    <td>${product.category !== 1 && product.category !== 3 
                        ? `${product.quantity} Kg` : product.quantity}</td>  
                </tr>`;
            }

            tablaProducts.innerHTML += fila;
            i++;
        }
        
        // Llenar lista de datos del pedido
        const listData = document.getElementById('data-pedido');
        const bodyList = `
            <li class="list-group-item d-flex justify-content-between align-items-start">
                <div class="ms-2 me-auto">
                    <div class="fw-bold">Nombre</div>
                    ${pedido.contact_name}
                </div>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-start">
                <div class="ms-2 me-auto">
                    <div class="fw-bold">Teléfono de contacto</div>
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
                    ${pedido.comment || 'Sin comentarios'}
                </div>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-start">
                <div class="ms-2 me-auto">
                    <div class="fw-bold">Reseña</div>
                    ${pedido.review || 'Sin reseña'}
                </div>
            </li>`;
        listData.innerHTML = bodyList;

        // Formatear total
        const formatter = new Intl.NumberFormat('es-CL', {
            style: 'currency',
            currency: 'CLP'
        });

        const total = document.getElementById('total');
        total.innerHTML = formatter.format(pedido.price);
    });
}

// Resetear filtros
function resetFilters() {
    setDefaultDates();
    document.getElementById('pedidos-tbody').innerHTML = `
        <tr>
            <td colspan="13" class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                Seleccione un rango de fechas para ver los pedidos
            </td>
        </tr>
    `;
    document.getElementById('total-pedidos').textContent = '0';
    document.getElementById('total-ventas').textContent = '$0';
    document.getElementById('total-vasos').textContent = '0';
    
    // Ocultar botón de exportar
    const btnExportar = document.getElementById('btnExportar');
    if (btnExportar) {
        btnExportar.style.display = 'none';
    }
}

// Formatear fecha
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}`;
}

// Formatear números
function formatNumber(number) {
    return number.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Mostrar loader
function showLoader() {
    document.getElementById('loader-container').style.display = 'block';
    document.getElementById('pedidos-tbody').style.display = 'none';
}

// Ocultar loader
function hideLoader() {
    document.getElementById('loader-container').style.display = 'none';
    document.getElementById('pedidos-tbody').style.display = 'table-row-group';
}

// Exportar a Excel
function exportarAExcel() {
    if (!filteredPedidos || filteredPedidos.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin datos',
            text: 'No hay pedidos para exportar'
        });
        return;
    }
    
    Swal.fire({
        title: 'Generando Excel...',
        text: 'Por favor espera un momento',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Preparar datos para CSV
    let csvContent = "data:text/csv;charset=utf-8,\uFEFF"; // BOM para UTF-8
    
    // Headers
    const headers = ['ID', 'Fecha', 'Cliente', 'Teléfono', 'Total', 'Método Pago', 'Alfredo', 'Boloñesa', 'Camarón', 'Champiñón', 'Pesto', 'Vasos', 'Otros'];
    csvContent += headers.join(',') + '\n';
    
    // Procesar cada pedido
    for (const pedido of filteredPedidos) {
        const total = parseFloat(pedido.price) || 0;
        
        // Parsear productos
        let productos = pedido.products;
        if (typeof productos === 'string') {
            try {
                productos = JSON.parse(productos);
            } catch (e) {
                productos = [];
            }
        }
        
        // Inicializar contadores
        let alfredo = '', bolonesa = '', camaron = '', champinon = '', pesto = '', vasos = '';
        let otrosProductos = [];
        
        // Categorizar productos
        if (productos && Array.isArray(productos)) {
            for (const p of productos) {
                const nombre = (p.name || '').toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                const nombreOriginal = p.name || 'Producto';
                const cantidad = parseFloat(p.quantity) || 0;
                const categoria = p.category || 0;
                
                let cantidadFormateada = cantidad;
                if (p.id == 2) {
                    cantidadFormateada = `${cantidad} (${Math.ceil(p.price)}gr)`;
                } else if (categoria !== 1 && categoria !== 3) {
                    cantidadFormateada = `${cantidad} Kg`;
                }
                
                if (nombre.includes('alfredo')) {
                    alfredo = cantidadFormateada;
                } else if (nombre.includes('bolonesa')) {
                    bolonesa = cantidadFormateada;
                } else if (nombre.includes('camaron')) {
                    camaron = cantidadFormateada;
                } else if (nombre.includes('champinon')) {
                    champinon = cantidadFormateada;
                } else if (nombre.includes('pesto') && !nombre.includes('focaccia')) {
                    // Forzar Kg para salsa pesto
                    pesto = categoria !== 1 && categoria !== 3 ? cantidadFormateada : `${cantidad} Kg`;
                } else if (nombre.includes('vaso') || p.id === 1) {
                    vasos = cantidad;
                } else {
                    otrosProductos.push(`${nombreOriginal}: ${cantidadFormateada}`);
                }
            }
        }
        
        const otros = otrosProductos.join(' | ');
        
        // Crear fila (escapar comas y comillas)
        const row = [
            pedido.id,
            formatDate(pedido.created_at),
            `"${(pedido.contact_name || 'N/A').replace(/"/g, '""')}"`,
            pedido.contact_phone || 'N/A',
            total.toFixed(0),
            `"${(pedido.paymode || 'N/A').replace(/"/g, '""')}"`,
            alfredo || '',
            bolonesa || '',
            camaron || '',
            champinon || '',
            pesto || '',
            vasos || '',
            `"${otros || ''}"`
        ];
        
        csvContent += row.join(',') + '\n';
    }
    
    // Descargar archivo
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    
    const startDate = document.getElementById('startDate').value.split('T')[0];
    const endDate = document.getElementById('endDate').value.split('T')[0];
    const fileName = `pedidos_historico_${startDate}_${endDate}.csv`;
    
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    Swal.fire({
        icon: 'success',
        title: '¡Exportado!',
        text: `Archivo ${fileName} descargado exitosamente`,
        timer: 2000,
        showConfirmButton: false
    });
}

// Volver a sucursal
function volverASucursal() {
    const savedId = sessionStorage.getItem('currentSucursalId');
    if (savedId) {
        window.location.href = `./sucursal.html?id=${savedId}`;
    } else {
        window.location.href = './sucursal.html';
    }
}

// Cerrar sesión
function cerrarSesion() {
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: '¿Estás seguro que deseas cerrar sesión?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            sessionStorage.removeItem('currentSucursalId');
            _store().session.clear();
            window.location.href = './login.html';
        }
    });
}
