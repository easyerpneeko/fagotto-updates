/**
 * Arqueo de Caja - JavaScript Functions
 * Funciones para manejar el sistema de arqueo de caja
 */

class ArqueoCaja {
    constructor() {
        this.denominations = {
            'bill_20000': 20000,
            'bill_10000': 10000,
            'bill_5000': 5000,
            'bill_2000': 2000,
            'bill_1000': 1000,
            'coin_500': 500,
            'coin_100': 100,
            'coin_50': 50,
            'coin_10': 10
        };
        
        this.ventasEfectivoDelDia = 0;
        this.totalVentasDelDia = 0;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.cargarDatosIniciales();
    }

    bindEvents() {
        // Event listeners para cálculo automático
        $('.denomination-input').on('input', () => {
            this.calculateTotal();
            this.calcularDiferencia();
        });

        // Botón guardar arqueo
        $('#guardarArqueo').on('click', () => {
            this.guardarArqueo();
        });

        // Botón limpiar formulario
        $('#limpiarFormulario').on('click', () => {
            this.limpiarFormulario();
        });

        // Botón imprimir arqueo
        $('#imprimirArqueo').on('click', () => {
            this.imprimirArqueo();
        });
    }

    /**
     * Calcular el total del conteo
     */
    calculateTotal() {
        let total = 0;
        
        for (let id in this.denominations) {
            let cantidad = parseInt($('#' + id).val()) || 0;
            total += cantidad * this.denominations[id];
        }
        
        $('#totalCounted').text('$' + this.formatMoney(total));
        return total;
    }

    /**
     * Calcular diferencia entre contado y ventas en efectivo
     */
    calcularDiferencia() {
        let totalContado = this.calculateTotal();
        let diferencia = totalContado - this.ventasEfectivoDelDia;
        
        $('#diferencia').text('$' + this.formatMoney(diferencia));
        $('#diferencia').removeClass('text-success text-danger text-warning');
        
        if (diferencia === 0) {
            $('#diferencia').addClass('text-success');
        } else if (diferencia > 0) {
            $('#diferencia').addClass('text-warning');
        } else {
            $('#diferencia').addClass('text-danger');
        }
        
        return diferencia;
    }

    /**
     * Cargar datos iniciales del día
     */
    cargarDatosIniciales() {
        this.cargarResumenDia();
        this.cargarHistorialArqueos();
        this.calculateTotal();
    }

    /**
     * Cargar resumen de ventas del día
     */
    cargarResumenDia() {
        const today = new Date();
        const startDate = today.toISOString().split('T')[0];
        const endDate = startDate;

        $.ajax({
            url: generarURLApi(`/local/report/arqueo-resumen?startDate=${startDate}&endDate=${endDate}`),
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${getTokenFromStorage()}`
            },
            success: (response) => {
                if (response.success) {
                    this.ventasEfectivoDelDia = response.ventasEfectivo || 0;
                    this.totalVentasDelDia = response.totalVentas || 0;
                    
                    $('#ventasEfectivo').text('$' + this.formatMoney(this.ventasEfectivoDelDia));
                    $('#totalVentas').text('$' + this.formatMoney(this.totalVentasDelDia));
                    
                    this.calcularDiferencia();
                } else {
                    this.showError('Error al cargar resumen del día');
                }
            },
            error: (xhr, status, error) => {
                console.error('Error al cargar resumen del día:', error);
                this.showError('Error de conexión al cargar datos del día');
            }
        });
    }

    /**
     * Cargar historial de arqueos
     */
    cargarHistorialArqueos() {
        $.ajax({
            url: generarURLApi('/local/arqueo/historial'),
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${getTokenFromStorage()}`
            },
            success: (response) => {
                if (response.success && response.data) {
                    this.renderHistorial(response.data);
                } else {
                    $('#historialArqueos').html(this.getEmptyHistorialHTML());
                }
            },
            error: (xhr, status, error) => {
                console.error('Error al cargar historial:', error);
                $('#historialArqueos').html(this.getErrorHistorialHTML());
            }
        });
    }

    /**
     * Renderizar historial de arqueos
     */
    renderHistorial(arqueos) {
        let html = '';
        
        if (arqueos.length > 0) {
            arqueos.forEach((arqueo) => {
                let fecha = new Date(arqueo.created_at).toLocaleDateString('es-CL');
                let hora = new Date(arqueo.created_at).toLocaleTimeString('es-CL', {
                    hour: '2-digit', 
                    minute: '2-digit'
                });
                
                let estadoClass = this.getEstadoClass(arqueo.diferencia);
                let estadoText = this.getEstadoText(arqueo.diferencia);
                
                html += `
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="fw-bold">$${this.formatMoney(arqueo.total_contado)}</span>
                                <span class="badge ${estadoClass} ms-2">${estadoText}</span>
                            </div>
                            <small class="text-muted">${fecha}<br>${hora}</small>
                        </div>
                        <small class="text-muted d-block">
                            Diferencia: $${this.formatMoney(arqueo.diferencia)}
                        </small>
                        <small class="text-muted">Usuario: ${arqueo.usuario_nombre || 'Sistema'}</small>
                    </div>
                `;
            });
        } else {
            html = this.getEmptyHistorialHTML();
        }
        
        $('#historialArqueos').html(html);
    }

    /**
     * Guardar arqueo
     */
    guardarArqueo() {
        let total = this.calculateTotal();
        
        if (total === 0) {
            this.showWarning('Debe ingresar al menos una denominación');
            return;
        }

        // Obtener detalle del conteo
        let detalleConteo = this.getDetalleConteo();
        
        // Validar que tenga al menos un valor
        if (Object.keys(detalleConteo).length === 0) {
            this.showWarning('Debe ingresar al menos una denominación');
            return;
        }

        let data = {
            total_contado: total,
            detalle_conteo: JSON.stringify(detalleConteo),
            observaciones: $('#observaciones').val(),
            fecha_arqueo: new Date().toISOString().split('T')[0]
        };

        // Mostrar loading
        this.showLoading();

        $.ajax({
            url: generarURLApi('/local/arqueo/guardar'),
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${getTokenFromStorage()}`,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(data),
            success: (response) => {
                this.hideLoading();
                
                if (response.success) {
                    this.showSuccess('Arqueo guardado correctamente');
                    this.limpiarFormulario();
                    this.cargarHistorialArqueos();
                    this.cargarResumenDia();
                } else {
                    this.showError(response.message || 'Error al guardar el arqueo');
                }
            },
            error: (xhr, status, error) => {
                this.hideLoading();
                console.error('Error al guardar arqueo:', error);
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    this.showError(xhr.responseJSON.message);
                } else {
                    this.showError('Error al guardar el arqueo. Intente nuevamente.');
                }
            }
        });
    }

    /**
     * Obtener detalle del conteo
     */
    getDetalleConteo() {
        let detalle = {};
        
        for (let id in this.denominations) {
            let cantidad = parseInt($('#' + id).val()) || 0;
            if (cantidad > 0) {
                detalle[id] = {
                    cantidad: cantidad,
                    valor: this.denominations[id],
                    subtotal: cantidad * this.denominations[id]
                };
            }
        }
        
        return detalle;
    }

    /**
     * Limpiar formulario
     */
    limpiarFormulario() {
        $('.denomination-input').val(0);
        $('#observaciones').val('');
        this.calculateTotal();
        this.calcularDiferencia();
    }

    /**
     * Imprimir arqueo
     */
    imprimirArqueo() {
        let total = this.calculateTotal();
        let diferencia = this.calcularDiferencia();
        let detalleConteo = this.getDetalleConteo();
        
        if (total === 0) {
            this.showWarning('No hay datos para imprimir');
            return;
        }

        // Crear contenido para imprimir
        let printContent = this.generatePrintContent(total, diferencia, detalleConteo);
        
        // Abrir ventana de impresión
        let printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.print();
    }

    /**
     * Generar contenido para imprimir
     */
    generatePrintContent(total, diferencia, detalle) {
        let fecha = new Date().toLocaleDateString('es-CL');
        let hora = new Date().toLocaleTimeString('es-CL');
        let observaciones = $('#observaciones').val();
        
        let detalleHTML = '';
        for (let id in detalle) {
            let item = detalle[id];
            detalleHTML += `
                <tr>
                    <td>$${this.formatMoney(item.valor)}</td>
                    <td>${item.cantidad}</td>
                    <td>$${this.formatMoney(item.subtotal)}</td>
                </tr>
            `;
        }
        
        return `
            <html>
            <head>
                <title>Arqueo de Caja - ${fecha}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .total { font-size: 18px; font-weight: bold; }
                    .diferencia { margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>ARQUEO DE CAJA</h1>
                    <p>Fecha: ${fecha} - Hora: ${hora}</p>
                </div>
                
                <h3>Detalle del Conteo</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Denominación</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${detalleHTML}
                    </tbody>
                </table>
                
                <div class="total">
                    <p>Total Contado: $${this.formatMoney(total)}</p>
                </div>
                
                <div class="diferencia">
                    <p>Ventas en Efectivo: $${this.formatMoney(this.ventasEfectivoDelDia)}</p>
                    <p>Diferencia: $${this.formatMoney(diferencia)}</p>
                </div>
                
                ${observaciones ? `<div><h3>Observaciones</h3><p>${observaciones}</p></div>` : ''}
            </body>
            </html>
        `;
    }

    // Utilidades
    formatMoney(amount) {
        return parseInt(amount).toLocaleString('es-CL');
    }

    getEstadoClass(diferencia) {
        if (diferencia === 0) return 'bg-success';
        if (diferencia > 0) return 'bg-warning';
        return 'bg-danger';
    }

    getEstadoText(diferencia) {
        if (diferencia === 0) return 'Exacto';
        if (diferencia > 0) return 'Sobrante';
        return 'Faltante';
    }

    getEmptyHistorialHTML() {
        return '<div class="text-center text-muted py-3"><i class="fas fa-inbox fa-2x mb-2"></i><p>No hay arqueos registrados</p></div>';
    }

    getErrorHistorialHTML() {
        return '<div class="text-center text-muted py-3"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Error al cargar historial</p></div>';
    }

    // Mensajes
    showSuccess(message) {
        this.showToast(message, 'success');
    }

    showError(message) {
        this.showToast(message, 'danger');
    }

    showWarning(message) {
        this.showToast(message, 'warning');
    }

    showToast(message, type) {
        // Crear toast dinámicamente
        let toastId = 'toast-' + Date.now();
        let toastHTML = `
            <div id="${toastId}" class="toast position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert">
                <div class="toast-header bg-${type} text-white">
                    <strong class="me-auto">Arqueo de Caja</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        $('body').append(toastHTML);
        let toast = new bootstrap.Toast(document.getElementById(toastId));
        toast.show();
        
        // Remover después de 5 segundos
        setTimeout(() => {
            $('#' + toastId).remove();
        }, 5000);
    }

    showLoading() {
        $('#guardarArqueo').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...');
    }

    hideLoading() {
        $('#guardarArqueo').prop('disabled', false).html('<i class="fas fa-save me-2"></i>Guardar Arqueo');
    }
}

// Inicializar cuando el documento esté listo
$(document).ready(function() {
    window.arqueoCajaApp = new ArqueoCaja();
});
