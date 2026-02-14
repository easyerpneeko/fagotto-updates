// ============================================
// Stock por Sucursales - JavaScript
// ============================================
// VERSION: 2026-02-13 12:30 - Filtrar antes de agrupar en Resumen

// Configurar moment.js en español
moment.locale('es');

console.log('🚀 Stock Sucursales cargado - VERSION: 2026-02-13 12:30');

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
        console.log('📊 Cargando resumen desde historial_stock_diario...');
        
        // Usar endpoint de historial diario - obtiene últimos registros
        const url = generarURLApi('/local/pedidofinal/historial-dia');
        
        await __conection({
            url: url,
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function(request) {
            const result = request.data || request;
            
            let todosLosRegistros = [];
            // Detectar formato de respuesta
            if (Array.isArray(result)) {
                todosLosRegistros = result;
            } else if (result && Array.isArray(result.data)) {
                todosLosRegistros = result.data;
            } else {
                todosLosRegistros = [];
                console.warn('⚠️ Formato de respuesta inesperado');
            }
            
            // AGRUPAR: Obtener solo el último registro por negocio-producto
            const registrosPorClave = {};
            todosLosRegistros.forEach(item => {
                const clave = `${item.id_negocio}_${item.id_producto}`;
                // Usar created_at si existe (timestamp completo), sino fecha_reporte
                const fechaActualStr = item.created_at || (item.fecha_reporte + ' 00:00:00');
                const fechaActual = new Date(fechaActualStr);
                
                if (!registrosPorClave[clave]) {
                    registrosPorClave[clave] = item;
                } else {
                    const fechaExistenteStr = registrosPorClave[clave].created_at || (registrosPorClave[clave].fecha_reporte + ' 00:00:00');
                    const fechaExistente = new Date(fechaExistenteStr);
                    if (fechaActual > fechaExistente) {
                        registrosPorClave[clave] = item;
                    }
                }
            });
            
            datosResumen = Object.values(registrosPorClave);
            console.log('✅ Resumen cargado:', datosResumen.length, 'productos únicos de', todosLosRegistros.length, 'registros totales');
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
        console.log('📜 Cargando historial desde historial_stock_diario...');
        
        // Usar endpoint de historial diario - sin fecha para obtener todos
        const url = generarURLApi('/local/pedidofinal/historial-dia');
        
        await __conection({
            url: url,
            header: credentials(),
            dev: true,
            method: 'GET'
        }, {}, function(request) {
            const result = request.data || request;
            
            // Detectar formato de respuesta
            if (Array.isArray(result)) {
                datosHistorial = result;
            } else if (result && Array.isArray(result.data)) {
                datosHistorial = result.data;
            } else {
                datosHistorial = [];
                console.warn('⚠️ Formato de respuesta inesperado');
            }
            
            console.log('✅ Historial cargado:', datosHistorial.length, 'registros');
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
    console.log('📊 Datos antes de filtrar:', { resumen: datosResumen.length, historial: datosHistorial.length });
    
    // Obtener el nombre del local seleccionado
    let nombreLocalSeleccionado = '';
    if (idLocal) {
        const localEncontrado = locales.find(l => String(l.id) === String(idLocal));
        nombreLocalSeleccionado = localEncontrado ? (localEncontrado.name || '').toLowerCase() : '';
        console.log('🏪 Filtrando por local:', nombreLocalSeleccionado, '(ID:', idLocal, ')');
    }
    
    // RESUMEN: Filtrar PRIMERO desde historial completo, luego agrupar
    console.log('📊 RESUMEN: Filtrando desde historial completo antes de agrupar...');
    let resumenFiltrado = [...datosHistorial];
    
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
        console.log(`🔎 FILTRANDO RESUMEN por: "${textoProducto}"`);
        const antesResumen = resumenFiltrado.length;
        let contadorCoincidencias = 0;
        
        resumenFiltrado = resumenFiltrado.filter((item, idx) => {
            // Buscar en múltiples campos
            const producto = String(item.producto_nombre || '').toLowerCase();
            const cantidad = String(item.cantidad_reportada || '');
            const idProducto = String(item.id_producto || '');
            const unidad = String(item.unidad_medida || '').toLowerCase();
            const negocio = String(item.nombre_negocio || '').toLowerCase();
            const usuario = String(item.usuario || '').toLowerCase();
            
            // Buscar también en la fecha formateada
            let fechaFormateada = '';
            if (item.created_at || item.fecha_reporte) {
                const fechaStr = item.created_at || (item.fecha_reporte + ' 00:00:00');
                fechaFormateada = moment(fechaStr).format('DD/MM/YYYY');
            }
            
            const coincide = producto.includes(textoProducto) ||
                           cantidad.includes(textoProducto) ||
                           idProducto.includes(textoProducto) ||
                           unidad.includes(textoProducto) ||
                           negocio.includes(textoProducto) ||
                           usuario.includes(textoProducto) ||
                           fechaFormateada.includes(textoProducto);
            
            // Log solo de los que SÍ coinciden (primeros 10)
            if (coincide && contadorCoincidencias < 10) {
                console.log(`  ✅ MATCH #${contadorCoincidencias + 1}: ${producto.substring(0, 30)} | Fecha: ${fechaFormateada} | Cant: ${cantidad}`);
                contadorCoincidencias++;
            }
            
            return coincide;
        });
        console.log(`✅ Filtro aplicado: ${antesResumen} → ${resumenFiltrado.length} (resumen)`);
    }
    
    if (fechaDesde) {
        console.log(`📅 Filtrando por fecha DESDE: ${fechaDesde}`);
        const antesFechaDesde = resumenFiltrado.length;
        
        resumenFiltrado = resumenFiltrado.filter((item, idx) => {
            const fechaItem = moment(item.fecha_reporte);
            const fechaFiltro = moment(fechaDesde);
            const cumple = fechaItem.isSameOrAfter(fechaFiltro);
            
            // Log primeros 3
            if (idx < 3) {
                console.log(`  Item: fecha_reporte="${item.fecha_reporte}" → moment="${fechaItem.format('YYYY-MM-DD')}" >= "${fechaFiltro.format('YYYY-MM-DD')}" = ${cumple}`);
            }
            
            return cumple;
        });
        console.log(`  Después fechaDesde: ${antesFechaDesde} → ${resumenFiltrado.length}`);
    }
    
    if (fechaHasta) {
        console.log(`📅 Filtrando por fecha HASTA: ${fechaHasta}`);
        const antesFechaHasta = resumenFiltrado.length;
        
        resumenFiltrado = resumenFiltrado.filter((item, idx) => {
            const fechaItem = moment(item.fecha_reporte);
            const fechaFiltro = moment(fechaHasta).endOf('day');
            const cumple = fechaItem.isSameOrBefore(fechaFiltro);
            
            // Log primeros 3
            if (idx < 3) {
                console.log(`  Item: fecha_reporte="${item.fecha_reporte}" → moment="${fechaItem.format('YYYY-MM-DD')}" <= "${fechaFiltro.format('YYYY-MM-DD')}" = ${cumple}`);
            }
            
            return cumple;
        });
        console.log(`  Después fechaHasta: ${antesFechaHasta} → ${resumenFiltrado.length}`);
    }
    
    // AGRUPAR registros filtrados: Mantener solo el último por negocio-producto
    console.log('🔄 Agrupando registros filtrados por negocio-producto...');
    const registrosPorClave = {};
    resumenFiltrado.forEach(item => {
        const clave = `${item.id_negocio}_${item.id_producto}`;
        const fechaActualStr = item.created_at || (item.fecha_reporte + ' 00:00:00');
        const fechaActual = new Date(fechaActualStr);
        
        if (!registrosPorClave[clave]) {
            registrosPorClave[clave] = item;
        } else {
            const fechaExistenteStr = registrosPorClave[clave].created_at || (registrosPorClave[clave].fecha_reporte + ' 00:00:00');
            const fechaExistente = new Date(fechaExistenteStr);
            if (fechaActual > fechaExistente) {
                registrosPorClave[clave] = item;
            }
        }
    });
    resumenFiltrado = Object.values(registrosPorClave);
    console.log(`✅ Después de agrupar: ${Object.keys(registrosPorClave).length} productos únicos`);
    
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
        console.log(`🔎 FILTRANDO HISTORIAL por: "${textoProducto}"`);
        const antesHistorial = historialFiltrado.length;
        let contadorCoincidencias = 0;
        
        historialFiltrado = historialFiltrado.filter((item, idx) => {
            // Buscar en múltiples campos
            const producto = String(item.producto_nombre || '').toLowerCase();
            const cantidad = String(item.cantidad_reportada || '');
            const idProducto = String(item.id_producto || '');
            const unidad = String(item.unidad_medida || '').toLowerCase();
            const negocio = String(item.nombre_negocio || '').toLowerCase();
            const usuario = String(item.usuario || '').toLowerCase();
            
            // Buscar también en la fecha formateada
            let fechaFormateada = '';
            if (item.created_at || item.fecha_reporte) {
                const fechaStr = item.created_at || (item.fecha_reporte + ' 00:00:00');
                fechaFormateada = moment(fechaStr).format('DD/MM/YYYY');
            }
            
            const coincide = producto.includes(textoProducto) ||
                           cantidad.includes(textoProducto) ||
                           idProducto.includes(textoProducto) ||
                           unidad.includes(textoProducto) ||
                           negocio.includes(textoProducto) ||
                           usuario.includes(textoProducto) ||
                           fechaFormateada.includes(textoProducto);
            
            // Log solo de los que SÍ coinciden (primeros 10)
            if (coincide && contadorCoincidencias < 10) {
                console.log(`  ✅ MATCH #${contadorCoincidencias + 1}: ${producto.substring(0, 30)} | Fecha: ${fechaFormateada} | Cant: ${cantidad}`);
                contadorCoincidencias++;
            }
            
            return coincide;
        });
        console.log(`✅ Filtro aplicado: ${antesHistorial} → ${historialFiltrado.length} (historial)`);
    }
    
    if (fechaDesde) {
        console.log(`📅 HISTORIAL - Filtrando por fecha DESDE: ${fechaDesde}`);
        const antesFechaDesde = historialFiltrado.length;
        
        historialFiltrado = historialFiltrado.filter((item, idx) => {
            const fechaItem = moment(item.fecha_reporte);
            const fechaFiltro = moment(fechaDesde);
            const cumple = fechaItem.isSameOrAfter(fechaFiltro);
            
            // Log primeros 3
            if (idx < 3) {
                console.log(`  Item: fecha_reporte="${item.fecha_reporte}" → moment="${fechaItem.format('YYYY-MM-DD')}" >= "${fechaFiltro.format('YYYY-MM-DD')}" = ${cumple}`);
            }
            
            return cumple;
        });
        console.log(`  Después fechaDesde: ${antesFechaDesde} → ${historialFiltrado.length}`);
    }
    
    if (fechaHasta) {
        console.log(`📅 HISTORIAL - Filtrando por fecha HASTA: ${fechaHasta}`);
        const antesFechaHasta = historialFiltrado.length;
        
        historialFiltrado = historialFiltrado.filter((item, idx) => {
            const fechaItem = moment(item.fecha_reporte);
            const fechaFiltro = moment(fechaHasta).endOf('day');
            const cumple = fechaItem.isSameOrBefore(fechaFiltro);
            
            // Log primeros 3
            if (idx < 3) {
                console.log(`  Item: fecha_reporte="${item.fecha_reporte}" → moment="${fechaItem.format('YYYY-MM-DD')}" <= "${fechaFiltro.format('YYYY-MM-DD')}" = ${cumple}`);
            }
            
            return cumple;
        });
        console.log(`  Después fechaHasta: ${antesFechaHasta} → ${historialFiltrado.length}`);
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
    console.log('🎨 Renderizando RESUMEN con', datos.length, 'items');
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
    
    console.log('📝 Primeros 3 items a renderizar:', datos.slice(0, 3).map(d => ({ 
        producto: d.producto_nombre, 
        fecha: d.fecha_reporte,
        cantidad: d.cantidad_reportada
    })));
    
    datos.forEach(item => {
        const tipo = detectarTipoMedida(item.unidad_medida);
        const tipoTexto = tipo.charAt(0).toUpperCase() + tipo.slice(1);
        const cantidad = formatearCantidad(item.cantidad_reportada, item.unidad_medida);
        
        // Usar created_at si existe, sino fecha_reporte
        let fechaCompleta;
        if (item.created_at) {
            fechaCompleta = item.created_at;
        } else if (item.fecha_reporte) {
            fechaCompleta = item.fecha_reporte + ' 00:00:00';
        } else {
            fechaCompleta = null;
        }
        
        const fecha = fechaCompleta ? moment(fechaCompleta).format('DD/MM/YYYY HH:mm') : 'Sin fecha';
        
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
    console.log('🎨 Renderizando HISTORIAL con', datos.length, 'items');
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
        
        // Usar created_at si existe, sino fecha_reporte
        let fechaCompleta;
        if (item.created_at) {
            fechaCompleta = item.created_at;
        } else if (item.fecha_reporte) {
            fechaCompleta = item.fecha_reporte + ' 00:00:00';
        } else {
            fechaCompleta = null;
        }
        
        const fecha = fechaCompleta ? moment(fechaCompleta).format('DD/MM/YYYY HH:mm') : 'Sin fecha';
        
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
