// ============================================
// Stock por Sucursales - JavaScript
// ============================================

// Configurar moment.js en español
moment.locale('es');

// Variables globales
let datosResumen = [];
let datosHistorial = [];
let locales = [];
let vistaActual = 'resumen';

// ============================================
// INICIALIZACIÓN
// ============================================

$(document).ready(function() {
    console.log('🚀 Iniciando Stock Sucursales...');
    cargarLocales();
    cargarDatos();
    
    // Event listeners para filtros en tiempo real
    $('#filtroProducto').on('input', function() {
        aplicarFiltros();
    });
});

// ============================================
// CARGAR LOCALES
// ============================================

async function cargarLocales() {
    try {
        console.log('📍 Cargando locales...');
        
        const url = generarURLApi('/getApps');
        
        await __conection({
            url: url,
            header: credentials(),
            dev: true,
            method: 'POST'
        }, {}, function(request) {
            if (request && Array.isArray(request)) {
                locales = request;
                console.log('✅ Locales cargados:', locales.length);
                
                // Llenar el select
                const $select = $('#filtroLocal');
                $select.empty();
                $select.append('<option value="">Todas las sucursales</option>');
                
                locales.forEach(local => {
                    const nombre = local.name || 'Sin nombre';
                    const id = local.id;
                    $select.append(`<option value="${id}">${nombre}</option>`);
                });
            } else {
                console.warn('⚠️ No se encontraron locales');
            }
        });
    } catch (error) {
        console.error('❌ Error cargando locales:', error);
    }
}

// ============================================
// CARGAR DATOS
// ============================================

async function cargarDatos() {
    mostrarLoading(true);
    
    try {
        await Promise.all([
            cargarResumen(),
            cargarHistorial()
        ]);
        
        aplicarFiltros();
    } catch (error) {
        console.error('❌ Error cargando datos:', error);
        alert('Error al cargar los datos. Por favor, intenta nuevamente.');
    } finally {
        mostrarLoading(false);
    }
}

// ============================================
// CARGAR RESUMEN
// ============================================

async function cargarResumen() {
    try {
        console.log('📊 Cargando resumen...');
        
        // USAR ENDPOINT ADMIN QUE NO FILTRA POR LLAVE
        const url = generarURLApi('/local/admin/pedidofinal/stock-negocio/resumen');
        
        await __conection({
            url: url,
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function(request) {
            console.log('🔍 DEBUG Resumen - request completo:', request);
            console.log('🔍 DEBUG Resumen - request.data:', request.data);
            
            const result = request.data || request;
            
            // Detectar formato de respuesta
            if (Array.isArray(result)) {
                datosResumen = result;
                console.log('✅ Caso 1: Array directo con', result.length, 'items');
            } else if (result && Array.isArray(result.data)) {
                datosResumen = result.data;
                console.log('✅ Caso 2: result.data con', result.data.length, 'items');
            } else {
                datosResumen = [];
                console.log('⚠️ No se detectó formato correcto');
            }
            
            console.log('✅ Resumen cargado:', datosResumen.length, 'registros');
            console.log('📋 Datos del resumen:', datosResumen);
        });
    } catch (error) {
        console.error('❌ Error cargando resumen:', error);
        datosResumen = [];
    }
}

// ============================================
// CARGAR HISTORIAL
// ============================================

async function cargarHistorial() {
    try {
        console.log('📜 Cargando historial...');
        
        // USAR ENDPOINT ADMIN QUE NO FILTRA POR LLAVE
        const url = generarURLApi('/local/admin/pedidofinal/stock-negocio');
        
        await __conection({
            url: url,
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function(request) {
            console.log('🔍 DEBUG Historial - request completo:', request);
            console.log('🔍 DEBUG Historial - request.data:', request.data);
            
            const result = request.data || request;
            
            // Detectar formato de respuesta
            if (Array.isArray(result)) {
                datosHistorial = result;
                console.log('✅ Caso 1: Array directo con', result.length, 'items');
            } else if (result && Array.isArray(result.data)) {
                datosHistorial = result.data;
                console.log('✅ Caso 2: result.data con', result.data.length, 'items');
            } else {
                datosHistorial = [];
                console.log('⚠️ No se detectó formato correcto');
            }
            
            console.log('✅ Historial cargado:', datosHistorial.length, 'registros');
            console.log('📋 Datos del historial:', datosHistorial);
        });
    } catch (error) {
        console.error('❌ Error cargando historial:', error);
        datosHistorial = [];
    }
}

// ============================================
// APLICAR FILTROS
// ============================================

function aplicarFiltros() {
    const idLocal = $('#filtroLocal').val();
    const textoProducto = $('#filtroProducto').val().toLowerCase().trim();
    const fechaDesde = $('#filtroFechaDesde').val();
    const fechaHasta = $('#filtroFechaHasta').val();
    
    console.log('🔍 Aplicando filtros:', { idLocal, textoProducto, fechaDesde, fechaHasta });
    
    // Obtener el nombre del local seleccionado
    let nombreLocalSeleccionado = '';
    if (idLocal) {
        const localEncontrado = locales.find(l => String(l.id) === String(idLocal));
        nombreLocalSeleccionado = localEncontrado ? (localEncontrado.name || '').toLowerCase() : '';
        console.log('🏪 Filtrando por local:', nombreLocalSeleccionado, '(ID:', idLocal, ')');
    }
    
    // Filtrar resumen
    let resumenFiltrado = [...datosResumen];
    
    if (idLocal && nombreLocalSeleccionado) {
        resumenFiltrado = resumenFiltrado.filter(item => {
            const idNegocio = String(item.id_negocio || '');
            const nombreNegocio = String(item.nombre_negocio || '').toLowerCase();
            
            // Comparar por ID o por nombre (usar == para comparación flexible)
            const coincideId = idNegocio == idLocal;
            const coincideNombre = nombreNegocio.includes(nombreLocalSeleccionado);
            
            // Debug
            if (idNegocio || nombreNegocio) {
                console.log(`   🔸 Item: id_negocio=${idNegocio}, nombre="${nombreNegocio}"`);
                console.log(`      Coincide ID: ${coincideId}, Coincide Nombre: ${coincideNombre}`);
            }
            
            return coincideId || coincideNombre;
        });
    }
    
    if (textoProducto) {
        resumenFiltrado = resumenFiltrado.filter(item => {
            const producto = String(item.producto_nombre || '').toLowerCase();
            return producto.includes(textoProducto);
        });
    }
    
    if (fechaDesde) {
        resumenFiltrado = resumenFiltrado.filter(item => {
            return moment(item.fecha_registro).isSameOrAfter(moment(fechaDesde));
        });
    }
    
    if (fechaHasta) {
        resumenFiltrado = resumenFiltrado.filter(item => {
            return moment(item.fecha_registro).isSameOrBefore(moment(fechaHasta).endOf('day'));
        });
    }
    
    // Filtrar historial
    let historialFiltrado = [...datosHistorial];
    
    if (idLocal && nombreLocalSeleccionado) {
        historialFiltrado = historialFiltrado.filter(item => {
            const idNegocio = String(item.id_negocio || '');
            const nombreNegocio = String(item.nombre_negocio || '').toLowerCase();
            
            // Comparar por ID o por nombre (usar == para comparación flexible)
            const coincideId = idNegocio == idLocal;
            const coincideNombre = nombreNegocio.includes(nombreLocalSeleccionado);
            
            return coincideId || coincideNombre;
        });
    }
    
    if (textoProducto) {
        historialFiltrado = historialFiltrado.filter(item => {
            const producto = String(item.producto_nombre || '').toLowerCase();
            return producto.includes(textoProducto);
        });
    }
    
    if (fechaDesde) {
        historialFiltrado = historialFiltrado.filter(item => {
            return moment(item.fecha_registro).isSameOrAfter(moment(fechaDesde));
        });
    }
    
    if (fechaHasta) {
        historialFiltrado = historialFiltrado.filter(item => {
            return moment(item.fecha_registro).isSameOrBefore(moment(fechaHasta).endOf('day'));
        });
    }
    
    console.log('📊 Resultados filtrados:', resumenFiltrado.length, 'resumen,', historialFiltrado.length, 'historial');
    
    // Actualizar tablas
    renderizarResumen(resumenFiltrado);
    renderizarHistorial(historialFiltrado);
    
    // Actualizar estadísticas
    actualizarEstadisticas(resumenFiltrado, historialFiltrado);
}

// ============================================
// LIMPIAR FILTROS
// ============================================

function limpiarFiltros() {
    $('#filtroLocal').val('');
    $('#filtroProducto').val('');
    $('#filtroFechaDesde').val('');
    $('#filtroFechaHasta').val('');
    aplicarFiltros();
}

// ============================================
// CAMBIAR VISTA
// ============================================

function cambiarVista(vista) {
    vistaActual = vista;
    
    // Actualizar botones
    $('.tab-btn').removeClass('active');
    $(event.target).closest('.tab-btn').addClass('active');
    
    // Mostrar/ocultar vistas
    if (vista === 'resumen') {
        $('#vistaResumen').show();
        $('#vistaHistorial').hide();
    } else {
        $('#vistaResumen').hide();
        $('#vistaHistorial').show();
    }
}

// ============================================
// RENDERIZAR RESUMEN
// ============================================

function renderizarResumen(datos) {
    const $tbody = $('#tablaResumen');
    $tbody.empty();
    
    if (datos.length === 0) {
        $tbody.html(`
            <tr>
                <td colspan="6" class="no-data">
                    <i class="fas fa-inbox"></i>
                    <p>No hay datos para mostrar</p>
                </td>
            </tr>
        `);
        return;
    }
    
    datos.forEach(item => {
        const tipo = detectarTipoMedida(item.unidad_medida);
        const tipoTexto = tipo.charAt(0).toUpperCase() + tipo.slice(1);
        const cantidad = formatearCantidad(item.cantidad_reportada, item.unidad_medida);
        const fecha = moment(item.fecha_registro).format('DD/MM/YYYY HH:mm');
        
        $tbody.append(`
            <tr>
                <td>
                    <div class="local-cell">
                        <strong>${item.nombre_negocio || 'Sin nombre'}</strong>
                        ${item.app_id ? `<small>ID: ${item.app_id}</small>` : ''}
                    </div>
                </td>
                <td>${item.producto_nombre}</td>
                <td>
                    <span class="badge-tipo tipo-${tipo}">${tipoTexto}</span>
                </td>
                <td class="cantidad-cell">
                    <span class="cantidad">${cantidad}</span>
                    <small class="unidad-text">${item.unidad_medida}</small>
                </td>
                <td>${fecha}</td>
                <td>${item.usuario || '-'}</td>
            </tr>
        `);
    });
}

// ============================================
// RENDERIZAR HISTORIAL
// ============================================

function renderizarHistorial(datos) {
    const $tbody = $('#tablaHistorial');
    $tbody.empty();
    
    if (datos.length === 0) {
        $tbody.html(`
            <tr>
                <td colspan="7" class="no-data">
                    <i class="fas fa-inbox"></i>
                    <p>No hay datos para mostrar</p>
                </td>
            </tr>
        `);
        return;
    }
    
    datos.forEach(item => {
        const tipo = detectarTipoMedida(item.unidad_medida);
        const tipoTexto = tipo.charAt(0).toUpperCase() + tipo.slice(1);
        const cantidad = formatearCantidad(item.cantidad_reportada, item.unidad_medida);
        const fecha = moment(item.fecha_registro).format('DD/MM/YYYY HH:mm');
        
        $tbody.append(`
            <tr>
                <td>${fecha}</td>
                <td>
                    <div class="local-cell">
                        <strong>${item.nombre_negocio || 'Sin nombre'}</strong>
                        ${item.app_id ? `<small>ID: ${item.app_id}</small>` : ''}
                    </div>
                </td>
                <td>${item.producto_nombre}</td>
                <td>
                    <span class="badge-tipo tipo-${tipo}">${tipoTexto}</span>
                </td>
                <td class="cantidad-cell">
                    <span class="cantidad">${cantidad}</span>
                    <small class="unidad-text">${item.unidad_medida}</small>
                </td>
                <td>${item.usuario || '-'}</td>
                <td>${item.observacion || '-'}</td>
            </tr>
        `);
    });
}

// ============================================
// ACTUALIZAR ESTADÍSTICAS
// ============================================

function actualizarEstadisticas(resumen, historial) {
    // Contar sucursales únicas
    const sucursalesUnicas = new Set();
    resumen.forEach(item => {
        if (item.id_negocio) sucursalesUnicas.add(item.id_negocio);
    });
    
    // Contar productos únicos
    const productosUnicos = new Set();
    resumen.forEach(item => {
        if (item.id_producto) productosUnicos.add(item.id_producto);
    });
    
    $('#statSucursales').text(sucursalesUnicas.size);
    $('#statProductos').text(productosUnicos.size);
    $('#statRegistros').text(historial.length);
}

// ============================================
// UTILIDADES
// ============================================

function detectarTipoMedida(unidadMedida) {
    if (!unidadMedida) return 'unidades';
    const unidad = String(unidadMedida).toLowerCase().trim();
    
    if (unidad.includes('litro') || unidad.includes('lt') || unidad.includes('ml')) {
        return 'litros';
    }
    if (unidad.includes('kg') || unidad.includes('kilo') || unidad.includes('gr') || (unidad.includes('bolsa') && unidad.includes('k'))) {
        return 'kilos';
    }
    return 'unidades';
}

function formatearCantidad(cantidad, unidadMedida) {
    const tipo = detectarTipoMedida(unidadMedida);
    const num = parseFloat(cantidad) || 0;
    
    // Solo kilos permite decimales
    if (tipo === 'kilos') {
        return num.toFixed(2);
    }
    // Litros y unidades son enteros
    return Math.round(num).toString();
}

function mostrarLoading(mostrar) {
    if (mostrar) {
        $('#loadingSection').show();
        $('#vistaResumen').hide();
        $('#vistaHistorial').hide();
    } else {
        $('#loadingSection').hide();
        if (vistaActual === 'resumen') {
            $('#vistaResumen').show();
        } else {
            $('#vistaHistorial').show();
        }
    }
}
