// Sistema de Facturas por Sucursal - COMPLETAMENTE INDEPENDIENTE
// NO usar helpers para evitar conflictos

console.log('[FACTURAS] 🚀 Sistema cargado...');

// Variables globales
let sucursales = [];
let facturas = [];
let facturaActual = null;

// URLs de la API
const API_BASE = 'https://fagottoerp.cl/api';

// Función para obtener credenciales del localStorage
function getCredentials() {
    const appKey = localStorage.getItem('app_key');
    const token = localStorage.getItem('auth_token');
    
    if (!appKey || !token) {
        console.error('[FACTURAS] ❌ No hay credenciales guardadas');
        mostrarError('No hay credenciales guardadas. Ve al dashboard principal y haz login.');
        return null;
    }
    
    return {
        'app-key': appKey,
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };
}

// Función para hacer peticiones HTTP
async function hacerPeticion(url, options = {}) {
    const credentials = getCredentials();
    if (!credentials) return null;
    
    const config = {
        method: 'GET',
        headers: credentials,
        ...options
    };
    
    console.log('[FACTURAS] 📡 Petición:', url);
    
    try {
        const response = await fetch(url, config);
        const data = await response.json();
        
        console.log('[FACTURAS] 📨 Respuesta:', response.status, data);
        
        if (!response.ok) {
            throw new Error(`Error ${response.status}: ${data.message || 'Error desconocido'}`);
        }
        
        return data;
    } catch (error) {
        console.error('[FACTURAS] ❌ Error en petición:', error);
        mostrarError(`Error de conexión: ${error.message}`);
        return null;
    }
}

// Cargar sucursales al iniciar
document.addEventListener('DOMContentLoaded', async function() {
    console.log('[FACTURAS] 🚀 Página cargada, iniciando...');
    
    // Verificar credenciales
    const credentials = getCredentials();
    if (!credentials) return;
    
    // Cargar sucursales
    await cargarSucursales();
    
    // Establecer fecha actual en el formulario de cancelación
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('fecha_emision').value = hoy;
});

// Cargar lista de sucursales
async function cargarSucursales() {
    console.log('[FACTURAS] 🔄 Cargando sucursales...');
    
    const data = await hacerPeticion(`${API_BASE}/getApps`, {
        method: 'POST'
    });
    
    if (data && data.success && data.content) {
        sucursales = data.content;
        console.log('[FACTURAS] ✅ Sucursales cargadas:', sucursales.length);
        
        // Llenar selector
        const selector = document.getElementById('sucursalSelect');
        selector.innerHTML = '<option value="">Selecciona una sucursal...</option>';
        
        sucursales.forEach(sucursal => {
            const option = document.createElement('option');
            option.value = sucursal.id;
            option.textContent = `${sucursal.name} (${sucursal.database.name})`;
            selector.appendChild(option);
        });
    }
}

// Cargar facturas de una sucursal específica
async function cargarFacturasSucursal() {
    const sucursalId = document.getElementById('sucursalSelect').value;
    
    if (!sucursalId) {
        mostrarError('Selecciona una sucursal primero');
        return;
    }
    
    console.log('[FACTURAS] 🔄 Cargando facturas de sucursal:', sucursalId);
    
    // Mostrar loading
    document.getElementById('loadingSection').style.display = 'block';
    document.getElementById('facturasContainer').innerHTML = '';
    
    const data = await hacerPeticion(`${API_BASE}/web/sucursal/${sucursalId}/facturas`);
    
    // Ocultar loading
    document.getElementById('loadingSection').style.display = 'none';
    
    if (data && data.success && data.data) {
        facturas = data.data;
        console.log('[FACTURAS] ✅ Facturas cargadas:', facturas.length);
        
        mostrarFacturas();
        mostrarInfoSucursal(sucursalId);
    } else {
        mostrarError('No se pudieron cargar las facturas');
    }
}

// Mostrar información de la sucursal seleccionada
function mostrarInfoSucursal(sucursalId) {
    const sucursal = sucursales.find(s => s.id == sucursalId);
    if (!sucursal) return;
    
    document.getElementById('sucursalNombre').textContent = sucursal.name;
    document.getElementById('totalFacturas').textContent = `${facturas.length} facturas`;
    document.getElementById('sucursalDatabase').textContent = sucursal.database.name;
    document.getElementById('sucursalInfo').style.display = 'block';
}

// Mostrar lista de facturas
function mostrarFacturas() {
    const container = document.getElementById('facturasContainer');
    
    if (facturas.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-file-invoice"></i>
                    <h4>No hay facturas</h4>
                    <p>No se encontraron facturas para esta sucursal</p>
                </div>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    facturas.forEach(factura => {
        const productos = factura.products ? JSON.parse(factura.products) : [];
        const totalProductos = productos.length;
        const estado = factura.print ? 'Facturado' : 'Pendiente';
        const estadoClass = factura.print ? 'status-facturado' : 'status-aprobado';
        
        html += `
            <div class="col-md-6 col-lg-4">
                <div class="factura-card">
                    <div class="factura-header">
                        <span class="factura-id"># ${factura.id}</span>
                        <span class="factura-status ${estadoClass}">${estado}</span>
                    </div>
                    
                    <div class="factura-amount">$${formatNumber(factura.total)}</div>
                    
                    <div class="factura-details">
                        <div class="detail-item">
                            <span class="detail-label">Cliente:</span>
                            <div>${factura.client || 'Cliente General'}</div>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Fecha:</span>
                            <div>${formatDate(factura.created_at)}</div>
                        </div>
                    </div>
                    
                    <div class="products-summary">
                        <i class="fas fa-box me-1"></i>
                        ${totalProductos} producto(s)
                    </div>
                    
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm flex-fill" onclick="verDetalleFactura(${factura.id})">
                            <i class="fas fa-eye me-1"></i>Ver Detalle
                        </button>
                        ${factura.print ? `
                        <button class="btn btn-outline-danger btn-sm" onclick="prepararCancelarFactura(${factura.id})" title="Cancelar Factura">
                            <i class="fas fa-ban"></i>
                        </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = `<div class="row">${html}</div>`;
}

// Ver detalle de una factura
async function verDetalleFactura(facturaId) {
    const sucursalId = document.getElementById('sucursalSelect').value;
    
    console.log('[FACTURAS] 🔍 Viendo detalle de factura:', facturaId);
    
    const data = await hacerPeticion(`${API_BASE}/web/sucursal/${sucursalId}/factura/${facturaId}`);
    
    if (data && data.success && data.data) {
        facturaActual = data.data;
        mostrarModalDetalle();
    }
}

// Mostrar modal con detalle de factura
function mostrarModalDetalle() {
    if (!facturaActual) return;
    
    const productos = facturaActual.products ? JSON.parse(facturaActual.products) : [];
    
    let productosHtml = '';
    productos.forEach(producto => {
        productosHtml += `
            <tr>
                <td>${producto.name}</td>
                <td>${producto.quantity}</td>
                <td>$${formatNumber(producto.price)}</td>
                <td>$${formatNumber(producto.quantity * producto.price)}</td>
            </tr>
        `;
    });
    
    const contenido = `
        <div class="row">
            <div class="col-md-6">
                <h6>Información General</h6>
                <p><strong>ID:</strong> ${facturaActual.id}</p>
                <p><strong>Cliente:</strong> ${facturaActual.client || 'Cliente General'}</p>
                <p><strong>Total:</strong> $${formatNumber(facturaActual.total)}</p>
                <p><strong>Fecha:</strong> ${formatDate(facturaActual.created_at)}</p>
                <p><strong>Estado:</strong> ${facturaActual.print ? 'Facturado' : 'Pendiente'}</p>
            </div>
            <div class="col-md-6">
                <h6>Productos</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cant.</th>
                                <th>Precio</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${productosHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('detalleFacturaContent').innerHTML = contenido;
    
    const modal = new bootstrap.Modal(document.getElementById('detalleFacturaModal'));
    modal.show();
}

// Preparar cancelación de factura
function prepararCancelarFactura(facturaId) {
    const factura = facturas.find(f => f.id === facturaId);
    if (!factura) return;
    
    facturaActual = factura;
    
    // Pre-llenar formulario
    document.getElementById('cancel_app_id').value = document.getElementById('sucursalSelect').value;
    document.getElementById('cancel_pedido_id').value = facturaId;
    document.getElementById('folio_referencia').value = ''; // Usuario debe completar
    document.getElementById('fecha_referencia').value = facturaActual.created_at.split(' ')[0];
    
    // Cerrar modal de detalle si está abierto
    const modalDetalle = bootstrap.Modal.getInstance(document.getElementById('detalleFacturaModal'));
    if (modalDetalle) modalDetalle.hide();
    
    // Abrir modal de cancelación
    const modal = new bootstrap.Modal(document.getElementById('cancelarFacturaModal'));
    modal.show();
}

// Abrir modal cancelar desde el detalle
function abrirModalCancelarFactura() {
    if (!facturaActual) return;
    prepararCancelarFactura(facturaActual.id);
}

// Procesar nota de crédito
async function procesarNotaCredito() {
    const form = document.getElementById('formCancelarFactura');
    const formData = new FormData(form);
    
    // Validar formulario
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Confirmar acción
    const folio = formData.get('folio_referencia');
    if (!confirm(`¿Estás seguro de cancelar la factura con folio ${folio}?\n\nEsta acción generará una Nota de Crédito y no se puede deshacer.`)) {
        return;
    }
    
    console.log('[FACTURAS] 📝 Procesando nota de crédito...');
    
    // Preparar datos
    const datos = {
        app_id: parseInt(formData.get('app_id')),
        pedido_id: parseInt(formData.get('pedido_id')),
        folio_referencia: parseInt(formData.get('folio_referencia')),
        fecha_referencia: formData.get('fecha_referencia'),
        fecha_emision: formData.get('fecha_emision'),
        motivo_cancelacion: formData.get('motivo_cancelacion'),
        observaciones: formData.get('observaciones') || ''
    };
    
    console.log('[FACTURAS] 📤 Datos a enviar:', datos);
    
    const response = await hacerPeticion(`${API_BASE}/web/pedido/cancelar-factura`, {
        method: 'POST',
        body: JSON.stringify(datos)
    });
    
    if (response && response.success) {
        mostrarExito('✅ Nota de Crédito generada exitosamente');
        
        // Cerrar modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('cancelarFacturaModal'));
        modal.hide();
        
        // Recargar facturas
        await cargarFacturasSucursal();
        
        // Si hay PDF, descargarlo
        if (response.response_folio) {
            descargarNotaCreditoPDF(response.response_folio);
        }
    } else {
        mostrarError(`❌ Error al generar nota de crédito: ${response?.message || 'Error desconocido'}`);
    }
}

// Descargar PDF de nota de crédito
function descargarNotaCreditoPDF(base64PDF) {
    try {
        const blob = new Blob([Uint8Array.from(atob(base64PDF), c => c.charCodeAt(0))], {
            type: 'application/pdf'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `nota_credito_${Date.now()}.pdf`;
        a.click();
        URL.revokeObjectURL(url);
    } catch (error) {
        console.error('[FACTURAS] ❌ Error al descargar PDF:', error);
        mostrarError('Error al descargar el PDF de la nota de crédito');
    }
}

// Descargar PDF de factura
async function descargarPDF() {
    if (!facturaActual) return;
    
    const sucursalId = document.getElementById('sucursalSelect').value;
    const url = `${API_BASE}/web/sucursal/${sucursalId}/factura/${facturaActual.id}/pdf`;
    
    console.log('[FACTURAS] ⬇️ Descargando PDF:', url);
    
    try {
        const credentials = getCredentials();
        const response = await fetch(url, { headers: credentials });
        
        if (response.ok) {
            const blob = await response.blob();
            const downloadUrl = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = `factura_${facturaActual.id}.pdf`;
            a.click();
            URL.revokeObjectURL(downloadUrl);
        } else {
            mostrarError('Error al descargar el PDF');
        }
    } catch (error) {
        console.error('[FACTURAS] ❌ Error al descargar PDF:', error);
        mostrarError('Error al descargar el PDF');
    }
}

// Limpiar filtros
function limpiarFiltros() {
    document.getElementById('sucursalSelect').value = '';
    document.getElementById('sucursalInfo').style.display = 'none';
    document.getElementById('facturasContainer').innerHTML = `
        <div class="col-12">
            <div class="empty-state">
                <i class="fas fa-file-invoice"></i>
                <h4>Selecciona una sucursal</h4>
                <p>Escoge una sucursal para ver sus facturas generadas</p>
            </div>
        </div>
    `;
    facturas = [];
}

// Funciones de utilidad
function formatNumber(number) {
    return new Intl.NumberFormat('es-CL').format(number);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('es-CL');
}

function mostrarError(mensaje) {
    console.error('[FACTURAS] ❌', mensaje);
    alert(mensaje);
}

function mostrarExito(mensaje) {
    console.log('[FACTURAS] ✅', mensaje);
    alert(mensaje);
}

// Funciones adicionales de debugging
function sincronizarLlaves() {
    const appKey = prompt('Ingresa tu App Key:');
    const authToken = prompt('Ingresa tu Auth Token:');
    
    if (appKey && authToken) {
        localStorage.setItem('app_key', appKey);
        localStorage.setItem('auth_token', authToken);
        mostrarExito('Credenciales sincronizadas. Recarga la página.');
    }
}

function limpiarCredenciales() {
    localStorage.removeItem('app_key');
    localStorage.removeItem('auth_token');
    mostrarExito('Credenciales eliminadas. Ve al dashboard para hacer login nuevamente.');
}

console.log('[FACTURAS] ✅ Sistema listo');
