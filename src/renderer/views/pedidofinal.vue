<template>
    <div class="pedido-final-container">
        <!-- Pantalla de Bienvenida -->
        <div v-if="!inicioSesion" class="welcome-screen">
            <div class="welcome-content">
                <div class="welcome-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h1 class="welcome-title">Bienvenido al Nuevo Sistema de Pedidos</h1>
                <p class="welcome-subtitle">Sistema de precios centralizados</p>
                <p class="welcome-description">
                    Realiza tus pedidos de forma rápida y sencilla.<br>
                    Selecciona los productos que necesitas y completa tu pedido en minutos.
                </p>
                <button @click="iniciarPedido" class="btn-iniciar">
                    <i class="fas fa-rocket"></i> Comenzar Pedido
                </button>
                <div class="welcome-info">
                    <div class="info-badge">
                        <i class="fas fa-check-circle"></i> Precios actualizados
                    </div>
                    <div class="info-badge">
                        <i class="fas fa-bolt"></i> Proceso rápido
                    </div>
                    <div class="info-badge">
                        <i class="fas fa-shield-alt"></i> Seguro
                    </div>
                </div>
            </div>
        </div>

        <!-- Interfaz Principal de Pedidos -->
        <div v-else class="pedidos-container">
            <div class="pedidos-main">
                <!-- Header Responsivo -->
                <div class="pedidos-header fade-in-up">
                    <div class="row align-items-center">
                        <div class="col-lg-5 col-md-6 col-12">
                            <div class="pedidos-title-card card">
                                <div class="card-body">
                                    <h5>🛒 Tu Pedido</h5>
                                    <span>{{ this.app && this.app.Name ? this.app.Name : 'Cargando...' }} • {{ this.date }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6 col-6 mt-3 mt-md-0">
                            <button @click="mostrarHistorial = !mostrarHistorial" class="btn-urgente w-100">
                                <i class="fas fa-history"></i>
                                {{ mostrarHistorial ? 'Pedido' : 'Historial' }}
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-6 col-6 mt-3 mt-md-0">
                            <button @click="mostrarModalPresupuesto = true" class="btn-urgente w-100" style="background: linear-gradient(45deg, #17a2b8, #138496);">
                                <i class="fas fa-info-circle"></i>
                                Presupuesto
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-6 col-6 mt-3 mt-md-0" style="display: none;">
                            <button @click="abrirAdminProductos" class="btn-urgente w-100" style="background: linear-gradient(45deg, #6c757d, #495057);">
                                <i class="fas fa-cog"></i>
                                Admin
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12 mt-3 mt-lg-0">
                            <div class="total-badge-responsive">
                                <span class="total-label">TOTAL:</span>
                                <span class="total-value">${{ formatNumber(totalConIVA) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Presupuesto de Pedidos Semanal -->
                    <div v-if="metaSemanal" class="row mt-3">
                        <div class="col-12">
                            <div class="presupuesto-card">
                                <div class="presupuesto-header">
                                    <i class="fas fa-chart-line"></i> Presupuesto de Pedidos
                                </div>
                                <div class="presupuesto-body">
                                    <div class="presupuesto-item">
                                        <span class="presupuesto-label">📊 Meta Semanal:</span>
                                        <span class="presupuesto-value">{{ formatCLP(metaSemanal.meta_semanal) }}</span>
                                    </div>
                                    <div class="presupuesto-item">
                                        <span class="presupuesto-label">🛒 Presupuesto Pedidos (30%):</span>
                                        <span class="presupuesto-value highlight">{{ formatCLP(metaSemanal.presupuesto_pedidos) }}</span>
                                    </div>
                                    <div class="presupuesto-item">
                                        <span class="presupuesto-label">📦 Por Despacho (÷3):</span>
                                        <span class="presupuesto-value highlight">{{ formatCLP(metaSemanal.presupuesto_por_despacho) }}</span>
                                    </div>
                                    <div class="presupuesto-progress" v-if="totalConIVA > 0">
                                        <div class="progress-info">
                                            <span>Pedido actual: {{ formatCLP(totalConIVA) }}</span>
                                            <span>{{ calcularPorcentajeDespacho() }}%</span>
                                        </div>
                                        <div class="progress-bar-container">
                                            <div class="progress-bar-fill" 
                                                 :style="{ width: calcularPorcentajeDespacho() + '%' }"
                                                 :class="{
                                                     'progress-ok': totalConIVA <= metaSemanal.presupuesto_por_despacho,
                                                     'progress-warning': totalConIVA > metaSemanal.presupuesto_por_despacho && totalConIVA <= metaSemanal.presupuesto_por_despacho * 1.1,
                                                     'progress-danger': totalConIVA > metaSemanal.presupuesto_por_despacho * 1.1
                                                 }">
                                            </div>
                                        </div>
                                        <div v-if="totalConIVA > metaSemanal.presupuesto_por_despacho" class="alert-exceso">
                                            ⚠️ Exceso: ${{ formatNumber(totalConIVA - metaSemanal.presupuesto_por_despacho) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de Pedidos -->
                <div v-if="mostrarHistorial" class="section-card fade-in-up">
                    <div class="section-card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <h3 class="m-0"><i class="fas fa-clock"></i> Historial de Pedidos ({{ historialPedidos.length }})</h3>
                            <button @click="cargarHistorial" class="btn-refresh-small" :disabled="loadingHistorial" style="background: transparent; border: 1px solid rgba(255,255,255,0.3); color: white; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer;">
                                <i class="fas fa-sync-alt" :class="{ 'fa-spin': loadingHistorial }"></i>
                            </button>
                        </div>
                    </div>
                    <div class="section-card-body">
                        <div v-if="loadingHistorial" class="loading-modern">
                            <i class="fas fa-spinner fa-spin"></i> Cargando historial...
                        </div>

                        <div v-else-if="!historialPedidos || historialPedidos.length === 0" class="empty-state fade-in-up">
                            <i class="fas fa-inbox fa-3x"></i>
                            <p>No hay pedidos registrados</p>
                        </div>

                        <div v-else-if="historialConProductosParsed.length === 0" class="empty-state fade-in-up">
                            <i class="fas fa-exclamation-triangle fa-3x"></i>
                            <p>Error procesando pedidos (computed vacío)</p>
                            <small>{{ historialPedidos.length }} pedidos en data pero 0 en computed</small>
                        </div>

                        <div v-else class="historial-grid">
                            <div v-for="pedido in historialConProductosParsed" :key="pedido.id" class="historial-card">
                        <div class="historial-card-header">
                            <div class="pedido-numero">
                                <i class="fas fa-hashtag"></i> {{ pedido.id }}
                            </div>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <div class="pedido-status" :class="'status-' + pedido.status">
                                    {{ pedido.status }}
                                </div>
                                <div v-if="pedido.payment_status" class="payment-badge" :class="'payment-' + pedido.payment_status">
                                    <i class="fas" :class="pedido.payment_status === 'paid' ? 'fa-check-circle' : pedido.payment_status === 'failed' ? 'fa-times-circle' : 'fa-clock'"></i>
                                    {{ pedido.payment_status === 'paid' ? 'Pagado' : pedido.payment_status === 'failed' ? 'Pago fallido' : 'Pendiente pago' }}
                                </div>
                            </div>
                        </div>
                        <div class="historial-card-body">
                            <div class="historial-info">
                                <div class="info-row">
                                    <i class="fas fa-calendar"></i>
                                    <span>{{ formatDate(pedido.created_at) }}</span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-user"></i>
                                    <span>{{ pedido.contact_name }}</span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-credit-card"></i>
                                    <span>{{ pedido.paymode }}</span>
                                </div>
                                <div class="info-row" v-if="pedido.comment">
                                    <i class="fas fa-comment"></i>
                                    <span>{{ pedido.comment }}</span>
                                </div>
                            </div>
                            <div class="historial-productos">
                                <h5>Productos:</h5>
                                <div v-for="(producto, index) in pedido.productosParsed" :key="index" class="producto-item">
                                    <span class="producto-qty">{{ producto.quantity }}x</span>
                                    <span class="producto-name">{{ producto.name }}</span>
                                    <span class="producto-price">${{ formatNumber(parseFloat(producto.price) * parseInt(producto.quantity)) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="historial-card-footer">
                            <div class="historial-breakdown">
                                <div class="historial-breakdown-row">
                                    <span class="historial-breakdown-label">NETO:</span>
                                    <span class="historial-breakdown-value">${{ formatNumber(calcularNetoHistorial(pedido)) }}</span>
                                </div>
                                <div class="historial-breakdown-row">
                                    <span class="historial-breakdown-label">EXENTO:</span>
                                    <span class="historial-breakdown-value">$0</span>
                                </div>
                                <div class="historial-breakdown-row">
                                    <span class="historial-breakdown-label">I.V.A (19%):</span>
                                    <span class="historial-breakdown-value">${{ formatNumber(calcularIVAHistorial(pedido)) }}</span>
                                </div>
                                <div class="historial-breakdown-separator"></div>
                                <div class="historial-breakdown-row historial-breakdown-total">
                                    <span class="historial-breakdown-label-total">💵 TOTAL:</span>
                                    <span class="historial-breakdown-value-total">${{ formatNumber(calcularTotalHistorial(pedido)) }}</span>
                                </div>
                            </div>
                            
                            <!-- Botón de pago si está pendiente -->
                            <div v-if="pedido.payment_status === 'pending' || !pedido.payment_status" class="historial-actions" style="margin-top: 1rem;">
                                <button @click="pagarPedidoExistente(pedido)" class="btn-pagar-historial" style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.75rem; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                                    <i class="fas fa-credit-card"></i> Pagar Ahora - ${{ formatNumber(calcularTotalHistorial(pedido)) }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>
                </div>

                <!-- Formulario Compacto -->
                <div v-if="!mostrarHistorial" class="section-card fade-in-up">
                    <div class="section-card-header">
                        <h3 class="m-0">📝 Datos del Pedido</h3>
                    </div>
                    <div class="section-card-body">
                        <!-- Información del Negocio -->
                        <div class="business-info">
                            <div class="info-item">
                                <div class="info-label">🏢 Local</div>
                                <p class="info-value">{{ this.app && this.app.Name ? this.app.Name : 'Cargando...' }}</p>
                            </div>
                            <div class="info-item">
                                <div class="info-label">📅 Fecha</div>
                                <p class="info-value">{{ this.date }}</p>
                            </div>
                        </div>

                        <!-- Formulario Moderno -->
                        <form class="modern-form">
                            <div class="form-group-modern">
                                <input v-model="name" type="text" placeholder="Tu nombre completo*" class="form-control-modern">
                                <i class="input-icon fas fa-user"></i>
                            </div>
                            <!-- Campo WhatsApp -->
                            <div class="form-group-modern">
                                <input v-model="whatsapp" type="text" placeholder="WhatsApp (+56912345678)*" class="form-control-modern" maxlength="12">
                                <i class="input-icon fab fa-whatsapp"></i>
                            </div>
                            <!-- Método de pago fijo: Efectivo (sin opción de cambiar) -->
                            <div class="form-group-modern" style="display: none;">
                                <input v-model="paymode" type="text" readonly class="form-control-modern">
                                <i class="input-icon fas fa-money-bill-wave"></i>
                            </div>
                            <!-- Mensaje de advertencia sobre observaciones -->
                            <div class="col-span-full">
                                <div style="margin-top: 10px; padding: 15px 20px; background: linear-gradient(135deg, #fff3cd 0%, #ffe5a0 100%); border-radius: 12px; border-left: 5px solid #ff9800; display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 8px rgba(255, 152, 0, 0.2);">
                                    <i class="fas fa-exclamation-triangle" style="color: #ff9800; font-size: 24px;"></i>
                                    <div>
                                        <div style="color: #856404; font-size: 16px; font-weight: 700; margin-bottom: 4px;">
                                            ⚠️ Comentarios u Observaciones
                                        </div>
                                        <div style="color: #856404; font-size: 14px; font-weight: 500;">
                                            Para agregar comentarios u observaciones sobre tu pedido, envíalos al correo electrónico
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Productos en Cards -->
                <div v-if="!mostrarHistorial" class="section-card fade-in-up">
                    <div class="section-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="m-0">📦 Selecciona tus Productos ({{ cantidadProductos }})</h3>
                        <button @click="cargarPreciosCentralizados" class="btn-refresh" style="background: transparent; border: 1px solid rgba(255,255,255,0.3); color: white; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer;">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="section-card-body">
                        <div v-if="loadingProducts" class="loading-modern">
                            <i class="fas fa-spinner fa-spin"></i> Cargando productos...
                        </div>

                        <!-- Grid de Productos por Categoría -->
                        <div v-else>
                            <div v-for="categoria in categorias" :key="categoria" class="categoria-group">
                                <h4 class="categoria-title">{{ getCategoriaEmoji(categoria) }} {{ categoria.toUpperCase() }}</h4>
                                <div class="productos-grid-responsive">
                                    <div v-for="producto in getProductosByCategoria(categoria)" :key="producto.id" 
                                        class="producto-card-modern"
                                        :class="{ 
                                            'selected': producto.cantidad > 0,
                                            'producto-sin-stock': producto.stock !== null && producto.stock <= 0
                                        }">
                                        <div v-if="producto.stock !== null && producto.stock <= 0" class="stock-badge-bloqueado">
                                            🚫 Sin Stock
                                        </div>
                                        <div v-else-if="producto.stock !== null && producto.stock > 0" class="stock-badge-disponible">
                                            � {{ producto.stock }} {{ producto.unidad_medida }}
                                        </div>
                                        <div class="card-header-modern">
                                            <h5 class="producto-nombre">{{ producto.producto }}</h5>
                                            <span class="producto-unidad">{{ producto.unidad_medida }}</span>
                                        </div>
                                        <div class="card-precio-modern">
                                            <span class="precio-label">Precio:</span>
                                            <span class="precio-valor">${{ formatNumber(producto.precio_por_unidad) }}</span>
                                        </div>
                                        <div class="card-controls-modern">
                                            <button @click="decrementar(producto)" class="btn-qty-modern" :disabled="producto.cantidad === 0 || (producto.stock !== null && producto.stock <= 0)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" 
                                                v-model.number="producto.cantidad" 
                                                min="0"
                                                class="input-qty-modern"
                                                :disabled="producto.stock !== null && producto.stock <= 0"
                                                @input="calcularTotal">
                                            <button @click="incrementar(producto)" class="btn-qty-modern" :disabled="producto.stock !== null && producto.stock <= 0">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <div v-if="producto.cantidad > 0" class="card-subtotal-modern">
                                            Subtotal: <strong>${{ formatNumber(producto.cantidad * producto.precio_por_unidad) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer con botón fijo -->
                <div class="section-card fade-in-up" v-if="!mostrarHistorial">
                    <div class="section-card-body" style="padding: 1.5rem;">
                        <div class="products-indicator">
                            <div class="products-status" :class="totalPedido > 0 ? 'has-products' : 'no-products'">
                                <i class="cart-icon fas fa-shopping-cart"></i>
                                <span v-if="cantidadProductos > 0">
                                    {{ cantidadProductos }} productos seleccionados
                                </span>
                                <span v-else>Sin productos seleccionados</span>
                                <span class="products-badge">{{ cantidadProductos }}</span>
                            </div>
                            <div class="action-buttons">
                                <button @click="abrirModalPago" class="btn-modern btn-success-modern" :disabled="waitResponse || totalPedido === 0">
                                    <i class="fas fa-credit-card"></i>
                                    {{ waitResponse ? 'Procesando...' : 'Pagar y Finalizar' }}
                                </button>
                            </div>
                        </div>
                        
                        <div v-if="cantidadProductos > 0" class="price-breakdown mt-3">
                            <div class="breakdown-card">
                                <div class="breakdown-header">
                                    <i class="fas fa-calculator"></i>
                                    <h4>RESUMEN DE COMPRA</h4>
                                </div>
                                <div class="breakdown-body">
                                    <div class="breakdown-row">
                                        <span class="breakdown-label">NETO:</span>
                                        <span class="breakdown-value">${{ formatNumber(totalNeto) }}</span>
                                    </div>
                                    <div class="breakdown-row">
                                        <span class="breakdown-label">EXENTO:</span>
                                        <span class="breakdown-value">${{ formatNumber(totalExento) }}</span>
                                    </div>
                                    <div class="breakdown-row">
                                        <span class="breakdown-label">I.V.A (19%):</span>
                                        <span class="breakdown-value">${{ formatNumber(totalIVA) }}</span>
                                    </div>
                                    <div class="breakdown-separator"></div>
                                    <div class="breakdown-row breakdown-total">
                                        <span class="breakdown-label-total">💵 TOTAL:</span>
                                        <span class="breakdown-value-total">${{ formatNumber(totalConIVA) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Pago con Linkyfi -->
        <div class="modal fade" id="modalPagoStripe" tabindex="-1" role="dialog" data-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
                    <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                        <h5 class="modal-title">
                            <i class="fas fa-credit-card"></i> Procesar Pago
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" :disabled="processingPayment">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 2rem;">
                        <div v-if="!processingPayment">
                            <!-- Resumen del pedido -->
                            <div class="payment-summary" style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 1.5rem;">
                                <h6 style="margin-bottom: 1rem; color: #495057; font-weight: 700;">
                                    <i class="fas fa-shopping-cart"></i> Detalle del Pedido
                                </h6>
                                
                                <!-- Lista de productos -->
                                <div style="max-height: 200px; overflow-y: auto; margin-bottom: 1rem; padding-right: 0.5rem;">
                                    <div v-if="pedidoAPagar">
                                        <!-- Productos del pedido existente -->
                                        <div v-for="(producto, index) in parseProducts(pedidoAPagar.products)" :key="index" 
                                             style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #dee2e6;">
                                            <div style="flex: 1;">
                                                <div style="font-weight: 600; color: #333;">{{ producto.name }}</div>
                                                <div style="font-size: 0.85rem; color: #6c757d;">{{ producto.quantity }}x ${{ formatNumber(producto.price) }}</div>
                                            </div>
                                            <div style="font-weight: 700; color: #667eea;">
                                                ${{ formatNumber(parseFloat(producto.price) * parseInt(producto.quantity)) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <!-- Productos del carrito nuevo -->
                                        <div v-for="producto in productosCentralizados.filter(p => p.cantidad > 0)" :key="producto.id" 
                                             style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #dee2e6;">
                                            <div style="flex: 1;">
                                                <div style="font-weight: 600; color: #333;">{{ producto.producto }}</div>
                                                <div style="font-size: 0.85rem; color: #6c757d;">{{ producto.cantidad }}x ${{ formatNumber(producto.precio_por_unidad) }}</div>
                                            </div>
                                            <div style="font-weight: 700; color: #667eea;">
                                                ${{ formatNumber(producto.cantidad * producto.precio_por_unidad) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Totales -->
                                <div style="border-top: 2px solid #dee2e6; padding-top: 1rem;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #6c757d;">
                                        <span>Subtotal ({{ modalCantidadProductos }} productos):</span>
                                        <span>${{ formatNumber(modalTotalNeto) }}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #6c757d;">
                                        <span>IVA (19%):</span>
                                        <span>${{ formatNumber(modalTotalIVA) }}</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.3rem; color: #667eea; margin-top: 0.75rem;">
                                        <span>TOTAL A PAGAR:</span>
                                        <span>${{ formatNumber(modalTotalConIVA) }} CLP</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Datos del cliente -->
                            <div class="mb-3">
                                <label style="font-weight: 600; color: #495057; margin-bottom: 0.5rem;">
                                    <i class="fas fa-user"></i> Nombre
                                </label>
                                <input v-model="name" type="text" class="form-control" placeholder="Tu nombre" readonly style="background: #f8f9fa;">
                            </div>

                            <div class="mb-3">
                                <label style="font-weight: 600; color: #495057; margin-bottom: 0.5rem;">
                                    <i class="fab fa-whatsapp"></i> WhatsApp (para confirmación)
                                </label>
                                <input v-model="whatsapp" type="text" class="form-control" placeholder="+56912345678" required>
                            </div>

                            <!-- Selección de método de pago -->
                            <div class="mb-4">
                                <label style="font-weight: 700; color: #495057; margin-bottom: 1rem; font-size: 1.1rem;">
                                    <i class="fas fa-credit-card"></i> Método de Pago
                                </label>
                                
                                <!-- Opción 1: Transferencia (Linkify) -->
                                <div @click="metodoPagoSeleccionado = 'linkify'" 
                                     :style="{
                                         border: metodoPagoSeleccionado === 'linkify' ? '3px solid #28a745' : '2px solid #dee2e6',
                                         background: metodoPagoSeleccionado === 'linkify' ? '#f1f8f4' : 'white',
                                         padding: '1.5rem',
                                         borderRadius: '10px',
                                         marginBottom: '1rem',
                                         cursor: 'pointer',
                                         transition: 'all 0.3s'
                                     }"
                                     class="payment-option">
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <div style="width: 40px; height: 40px; background: #28a745; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-university" style="color: white; font-size: 1.2rem;"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 700; color: #333; font-size: 1.1rem;">Transferencia Bancaria</div>
                                                <div style="font-size: 0.9rem; color: #6c757d;">A través de Linkify - Sin comisión adicional</div>
                                            </div>
                                        </div>
                                        <div>
                                            <div style="font-size: 1.3rem; font-weight: 700; color: #28a745;">
                                                ${{ formatNumber(modalTotalConIVA) }}
                                            </div>
                                            <div style="font-size: 0.85rem; color: #28a745; font-weight: 600;">
                                                <i class="fas fa-check-circle"></i> GRATIS
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Opción 2: Tarjeta (Flow) -->
                                <div @click="metodoPagoSeleccionado = 'flow'" 
                                     :style="{
                                         border: metodoPagoSeleccionado === 'flow' ? '3px solid #667eea' : '2px solid #dee2e6',
                                         background: metodoPagoSeleccionado === 'flow' ? '#f0f3ff' : 'white',
                                         padding: '1.5rem',
                                         borderRadius: '10px',
                                         cursor: 'pointer',
                                         transition: 'all 0.3s'
                                     }"
                                     class="payment-option">
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <div style="width: 40px; height: 40px; background: #667eea; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-credit-card" style="color: white; font-size: 1.2rem;"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 700; color: #333; font-size: 1.1rem;">Tarjeta Crédito/Débito</div>
                                                <div style="font-size: 0.9rem; color: #6c757d;">Pago inmediato con Flow.cl</div>
                                            </div>
                                        </div>
                                        <div>
                                            <div style="font-size: 1.3rem; font-weight: 700; color: #667eea;">
                                                ${{ formatNumber(totalConComisionFlow) }}
                                            </div>
                                            <div style="font-size: 0.85rem; color: #ff9800; font-weight: 600;">
                                                + {{ comisionFlow }}% comisión
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="!metodoPagoSeleccionado" style="background: #fff3cd; padding: 1rem; border-radius: 8px; border-left: 4px solid #ffc107; margin-bottom: 1rem;">
                                <small style="color: #856404;">
                                    <i class="fas fa-exclamation-triangle"></i> Por favor selecciona un método de pago
                                </small>
                            </div>

                            <div v-if="metodoPagoSeleccionado === 'linkify'" style="background: #e3f2fd; padding: 1rem; border-radius: 8px; border-left: 4px solid #2196f3; margin-bottom: 1rem;">
                                <small style="color: #1976d2;">
                                    <i class="fas fa-lock"></i> Serás redirigido a Linkify para completar tu transferencia bancaria de forma segura.
                                </small>
                            </div>

                            <div v-if="metodoPagoSeleccionado === 'linkify'" style="text-align: center;">
                                <button @click="abrirAyudaTransferencia" type="button" class="btn btn-info" style="border-radius: 8px; padding: 0.5rem 1.5rem; font-weight: 600;">
                                    <i class="fas fa-question-circle"></i> ¿Cómo hacer una transferencia?
                                </button>
                            </div>

                            <div v-if="metodoPagoSeleccionado === 'flow'" style="background: #e8eaf6; padding: 1rem; border-radius: 8px; border-left: 4px solid #667eea;">
                                <small style="color: #5e35b1;">
                                    <i class="fas fa-shield-alt"></i> Serás redirigido a Flow.cl para pagar con tu tarjeta de forma segura.
                                </small>
                            </div>
                        </div>

                        <!-- Procesando pago -->
                        <div v-else style="text-align: center; padding: 2rem;">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                <span class="sr-only">Procesando...</span>
                            </div>
                            <h5 style="margin-top: 1rem; color: #495057;">Preparando pago...</h5>
                            <p style="color: #6c757d;">Serás redirigido a la página de pago.</p>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #dee2e6; padding: 1rem 2rem;" v-if="!processingPayment">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button @click="procesarPago" 
                                :disabled="!metodoPagoSeleccionado"
                                class="btn btn-primary" 
                                :style="{
                                    background: metodoPagoSeleccionado === 'flow' ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : 'linear-gradient(135deg, #28a745 0%, #20c997 100%)',
                                    border: 'none',
                                    borderRadius: '8px',
                                    padding: '0.5rem 2rem',
                                    fontWeight: '600',
                                    fontSize: '1.05rem',
                                    opacity: !metodoPagoSeleccionado ? 0.5 : 1,
                                    cursor: !metodoPagoSeleccionado ? 'not-allowed' : 'pointer'
                                }">
                            <i class="fas fa-dollar-sign"></i> 
                            Pagar ${{ formatNumber(metodoPagoSeleccionado === 'flow' ? totalConComisionFlow : modalTotalConIVA) }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Ayuda para Transferencias -->
        <div class="modal fade" id="modalAyudaTransferencia" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content" style="border-radius: 15px;">
                    <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none;">
                        <h5 class="modal-title">
                            <i class="fas fa-graduation-cap"></i> ¿Cómo hacer una transferencia bancaria?
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 0;">
                        <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;">
                            <iframe 
                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"
                                src="https://www.youtube.com/embed/KHUNZqKNP5w?si=KuZsuzU6scXoU2_I" 
                                title="YouTube video player" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                referrerpolicy="strict-origin-when-cross-origin" 
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #dee2e6;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 8px;">
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Administración de Productos -->
        <div class="modal fade" id="modalAdminProductos" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content" style="border-radius: 15px;">
                    <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h5 class="modal-title">
                            <i class="fas fa-cog"></i> Administración de Productos
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 2rem;">
                        <!-- Botón para agregar nuevo producto -->
                        <div class="mb-4">
                            <button @click="nuevoProducto" class="btn btn-success btn-lg">
                                <i class="fas fa-plus-circle"></i> Agregar Nuevo Producto
                            </button>
                        </div>

                        <!-- Tabla de productos -->
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Producto</th>
                                        <th>Unidad Venta</th>
                                        <th>Unidad Medida</th>
                                        <th>Precio</th>
                                        <th>Categoría</th>
                                        <th>Activo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="producto in productosCentralizados" :key="producto.id">
                                        <td>{{ producto.id }}</td>
                                        <td>{{ producto.producto }}</td>
                                        <td>{{ producto.unidad_venta }}</td>
                                        <td>{{ producto.unidad_medida }}</td>
                                        <td>${{ formatNumber(producto.precio_por_unidad) }}</td>
                                        <td>
                                            <span class="badge badge-primary">{{ producto.categoria }}</span>
                                        </td>
                                        <td>
                                            <span :class="producto.activo ? 'badge badge-success' : 'badge badge-secondary'">
                                                {{ producto.activo ? 'Sí' : 'No' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button @click="editarProducto(producto)" class="btn btn-sm btn-warning mr-1">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button @click="confirmarEliminar(producto)" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Editar/Crear Producto -->
        <div class="modal fade" id="modalEditarProducto" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" style="border-radius: 15px;">
                    <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                        <h5 class="modal-title">
                            <i class="fas fa-box"></i> {{ productoEditando.id ? 'Editar' : 'Nuevo' }} Producto
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="padding: 2rem;">
                        <form @submit.prevent="guardarProducto">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-tag"></i> Nombre del Producto *</label>
                                        <input v-model="productoEditando.producto" type="text" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fas fa-weight"></i> Unidad Venta *</label>
                                        <input v-model.number="productoEditando.unidad_venta" type="number" step="0.01" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fas fa-ruler"></i> Unidad Medida *</label>
                                        <select v-model="productoEditando.unidad_medida" class="form-control" required>
                                            <option value="kg">kg</option>
                                            <option value="unidad">unidad</option>
                                            <option value="litro">litro</option>
                                            <option value="gramo">gramo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-dollar-sign"></i> Precio por Unidad *</label>
                                        <input v-model.number="productoEditando.precio_por_unidad" type="number" step="1" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fas fa-list"></i> Categoría *</label>
                                        <select v-model="productoEditando.categoria" class="form-control" required>
                                            <option value="insumos">Insumos</option>
                                            <option value="salsas">Salsas</option>
                                            <option value="ciabatta">Ciabatta</option>
                                            <option value="pastas">Pastas</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="switchActivo" v-model="productoEditando.activo">
                                            <label class="custom-control-label" for="switchActivo">
                                                <i class="fas fa-toggle-on"></i> Producto Activo
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" @click="guardarProducto" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Presupuesto -->
        <div v-if="mostrarModalPresupuesto" class="modal-overlay-presupuesto" @click.self="mostrarModalPresupuesto = false">
            <div class="modal-dialog-presupuesto">
                <div class="modal-content-presupuesto">
                    <div class="modal-header-presupuesto">
                        <h5 class="modal-title-presupuesto">
                            <i class="fas fa-calculator me-2"></i>
                            Cómo Funciona el Presupuesto de Pedidos
                        </h5>
                        <button @click="cerrarModalPresupuesto" class="btn-close-presupuesto">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body-presupuesto">
                        <div class="presupuesto-info">
                            <div class="info-header">
                                <i class="fas fa-chart-line"></i>
                                <h6>Cálculo Automático del Presupuesto</h6>
                            </div>
                            
                            <div class="info-section">
                                <h6 class="section-title">
                                    <i class="fas fa-lightbulb"></i>
                                    Ejemplo Práctico
                                </h6>
                                <div class="ejemplo-card">
                                    <div class="ejemplo-content">
                                        <div class="ejemplo-step">
                                            <span class="step-check">✅</span>
                                            <div class="step-content">
                                                <strong>Tu local proyecta vender:</strong>
                                                <p class="step-value">$5.000.000 esta semana</p>
                                            </div>
                                        </div>
                                        <div class="ejemplo-step">
                                            <span class="step-check">✅</span>
                                            <div class="step-content">
                                                <strong>Puedes pedir hasta el 30%:</strong>
                                                <p class="step-value">$1.500.000 de presupuesto total</p>
                                            </div>
                                        </div>
                                        <div class="ejemplo-step">
                                            <span class="step-check">✅</span>
                                            <div class="step-content">
                                                <strong>Dividido en 3 despachos semanales:</strong>
                                                <p class="step-value">$500.000 por cada despacho</p>
                                            </div>
                                        </div>
                                        <div class="ejemplo-step">
                                            <span class="step-check">✅</span>
                                            <div class="step-content">
                                                <strong>Beneficio:</strong>
                                                <p class="step-value">Control óptimo del inventario sin excesos</p>
                                            </div>
                                        </div>
                                        <div class="ejemplo-step">
                                            <span class="step-check">✅</span>
                                            <div class="step-content">
                                                <strong>Indicador visual:</strong>
                                                <p class="step-value">Barra de progreso te muestra cuánto llevas gastado</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="info-section">
                                <h6 class="section-title">
                                    <i class="fas fa-info-circle"></i>
                                    Consejos Importantes
                                </h6>
                                <div class="ejemplo-card" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #f59e0b;">
                                    <div class="ejemplo-content">
                                        <div class="ejemplo-step">
                                            <span class="step-arrow">➡️</span>
                                            <div class="step-content">
                                                <p class="step-value">Planifica pensando en la proyección semanal</p>
                                            </div>
                                        </div>
                                        <div class="ejemplo-step">
                                            <span class="step-arrow">➡️</span>
                                            <div class="step-content">
                                                <p class="step-value">Distribuye el presupuesto equitativamente</p>
                                            </div>
                                        </div>
                                        <div class="ejemplo-step">
                                            <span class="step-arrow">➡️</span>
                                            <div class="step-content">
                                                <p class="step-value">Evita concentrar todo en un solo despacho</p>
                                            </div>
                                        </div>
                                        <div class="ejemplo-step">
                                            <span class="step-arrow">➡️</span>
                                            <div class="step-content">
                                                <p class="step-value">Monitorea tu progreso en tiempo real</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="info-section">
                                <div class="alert-tip-custom">
                                    <div class="tip-icon">💡</div>
                                    <div class="tip-content">
                                        <strong>Tip:</strong> El sistema calcula automáticamente tu presupuesto basándose en las metas semanales. Úsalo como guía para optimizar tus pedidos y mantener la rentabilidad del negocio.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4" style="color: #6b7280; font-size: 0.9rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                            <code>&lt;/&gt;</code> Saludos Jimmy Arriagada <code>&lt;/&gt;</code>
                        </div>
                    </div>
                    <div class="modal-footer-presupuesto">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Puedes volver a ver esta guía haciendo clic en el botón "Presupuesto" en la parte superior
                        </p>
                        <button @click="cerrarModalPresupuesto" class="btn-entendido">
                            <i class="fas fa-check me-2"></i>
                            Entendido
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Loader from '@/helpers/Loader';
import FormatNumber from '@/helpers/FormatNumber.js';
import BaseUrl from '@/helpers/baseUrl';
import Connection from '@/helpers/Connection';
import moment from 'moment';

export default {
    name: 'PedidoFinal',
    components: {
        
    },
    data() {
        return {
            // Control de pantallas
            inicioSesion: false,
            mostrarHistorial: false,
            mostrarModalPresupuesto: false,
            loadingHistorial: false,
            historialPedidos: [],
            
            // Productos centralizados desde DB maestra
            productosCentralizados: [],
            loadingProducts: false,
            stockRefreshInterval: null, // Timer para actualizar stock
            historialRefreshInterval: null, // Timer para actualizar historial
            isLoadingHistorial: false, // Flag para prevenir múltiples cargas simultáneas
            
            // Administración de productos
            productoEditando: {
                id: null,
                producto: '',
                unidad_venta: 1,
                unidad_medida: 'kg',
                precio_por_unidad: 0,
                categoria: 'insumos',
                activo: 1
            },
            
            // Formulario
            name: '',
            whatsapp: '',
            phone: '', // No se usa pero se mantiene para compatibilidad
            comment: '', // No se usa pero se mantiene para compatibilidad
            paymode: 'Efectivo', // Por defecto Efectivo
            
            // Datos de negocio
            app: null,
            date: moment().format('YYYY-MM-DD HH:mm:ss'),
            metaSemanal: null, // Datos de meta semanal y presupuesto
            
            // Control
            submitted: false,
            waitResponse: false,
            
            // Pago Stripe
            showPaymentModal: false,
            processingPayment: false,
            url_linkify: 'https://app.linkify.cl/pay/0GEVx3vk2gmqe9r/remote/',
            url_payment: '',
            pedidoAPagar: null, // Pedido existente que se va a pagar desde historial
            
            // Método de pago seleccionado
            metodoPagoSeleccionado: null, // 'linkify' o 'flow'
            comisionFlow: 3.18, // Comisión Flow en porcentaje
            
            // Totales
            totalPedido: 0,
            
            // Opciones
            paymodes: [
                { id: 2, name: 'Efectivo' },
                { id: 3, name: 'Tarjeta' }
            ]
        }
    },
    async beforeCreate() {
        var request = await this.$store.dispatch('main/refreshData', '?slim');
        this.app = request.data;
    },
    async mounted() {
        this.name = this.me.fullname;
        // Cargar productos al iniciar
        await this.cargarPreciosCentralizados();
        // Cargar meta semanal
        await this.cargarMetaSemanal();
        
        // Auto-mostrar modal de bienvenida/presupuesto si no se ha mostrado antes
        const modalYaMostrado = localStorage.getItem('presupuestoModalShown');
        if (!modalYaMostrado) {
            // Esperar un momento para que la UI esté lista
            setTimeout(() => {
                this.mostrarModalPresupuesto = true;
            }, 500);
        }
        
        // Si ya inició sesión, cargar historial
        if (this.inicioSesion && this.app && this.app.Id) {
            this.cargarHistorial();
        }
        // Iniciar actualización automática de stock cada 10 segundos
        this.iniciarActualizacionStock();
        // Iniciar actualización automática de historial cada 15 segundos
        this.iniciarActualizacionHistorial();
    },
    beforeDestroy() {
        // Limpiar el intervalo cuando se destruye el componente
        if (this.stockRefreshInterval) {
            clearInterval(this.stockRefreshInterval);
        }
        // Limpiar el intervalo de historial
        if (this.historialRefreshInterval) {
            clearInterval(this.historialRefreshInterval);
        }
    },
    watch: {
        // Watcher para detectar cambios en historialPedidos
        historialPedidos: {
            handler(newVal, oldVal) {
                console.log('🔔 [WATCHER] historialPedidos cambió:');
                console.log('   Anterior:', oldVal ? oldVal.length : 0, 'pedidos');
                console.log('   Nuevo:', newVal ? newVal.length : 0, 'pedidos');
            },
            deep: true
        },
        
        // Watcher para detectar cuándo se muestra/oculta el historial
        mostrarHistorial(newVal) {
            console.log('🔔 [WATCHER] mostrarHistorial cambió a:', newVal);
            if (newVal && this.historialPedidos.length === 0) {
                console.log('⚠️ [WATCHER] Historial vacío al mostrarlo, recargando...');
                this.cargarHistorial();
            }
        },
        
        // Watcher para detectar cambios en loadingHistorial
        loadingHistorial(newVal) {
            console.log('🔔 [WATCHER] loadingHistorial cambió a:', newVal);
        },
        
        // Watcher para detectar cambios en loadingProducts
        loadingProducts(newVal) {
            console.log('🔔 [WATCHER] loadingProducts cambió a:', newVal);
        },
        
        // Watcher para detectar cambios en inicioSesion
        inicioSesion(newVal) {
            console.log('🔔 [WATCHER] inicioSesion cambió a:', newVal);
        }
    },
    computed: {
        me: { get() { return this.$store.getters['main/user']; } },
        
        // Categorías únicas de productos ordenadas
        categorias() {
            const cats = [...new Set(this.productosCentralizados.map(p => p.categoria))];
            // Orden específico: insumos, salsas, ciabatta, pastas
            const orden = { 'insumos': 1, 'salsas': 2, 'ciabatta': 3, 'pastas': 4 };
            return cats.sort((a, b) => {
                const ordenA = orden[a.toLowerCase()] || 999;
                const ordenB = orden[b.toLowerCase()] || 999;
                return ordenA - ordenB;
            });
        },
        
        // Cantidad de productos seleccionados
        cantidadProductos() {
            return this.productosCentralizados.filter(p => p.cantidad > 0).length;
        },
        
        // Cálculos de totales
        totalNeto() {
            return this.totalPedido; // El total sin IVA
        },
        
        totalExento() {
            return 0; // Por ahora, todos los productos llevan IVA
        },
        
        totalIVA() {
            return Math.round(this.totalNeto * 0.19); // 19% de IVA
        },
        
        totalConIVA() {
            return this.totalNeto + this.totalIVA; // Total final con IVA
        },
        
        // Computed properties para el modal de pago (dinámicas según si es pedido existente o nuevo)
        modalCantidadProductos() {
            if (this.pedidoAPagar) {
                return this.parseProducts(this.pedidoAPagar.products).length;
            }
            return this.cantidadProductos;
        },
        
        modalTotalNeto() {
            if (this.pedidoAPagar) {
                return this.calcularNetoHistorial(this.pedidoAPagar);
            }
            return this.totalNeto;
        },
        
        modalTotalIVA() {
            if (this.pedidoAPagar) {
                return this.calcularIVAHistorial(this.pedidoAPagar);
            }
            return this.totalIVA;
        },
        
        modalTotalConIVA() {
            if (this.pedidoAPagar) {
                return this.calcularTotalHistorial(this.pedidoAPagar);
            }
            return this.totalConIVA;
        },
        
        // Total con comisión de Flow (3.18%)
        totalConComisionFlow() {
            const total = this.modalTotalConIVA;
            const comision = total * (this.comisionFlow / 100);
            return Math.round(total + comision);
        },
        
        isValidName: {
            get() { return this.name.length > 0 }
        },
        isValidPhone: {
            get() { 
                const regex = /^\+56\d{9}$/;
                return regex.test(this.phone);
            }
        },
        isValidComment: {
            get() { return this.comment.length > 0 }
        },
        isValidPaymode: {
            get() { return this.paymode === 'Efectivo' } // Siempre será válido porque es fijo
        },
        
        // Cachear productos parseados del historial para evitar re-renderizados infinitos
        historialConProductosParsed() {
            try {
                console.log('🎯 [COMPUTED] historialConProductosParsed ejecutándose...');
                console.log('   mostrarHistorial:', this.mostrarHistorial);
                console.log('   loadingHistorial:', this.loadingHistorial);
                console.log('   historialPedidos.length:', this.historialPedidos ? this.historialPedidos.length : 0);
                
                if (!this.historialPedidos || this.historialPedidos.length === 0) {
                    console.log('📊 Computed: No hay pedidos en historial');
                    return [];
                }
                
                const result = this.historialPedidos.map(pedido => {
                    try {
                        return {
                            ...pedido,
                            productosParsed: this.parseProductsSafe(pedido.products)
                        };
                    } catch (e) {
                        console.error('❌ Error parseando pedido', pedido.id, e);
                        return {
                            ...pedido,
                            productosParsed: []
                        };
                    }
                });
                
                console.log('✅ Computed ejecutado:', result.length, 'pedidos con productos parseados');
                return result;
            } catch (error) {
                console.error('❌ Error crítico en historialConProductosParsed:', error);
                return [];
            }
        }
    },
    methods: {
        formatNumber(value) {
            return FormatNumber.format(value);
        },
        
        formatCLP(value) {
            if (!value || value === 0) return '$0';
            const formatted = new Intl.NumberFormat('es-CL', {
                style: 'currency',
                currency: 'CLP',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value);
            return formatted;
        },
        
        formatDate(date) {
            return moment(date).format('DD/MM/YYYY HH:mm');
        },
        
        calcularPorcentajeDespacho() {
            if (!this.metaSemanal || !this.metaSemanal.presupuesto_por_despacho) return 0;
            const porcentaje = (this.totalConIVA / this.metaSemanal.presupuesto_por_despacho) * 100;
            return Math.min(Math.round(porcentaje), 150); // Límite visual en 150%
        },
        
        parseProducts(productsJson) {
            try {
                const productos = JSON.parse(productsJson);
                console.log('🛒 Productos parseados:', productos);
                return productos;
            } catch (e) {
                console.error('❌ Error parseando productos:', e);
                return [];
            }
        },
        
        // Versión segura sin logging excesivo (usada en computed)
        parseProductsSafe(productsJson) {
            try {
                if (!productsJson) {
                    return [];
                }
                // Si ya es un array, retornarlo directamente
                if (Array.isArray(productsJson)) {
                    return productsJson;
                }
                // Si es string, parsearlo
                if (typeof productsJson === 'string') {
                    return JSON.parse(productsJson);
                }
                return [];
            } catch (e) {
                console.warn('⚠️ Error parseando productos (safe):', e.message);
                return [];
            }
        },
        
        iniciarPedido() {
            this.inicioSesion = true;
            // Cargar meta semanal
            this.cargarMetaSemanal();
            // Cargar historial solo si app está disponible
            if (this.app && this.app.Id) {
                this.cargarHistorial();
            }
        },
        
        cerrarModalPresupuesto() {
            // Marcar como mostrado en localStorage
            localStorage.setItem('presupuestoModalShown', 'true');
            this.mostrarModalPresupuesto = false;
        },
        
        incrementar(producto) {
            producto.cantidad++;
            this.calcularTotal();
        },
        
        decrementar(producto) {
            if (producto.cantidad > 0) {
                producto.cantidad--;
                this.calcularTotal();
            }
        },
        
        getProductosByCategoria(categoria) {
            return this.productosCentralizados
                .filter(p => p.categoria === categoria)
                .sort((a, b) => a.producto.localeCompare(b.producto));
        },
        
        getCategoriaEmoji(categoria) {
            const emojis = {
                'insumos': '📦',
                'salsas': '🍝',
                'ciabatta': '🥖',
                'pastas': '🍝',
                'general': '🛒'
            };
            return emojis[categoria.toLowerCase()] || '📌';
        },
        
        iniciarActualizacionStock() {
            console.log('⏸️ [STOCK] Actualización automática de stock DESHABILITADA temporalmente para debugging');
            return; // TEMPORAL: Deshabilitar para debugging
            
            // Actualizar stock cada 10 segundos
            this.stockRefreshInterval = setInterval(async () => {
                if (!this.mostrarHistorial && this.inicioSesion) {
                    console.log('📊 [STOCK] Actualizando stock en background...');
                    await this.actualizarStockSilencioso();
                } else {
                    console.log('⏭️ [STOCK] Saltando actualización (mostrarHistorial:', this.mostrarHistorial, ', inicioSesion:', this.inicioSesion, ')');
                }
            }, 10000); // 10 segundos
            
            console.log('🔄 Actualización automática de stock iniciada (cada 10s)');
        },
        
        iniciarActualizacionHistorial() {
            // Actualizar historial cada 15 segundos solo si está visible y hay pedidos pendientes
            this.historialRefreshInterval = setInterval(async () => {
                if (this.mostrarHistorial && this.app && this.app.Id) {
                    // Verificar si hay pedidos pendientes de pago
                    const hayPendientes = this.historialPedidos.some(p => 
                        p.payment_status !== 'paid' && p.payment_status !== 'pagado'
                    );
                    
                    if (hayPendientes) {
                        console.log('🔄 Actualizando historial (hay pagos pendientes)...');
                        await this.cargarHistorial();
                    }
                }
            }, 15000); // 15 segundos
            
            console.log('🔄 Actualización automática de historial iniciada (cada 15s)');
        },
        
        async actualizarStockSilencioso() {
            try {
                // Guardar las cantidades actuales del usuario
                const cantidadesActuales = {};
                this.productosCentralizados.forEach(p => {
                    if (p.cantidad > 0) {
                        cantidadesActuales[p.id] = p.cantidad;
                    }
                });
                
                // Obtener stock actualizado
                const response = await this.$store.dispatch('products/getPreciosCentralizados');
                
                let productos = null;
                if (response.success && response.data) {
                    if (response.data.data && Array.isArray(response.data.data)) {
                        productos = response.data.data;
                    } else if (Array.isArray(response.data)) {
                        productos = response.data;
                    } else if (response.data.success && Array.isArray(response.data.data)) {
                        productos = response.data.data;
                    }
                }
                
                if (productos && Array.isArray(productos)) {
                    // Detectar productos que se quedaron sin stock
                    const productosAgotados = [];
                    
                    this.productosCentralizados = productos.map(p => {
                        const producto = {
                            ...p,
                            precio_por_unidad: parseFloat(p.precio_por_unidad),
                            cantidad: cantidadesActuales[p.id] || 0 // Mantener cantidad del carrito
                        };
                        
                        // Verificar si el producto se quedó sin stock y el usuario tenía cantidad
                        if (producto.stock !== null && producto.stock <= 0 && cantidadesActuales[p.id] > 0) {
                            productosAgotados.push(producto.producto);
                            producto.cantidad = 0; // Quitar del carrito
                        }
                        
                        return producto;
                    });
                    
                    // Alertar si algún producto se agotó
                    if (productosAgotados.length > 0) {
                        this.$awn.alert(`⚠️ Se agotó: ${productosAgotados.join(', ')}. Fue removido de tu pedido.`);
                        this.calcularTotal();
                    }
                    
                    console.log('🔄 Stock actualizado en tiempo real');
                }
            } catch (error) {
                console.error('❌ Error actualizando stock:', error);
            }
        },
        
        async cargarPreciosCentralizados() {
            try {
                this.loadingProducts = true;
                console.log('📡 Cargando precios centralizados desde DB maestra via Server app...');
                
                // Llamar al Server app que consulta easyerp.pedidofinal_precios
                const response = await this.$store.dispatch('products/getPreciosCentralizados');
                
                console.log('🔍 Respuesta completa:', JSON.stringify(response, null, 2));
                console.log('🔍 response.data:', response.data);
                console.log('🔍 Tipo de response.data:', typeof response.data);
                console.log('🔍 Es array?', Array.isArray(response.data));
                
                // El servidor Laravel devuelve { success: true, data: [...] }
                // Pero Connection.js lo envuelve nuevamente en { success: true, data: {...} }
                // Entonces necesitamos acceder a response.data.data
                let productos = null;
                
                if (response.success && response.data) {
                    // Si response.data.data existe (doble encapsulación)
                    if (response.data.data && Array.isArray(response.data.data)) {
                        productos = response.data.data;
                    }
                    // Si response.data es directamente el array
                    else if (Array.isArray(response.data)) {
                        productos = response.data;
                    }
                    // Si response.data tiene la estructura de Laravel directamente
                    else if (response.data.success && Array.isArray(response.data.data)) {
                        productos = response.data.data;
                    }
                }
                
                if (productos && Array.isArray(productos)) {
                    // Agregar campo 'cantidad' a cada producto
                    this.productosCentralizados = productos.map(p => {
                        console.log(`💰 Producto: ${p.producto} - Precio DB: ${p.precio_por_unidad} (tipo: ${typeof p.precio_por_unidad})`);
                        return {
                            ...p,
                            precio_por_unidad: parseFloat(p.precio_por_unidad),
                            cantidad: 0
                        };
                    });
                    
                    console.log('✅ Precios centralizados cargados:', this.productosCentralizados.length, 'productos');
                    console.log('📦 Productos:', this.productosCentralizados);
                } else {
                    console.error('❌ No se pudo encontrar el array de productos en la respuesta');
                    throw new Error('La respuesta del servidor no contiene un array de productos');
                }
            } catch (error) {
                console.error('❌ Error cargando precios centralizados:', error.message);
                console.error('❌ Error completo:', error);
                this.$awn.alert('Error al cargar precios desde servidor');
            } finally {
                this.loadingProducts = false;
            }
        },
        
        calcularTotal() {
            this.totalPedido = this.productosCentralizados.reduce((total, producto) => {
                return total + (producto.cantidad * producto.precio_por_unidad);
            }, 0);
        },
        
        async cargarMetaSemanal() {
            try {
                const response = await this.$store.dispatch('metas/fetchCurrentMeta');
                
                if (response && response.success && response.data) {
                    // Si el backend ya devuelve meta_semanal, usarlo
                    if (response.data.meta_semanal) {
                        this.metaSemanal = response.data;
                    } else {
                        // FALLBACK: Calcular localmente mientras se despliega el backend
                        console.log('⚠️ [META] Backend no tiene meta_semanal, calculando localmente...');
                        
                        // Obtener todas las metas del mes actual
                        const mes = new Date().getMonth() + 1;
                        const anio = new Date().getFullYear();
                        const hoy = new Date();
                        
                        // Calcular lunes de la semana actual
                        const diaSemana = hoy.getDay(); // 0=domingo, 1=lunes, etc
                        const diasHastaLunes = diaSemana === 0 ? 6 : diaSemana - 1;
                        const lunes = new Date(hoy);
                        lunes.setDate(hoy.getDate() - diasHastaLunes);
                        
                        // Obtener metas de toda la semana (lunes a domingo)
                        let metaSemanal = 0;
                        const diasSemana = [];
                        
                        for (let i = 0; i < 7; i++) {
                            const fecha = new Date(lunes);
                            fecha.setDate(lunes.getDate() + i);
                            const diaNum = fecha.getDate();
                            const mesNum = fecha.getMonth() + 1; // Mes correcto de la fecha
                            const anioNum = fecha.getFullYear(); // Año correcto de la fecha
                            
                            // Buscar meta de este día en la DB
                            const metaDiaResponse = await Connection.request('GET', 
                                BaseUrl.getUrl(`api/web/metas-locales?mes=${mesNum}&anio=${anioNum}&dia=${diaNum}`));
                            
                            let montoDia = 0;
                            if (metaDiaResponse && metaDiaResponse.success && metaDiaResponse.data) {
                                // API devuelve estructura anidada: {success, data: {success, data: [...]}}
                                let dataArray = metaDiaResponse.data;
                                if (metaDiaResponse.data.data && Array.isArray(metaDiaResponse.data.data)) {
                                    dataArray = metaDiaResponse.data.data;
                                }
                                
                                // Filtrar por app_id
                                const appId = this.$store.state.main.Aplication ? this.$store.state.main.Aplication.Id : 58;
                                
                                if (Array.isArray(dataArray) && dataArray.length > 0) {
                                    const metaDia = dataArray.find(m => m.aplication_id === appId);
                                    if (metaDia) {
                                        montoDia = parseFloat(metaDia.meta_diaria) || 0;
                                    }
                                }
                            }
                            
                            metaSemanal += montoDia;
                            diasSemana.push({
                                fecha: fecha.toISOString().split('T')[0],
                                dia: diaNum,
                                mes: mesNum,
                                anio: anioNum,
                                meta_diaria: montoDia
                            });
                        }
                        
                        // Calcular presupuestos
                        const presupuestoPedidos = metaSemanal * 0.30;
                        const presupuestoPorDespacho = presupuestoPedidos / 3;
                        
                        this.metaSemanal = {
                            success: true,
                            meta: response.data.meta,
                            meta_semanal: metaSemanal,
                            dias_semana: diasSemana,
                            presupuesto_pedidos: presupuestoPedidos,
                            presupuesto_por_despacho: presupuestoPorDespacho
                        };
                        
                        console.log('✅ Meta semanal cargada:', `$${this.formatNumber(metaSemanal)}`);
                    }
                } else {
                    console.warn('⚠️ [META] No se pudo cargar la meta semanal');
                    this.metaSemanal = null;
                }
            } catch (error) {
                console.error('❌ [META] Error cargando meta semanal:', error);
                this.metaSemanal = null;
            }
        },
        
        validar_form() {
            if (!this.isValidName) {
                this.$awn.alert("Ingresa tu nombre completo");
                return false;
            }
            // Teléfono y comentario no son obligatorios desde el 8/01/2026
            if (!this.isValidPaymode) {
                this.$awn.alert("Selecciona un método de pago");
                return false;
            }
            if (this.totalPedido === 0) {
                this.$awn.alert("Debes seleccionar al menos un producto");
                return false;
            }
            return true;
        },
        
        // ========================================
        // MÉTODOS DE PAGO CON LINKYFI
        // ========================================
        
        abrirModalPago() {
            if (!this.validar_form()) {
                return;
            }
            
            this.pedidoAPagar = null; // Limpiar pedido existente
            
            // Abrir modal
            $('#modalPagoStripe').modal('show');
        },
        
        pagarPedidoExistente(pedido) {
            // Guardar el pedido que se va a pagar
            this.pedidoAPagar = pedido;
            
            // Prellenar datos
            this.name = pedido.contact_name;
            this.whatsapp = pedido.whatsapp || pedido.contact_phone || '';
            
            // Abrir modal
            $('#modalPagoStripe').modal('show');
        },
        
        async procesarPago() {
            // Validar que haya seleccionado un método de pago
            if (!this.metodoPagoSeleccionado) {
                this.$awn.alert('Por favor selecciona un método de pago');
                return;
            }
            
            if (!this.whatsapp) {
                this.$awn.alert('Por favor ingresa tu número de WhatsApp para recibir la confirmación');
                return;
            }
            
            this.processingPayment = true;
            
            try {
                if (this.metodoPagoSeleccionado === 'linkify') {
                    // Proceso con Linkify (transferencia)
                    await this.procesarPagoLinkify();
                } else if (this.metodoPagoSeleccionado === 'flow') {
                    // Proceso con Flow (tarjeta)
                    await this.procesarPagoFlow();
                }
            } catch (error) {
                console.error('❌ Error procesando pago:', error);
                this.$awn.alert('Error al procesar el pago: ' + error.message);
            } finally {
                this.processingPayment = false;
                this.pedidoAPagar = null; // Limpiar pedido temporal
                this.metodoPagoSeleccionado = null; // Resetear método de pago
            }
        },
        
        async procesarPagoLinkify() {
            // Verificar si es pedido existente o nuevo
            if (this.pedidoAPagar) {
                // Es un pedido existente, generar URL de pago
                const requestId = this.pedidoAPagar.payment ? this.pedidoAPagar.payment.id : this.pedidoAPagar.id;
                this.url_payment = this.url_linkify + this.app.Id + 'i' + requestId;
                
                console.log('💳 URL de pago Linkify generada:', this.url_payment);
                
                // NO abrir Linkify, solo enviar WhatsApp automáticamente
                await this.enviarNotificacionWhatsApp(this.pedidoAPagar, this.url_payment);
                
                // Cerrar modal
                $('#modalPagoStripe').modal('hide');
                this.$awn.success('✅ WhatsApp enviado correctamente con el link de pago.', {
                    labels: { success: 'MENSAJE ENVIADO' }
                });
            } else {
                // Es un pedido nuevo, crearlo primero con status_payment='impagado'
                const response = await this.crearPedidoSinPago();
                
                console.log('📦 Response completo:', response);
                
                // El backend retorna {success, message, data}, el pedido está en response.data
                const pedidoCreado = response && response.data ? response.data : null;
                
                console.log('📦 Pedido extraído:', pedidoCreado);
                console.log('📦 Payment del pedido:', pedidoCreado ? pedidoCreado.payment : 'NO PAYMENT');
                
                // Obtener el ID del payment
                const paymentId = pedidoCreado && pedidoCreado.payment && pedidoCreado.payment.id;
                console.log('📦 paymentId extraído:', paymentId);
                
                if (pedidoCreado && paymentId) {
                    // Generar URL de pago usando el ID del PAYMENT (no del request)
                    this.url_payment = this.url_linkify + this.app.Id + 'i' + paymentId;
                    
                    console.log('💳 URL de pago Linkify generada:', this.url_payment);
                    
                    // NO abrir Linkify, solo enviar WhatsApp automáticamente
                    await this.enviarNotificacionWhatsApp(pedidoCreado, this.url_payment);
                    
                    // Cerrar modal
                    $('#modalPagoStripe').modal('hide');
                    this.$awn.success('✅ Pedido creado y WhatsApp enviado correctamente.', {
                        labels: { success: 'PEDIDO CREADO' }
                    });
                    
                    // Recargar historial para ver el pedido pendiente
                    await this.cargarHistorial();
                    this.mostrarHistorial = true;
                } else {
                    throw new Error('No se pudo crear el pedido correctamente');
                }
            }
        },
        
        async procesarPagoFlow() {
            // Crear o obtener el pedido
            let pedido = this.pedidoAPagar;
            let paymentId = null;
            
            if (!pedido) {
                // Crear pedido nuevo
                const response = await this.crearPedidoSinPago();
                pedido = response && response.data ? response.data : null;
                
                if (!pedido || !pedido.payment || !pedido.payment.id) {
                    throw new Error('No se pudo crear el pedido correctamente');
                }
            }
            
            paymentId = pedido.payment ? pedido.payment.id : null;
            
            if (!paymentId) {
                throw new Error('No se encontró el ID del pago');
            }
            
            console.log('💳 Creando pago Flow...');
            
            // Calcular el monto con comisión Flow
            const montoTotal = this.totalConComisionFlow;
            
            // Validar monto mínimo de Flow (350 CLP)
            if (montoTotal < 350) {
                this.$awn.alert('El monto mínimo para pago con tarjeta es $350 CLP. Total con comisión: $' + Math.round(montoTotal));
                throw new Error('El monto mínimo para Flow es 350 CLP. Monto actual: ' + Math.round(montoTotal) + ' CLP');
            }
            
            // Crear pago en Flow
            const url = BaseUrl.getUrl('api/local/flow/create-payment');
            console.log('🌐 URL Flow:', url);
            
            const flowResponse = await Connection.fetch(url, 'POST', {
                amount: montoTotal,
                subject: `Pedido #${pedido.id} - Fagotto`,
                email: this.me.email || 'cliente@fagotto.cl',
                pedido_id: pedido.id,
                payment_id: paymentId
            }, null, false);
            
            console.log('✅ Respuesta Flow completa:', flowResponse);
            console.log('✅ flowResponse.ok:', flowResponse.ok);
            console.log('✅ flowResponse.data:', flowResponse.data);
            console.log('✅ Tipo de flowResponse.data:', typeof flowResponse.data);
            console.log('✅ Claves de flowResponse.data:', flowResponse.data ? Object.keys(flowResponse.data) : 'null');
            
            // Intentar diferentes estructuras posibles
            let flowData = null;
            
            if (flowResponse.data) {
                // Opción 1: flowResponse.data.data (doble anidación)
                if (flowResponse.data.data) {
                    flowData = flowResponse.data.data;
                    console.log('📦 Usando flowResponse.data.data');
                }
                // Opción 2: flowResponse.data directamente
                else if (flowResponse.data.url || flowResponse.data.token) {
                    flowData = flowResponse.data;
                    console.log('📦 Usando flowResponse.data directamente');
                }
            }
            
            console.log('✅ flowData final:', flowData);
            
            if (flowResponse.ok && flowData && flowData.url && flowData.token) {
                // Enviar WhatsApp con link de Flow
                const flowUrl = flowData.url + '?token=' + flowData.token;
                
                // Enviar automáticamente por Twilio (igual que Linkify)
                await this.enviarNotificacionWhatsApp(pedido, flowUrl);
                
                // Cerrar modal
                $('#modalPagoStripe').modal('hide');
                this.$awn.success('✅ Link de pago enviado por WhatsApp.', {
                    labels: { success: 'PAGO FLOW' }
                });
                
                // Recargar historial
                await this.cargarHistorial();
                this.mostrarHistorial = true;
            } else {
                console.error('❌ Estructura de respuesta incorrecta:', flowResponse);
                console.error('❌ flowData:', flowData);
                const errorMsg = flowResponse.data && flowResponse.data.message ? flowResponse.data.message : 'Respuesta inválida';
                throw new Error('No se pudo crear el pago en Flow: ' + errorMsg);
            }
        },
        
        async crearPedidoSinPago() {
            // Crear pedido con status_payment='impagado' para Linkyfi
            // Filtrar solo productos con cantidad > 0
            const productosSeleccionados = this.productosCentralizados
                .filter(p => p.cantidad > 0)
                .map(p => ({
                    id: p.id,
                    name: p.producto,
                    quantity: p.cantidad,
                    price: p.precio_por_unidad,
                    unidad_medida: p.unidad_medida,
                    unidad_venta: p.unidad_venta,
                    vasos: 0
                }));
            
            const data = {
                contact_name: this.name,
                contact_phone: this.whatsapp,
                whatsapp: this.whatsapp,
                paymode: 'Transferencia',
                payment_status: 'impagado',
                status: 'nuevo', // Queda pendiente hasta que se pague
                products: JSON.stringify(productosSeleccionados),
                comment: this.comment || 'Pedido pendiente de pago',
                price: this.totalConIVA,
                subtotal: this.totalNeto,
                iva: this.totalIVA,
                emergency: 0,
                despacho: 0,
                app_id: this.app.Id
            };
            
            var formData = new FormData();
            for (let key in data) {
                formData.append(key, data[key] !== null && data[key] !== undefined ? data[key] : '');
            }
            
            Loader.fullPage();
            let request = await this.$store.dispatch('requests/newRequestPedidoFinal', formData);
            Loader.hide();
            
            if (request.success) {
                console.log('✅ Pedido creado con status_payment=impagado:', request.data);
                return request.data; // Retornar el pedido con payment.id
            } else {
                throw new Error(request.message || 'Error al crear el pedido');
            }
        },
        
        async enviarNotificacionWhatsApp(pedido, urlPago) {
            // Obtener lista de productos
            let productosTexto = '';
            let productos = [];
            
            if (pedido && pedido.products) {
                // Es un pedido existente
                productos = this.parseProducts(pedido.products);
            } else {
                // Es un pedido nuevo
                productos = this.productosCentralizados.filter(p => p.cantidad > 0);
            }
            
            // Construir lista de productos
            productos.forEach((prod, index) => {
                const nombre = prod.name || prod.producto;
                const cantidad = prod.quantity || prod.cantidad;
                const precio = prod.price || prod.precio_por_unidad;
                const subtotal = cantidad * precio;
                productosTexto += `${index + 1}. ${nombre}\n   ${cantidad}x $${this.formatNumber(precio)} = $${this.formatNumber(subtotal)}\n`;
            });
            
            // Calcular totales
            const totalNeto = pedido ? this.calcularNetoHistorial(pedido) : this.totalNeto;
            const totalIVA = pedido ? this.calcularIVAHistorial(pedido) : this.totalIVA;
            const totalConIVA = pedido ? this.calcularTotalHistorial(pedido) : this.totalConIVA;
            
            // Crear mensaje de WhatsApp con detalles completos
            const mensaje = `🛒 *Nuevo Pedido - ${this.app.Name || 'Fagotto'}*\n\n` +
                `👤 Cliente: ${this.name}\n` +
                `📱 Teléfono: ${this.whatsapp}\n` +
                `📅 Fecha: ${new Date().toLocaleString('es-CL')}\n\n` +
                `📦 *PRODUCTOS:*\n${productosTexto}\n` +
                `💰 *RESUMEN:*\n` +
                `   Subtotal: $${this.formatNumber(totalNeto)}\n` +
                `   IVA (19%): $${this.formatNumber(totalIVA)}\n` +
                `   ━━━━━━━━━━━━━━━━\n` +
                `   *TOTAL: $${this.formatNumber(totalConIVA)} CLP*\n\n` +
                `🔗 *Link de Pago:*\n${urlPago}\n\n` +
                `⏳ *Estado:* Pendiente de pago\n\n` +
                `❓ *¿Cómo hacer una transferencia?*\n` +
                `📹 Video tutorial: https://youtu.be/KHUNZqKNP5w\n\n` +
                `Por favor, completa el pago para confirmar tu pedido. ¡Gracias! 😊`;
            
            const mensajeCodificado = encodeURIComponent(mensaje);
            const numeroLimpio = this.whatsapp.replace(/[^\d]/g, '');
            
            console.log('📱 Enviando WhatsApp automáticamente via Twilio...');
            
            // Validar que haya número de WhatsApp
            if (!this.whatsapp || this.whatsapp.trim() === '') {
                console.warn('⚠️ No hay número de WhatsApp ingresado');
                this.$awn.warning('No se ingresó número de WhatsApp para enviar el mensaje');
                return;
            }
            
            // Enviar mensaje automáticamente via Twilio
            try {
                console.log('📤 Datos a enviar:', {
                    to: this.whatsapp,
                    message_length: mensaje.length,
                    pedido_id: pedido ? pedido.id : null
                });
                
                const response = await this.$store.dispatch('notifications/sendWhatsApp', {
                    to: this.whatsapp,
                    message: mensaje,
                    pedido_id: pedido ? pedido.id : null
                });
                
                if (response.success) {
                    this.$awn.success('✅ WhatsApp enviado correctamente a ' + this.whatsapp, {
                        labels: { success: 'MENSAJE ENVIADO' },
                        durations: { success: 5000 }
                    });
                    console.log('✅ WhatsApp enviado via Twilio:', response.data);
                } else {
                    throw new Error(response.message || 'Error al enviar WhatsApp');
                }
            } catch (error) {
                console.error('❌ Error enviando WhatsApp:', error);
                this.$awn.alert('Error al enviar WhatsApp: ' + error.message);
            }
        },
        
        enviarPorWhatsAppWeb(pedido, urlPago) {
            // Obtener lista de productos
            let productosTexto = '';
            let productos = [];
            
            if (pedido && pedido.products) {
                // Es un pedido existente
                productos = this.parseProducts(pedido.products);
            } else {
                // Es un pedido nuevo
                productos = this.productosCentralizados.filter(p => p.cantidad > 0);
            }
            
            // Construir lista de productos
            productos.forEach((prod, index) => {
                const nombre = prod.name || prod.producto;
                const cantidad = prod.quantity || prod.cantidad;
                const precio = prod.price || prod.precio_por_unidad;
                const subtotal = cantidad * precio;
                productosTexto += `${index + 1}. ${nombre}\n   ${cantidad}x $${this.formatNumber(precio)} = $${this.formatNumber(subtotal)}\n`;
            });
            
            // Calcular totales
            const totalNeto = pedido ? this.calcularNetoHistorial(pedido) : this.totalNeto;
            const totalIVA = pedido ? this.calcularIVAHistorial(pedido) : this.totalIVA;
            const totalConIVA = pedido ? this.calcularTotalHistorial(pedido) : this.totalConIVA;
            
            // Crear mensaje de WhatsApp con detalles completos
            const mensaje = `🛒 *Nuevo Pedido - ${this.app.Name || 'Fagotto'}*\n\n` +
                `👤 Cliente: ${this.name}\n` +
                `📱 Teléfono: ${this.whatsapp}\n` +
                `📅 Fecha: ${new Date().toLocaleString('es-CL')}\n\n` +
                `📦 *PRODUCTOS:*\n${productosTexto}\n` +
                `💰 *RESUMEN:*\n` +
                `   Subtotal: $${this.formatNumber(totalNeto)}\n` +
                `   IVA (19%): $${this.formatNumber(totalIVA)}\n` +
                `   ━━━━━━━━━━━━━━━━\n` +
                `   *TOTAL: $${this.formatNumber(totalConIVA)} CLP*\n\n` +
                `🔗 *Link de Pago:*\n${urlPago}\n\n` +
                `⏳ *Estado:* Pendiente de pago\n\n` +
                `❓ *¿Cómo hacer una transferencia?*\n` +
                `📹 Video tutorial: https://youtu.be/KHUNZqKNP5w\n\n` +
                `Por favor, completa el pago para confirmar tu pedido. ¡Gracias! 😊`;
            
            // Codificar mensaje para URL
            const mensajeCodificado = encodeURIComponent(mensaje);
            const numeroLimpio = this.whatsapp.replace(/[^\d]/g, '');
            
            // Construir URL de wa.me
            const waUrl = `https://wa.me/${numeroLimpio}?text=${mensajeCodificado}`;
            
            console.log('📱 Abriendo WhatsApp Web con wa.me');
            console.log('🔗 URL:', waUrl);
            
            // Abrir WhatsApp Web
            window.open(waUrl, '_blank');
            
            this.$awn.success('✅ WhatsApp abierto. Envía el mensaje al cliente.', {
                labels: { success: 'WHATSAPP WEB' }
            });
        },
        
        // ========================================
        // MÉTODO ORIGINAL (YA NO SE USA DIRECTAMENTE)
        // ========================================
        async newRequest() {
            this.submitted = true;
            if (!this.validar_form()) {
                return;
            }
            
            // Actualizar stock una última vez antes de enviar
            await this.actualizarStockSilencioso();
            
            // Filtrar solo productos con cantidad > 0
            const productosSeleccionados = this.productosCentralizados
                .filter(p => p.cantidad > 0)
                .map(p => ({
                    id: p.id,
                    name: p.producto,
                    quantity: p.cantidad,
                    price: p.precio_por_unidad,
                    unidad_medida: p.unidad_medida,
                    unidad_venta: p.unidad_venta,
                    vasos: 0 // Campo requerido por RequestsController
                }));
            
            // Validar que no haya productos sin stock
            const productosSinStock = productosSeleccionados.filter(p => {
                const productoDB = this.productosCentralizados.find(prod => prod.id === p.id);
                return productoDB && productoDB.stock !== null && productoDB.stock <= 0;
            });
            
            if (productosSinStock.length > 0) {
                const nombres = productosSinStock.map(p => p.name).join(', ');
                this.$awn.alert(`⚠️ Los siguientes productos se agotaron: ${nombres}. Actualiza tu pedido.`);
                // Remover productos sin stock del carrito
                productosSinStock.forEach(p => {
                    const producto = this.productosCentralizados.find(prod => prod.id === p.id);
                    if (producto) producto.cantidad = 0;
                });
                this.calcularTotal();
                return;
            }
            
            const data = {
                contact_name: this.name,
                contact_phone: this.whatsapp,
                whatsapp: this.whatsapp,
                paymode: this.paymode,
                status: 'nuevo',
                products: JSON.stringify(productosSeleccionados),
                comment: this.comment || 'Sin comentarios', // Texto por defecto si está vacío
                price: this.totalConIVA, // Total final con IVA
                subtotal: this.totalNeto, // Total neto sin IVA
                iva: this.totalIVA, // IVA calculado
                emergency: 0,
                despacho: 0,
                app_id: this.app.Id
            };
            
            console.log('📤 Datos a enviar:', data);
            console.log('🔑 app_id tipo:', typeof this.app.Id, 'valor:', this.app.Id);
            
            var formData = new FormData();
            // Enviar todos los campos, incluso los vacíos
            for (let key in data) {
                formData.append(key, data[key] !== null && data[key] !== undefined ? data[key] : '');
            }
            
            this.waitResponse = true;
            Loader.fullPage();
            let request = await this.$store.dispatch('requests/newRequestPedidoFinal', formData);
            Loader.hide();
            
            if (request.success) {
                this.$awn.success('Pedido enviado exitosamente', { labels: { success: 'CORRECTO' } });
                
                // Limpiar formulario
                this.name = this.me.fullname;
                this.phone = "";
                this.paymode = 'Efectivo';
                this.comment = "";
                this.totalPedido = 0;
                
                // Resetear cantidades
                this.productosCentralizados.forEach(p => p.cantidad = 0);
                
                this.submitted = false;
                
                // Recargar historial y cambiar a vista de historial
                await this.cargarHistorial();
                this.mostrarHistorial = true;
            } else {
                console.log('❌ Error response:', request.data);
                console.log('❌ Errors detail:', request.data.errors);
                this.$awn.alert(request.data.message || 'Error al crear pedido');
            }
            this.waitResponse = false;
        },
        
        async cargarHistorial() {
            try {
                console.log('🔍 [HISTORIAL] Iniciando carga de historial...');
                
                // Prevenir múltiples cargas simultáneas
                if (this.isLoadingHistorial) {
                    console.warn('⚠️ [HISTORIAL] Ya hay una carga en progreso, saltando...');
                    return;
                }
                
                this.isLoadingHistorial = true;
                
                // Validar que app esté disponible
                if (!this.app || !this.app.Id) {
                    console.warn('⚠️ [HISTORIAL] App no disponible aún, esperando...');
                    this.isLoadingHistorial = false;
                    return;
                }
                
                console.log('🔍 [HISTORIAL] App ID:', this.app.Id);
                this.loadingHistorial = true;
                
                // Usar la misma estructura que pedidos.vue
                var params = '?params=true&appId=' + this.app.Id;
                
                console.log('🔍 [HISTORIAL] Llamando a getRequests con params:', params);
                var request = await this.$store.dispatch('requests/getRequests', params);
                console.log('📦 [HISTORIAL] Response completo:', request);
                
                if (!request.success) {
                    console.error('❌ [HISTORIAL] Request no exitoso:', request.data);
                    this.$awn.alert(request.data);
                    this.historialPedidos = [];
                    return;
                }
                
                // Misma estructura que pedidos.vue: request.data.items
                if (request.data && request.data.items) {
                    console.log('🔍 [HISTORIAL] Items recibidos:', request.data.items.length);
                    
                    // Ordenar por fecha más reciente primero
                    this.historialPedidos = request.data.items.sort((a, b) => {
                        return new Date(b.created_at) - new Date(a.created_at);
                    });
                    
                    console.log('✅ [HISTORIAL] Historial asignado:', this.historialPedidos.length, 'pedidos');
                    console.log('🔍 [HISTORIAL] Primer pedido:', this.historialPedidos[0]);
                } else {
                this.isLoadingHistorial = false;
                    console.warn('⚠️ [HISTORIAL] No hay items en la respuesta');
                    this.historialPedidos = [];
                }
            } catch (error) {
                console.error('❌ [HISTORIAL] Error cargando historial:', error);
                this.$awn.alert('Error al cargar historial de pedidos');
                this.historialPedidos = [];
            } finally {
                this.loadingHistorial = false;
                console.log('🏁 [HISTORIAL] Carga finalizada. Total pedidos:', this.historialPedidos.length);
            }
        },
        
        // ==================== MÉTODOS DE ADMINISTRACIÓN DE PRODUCTOS ====================
        
        abrirAdminProductos() {
            $('#modalAdminProductos').modal('show');
        },
        
        abrirAyudaTransferencia() {
            $('#modalAyudaTransferencia').modal('show');
        },
        
        nuevoProducto() {
            this.productoEditando = {
                id: null,
                producto: '',
                unidad_venta: 1,
                unidad_medida: 'kg',
                precio_por_unidad: 0,
                categoria: 'insumos',
                activo: 1
            };
            $('#modalEditarProducto').modal('show');
        },
        
        editarProducto(producto) {
            this.productoEditando = {
                ...producto,
                activo: producto.activo ? 1 : 0
            };
            $('#modalEditarProducto').modal('show');
        },
        
        async guardarProducto() {
            try {
                // Validar campos requeridos
                if (!this.productoEditando.producto || !this.productoEditando.unidad_venta || 
                    !this.productoEditando.precio_por_unidad || !this.productoEditando.categoria) {
                    this.$awn.alert('Por favor completa todos los campos requeridos');
                    return;
                }
                
                Loader.fullPage();
                
                const datos = {
                    producto: this.productoEditando.producto,
                    unidad_venta: parseFloat(this.productoEditando.unidad_venta),
                    unidad_medida: this.productoEditando.unidad_medida,
                    precio_por_unidad: parseFloat(this.productoEditando.precio_por_unidad),
                    categoria: this.productoEditando.categoria.toLowerCase(),
                    activo: this.productoEditando.activo ? 1 : 0
                };
                
                let request;
                
                if (this.productoEditando.id) {
                    // Actualizar producto existente
                    request = await this.$store.dispatch('products/updatePedidoFinalProduct', {
                        id: this.productoEditando.id,
                        data: datos
                    });
                } else {
                    // Crear nuevo producto
                    request = await this.$store.dispatch('products/createPedidoFinalProduct', datos);
                }
                
                Loader.hide();
                
                if (request.success) {
                    this.$awn.success(this.productoEditando.id ? 'Producto actualizado correctamente' : 'Producto creado correctamente');
                    $('#modalEditarProducto').modal('hide');
                    
                    // Recargar productos
                    await this.cargarPreciosCentralizados();
                } else {
                    this.$awn.alert(request.data || 'Error al guardar el producto');
                }
            } catch (error) {
                Loader.hide();
                console.error('❌ Error guardando producto:', error);
                this.$awn.alert('Error al guardar el producto');
            }
        },
        
        confirmarEliminar(producto) {
            if (confirm(`¿Estás seguro de eliminar el producto "${producto.producto}"?\n\nEsta acción no se puede deshacer.`)) {
                this.eliminarProducto(producto.id);
            }
        },
        
        async eliminarProducto(id) {
            try {
                Loader.fullPage();
                
                const request = await this.$store.dispatch('products/deletePedidoFinalProduct', id);
                
                Loader.hide();
                
                if (request.success) {
                    this.$awn.success('Producto eliminado correctamente');
                    
                    // Recargar productos
                    await this.cargarPreciosCentralizados();
                } else {
                    this.$awn.alert(request.data || 'Error al eliminar el producto');
                }
            } catch (error) {
                Loader.hide();
                console.error('❌ Error eliminando producto:', error);
                this.$awn.alert('Error al eliminar el producto');
            }
        },
        
        // ==================== FIN MÉTODOS DE ADMINISTRACIÓN ====================
        
        calcularNetoHistorial(pedido) {
            try {
                // Si el pedido tiene subtotal guardado, usarlo como NETO
                if (pedido.subtotal && parseFloat(pedido.subtotal) > 0) {
                    return parseFloat(pedido.subtotal);
                }
                
                // Si no tiene subtotal, asumir que el price es el total con IVA incluido
                // y calcular el neto: Neto = Total / 1.19
                const total = parseFloat(pedido.price || 0);
                const neto = total / 1.19;
                
                return Math.round(neto);
            } catch (error) {
                console.error('❌ Error en calcularNetoHistorial:', error, pedido);
                return 0;
            }
        },
        
        calcularIVAHistorial(pedido) {
            try {
                // Si el pedido tiene IVA guardado, usarlo
                if (pedido.iva && parseFloat(pedido.iva) > 0) {
                    return parseFloat(pedido.iva);
                }
                
                // Calcular el IVA basándose en el neto
                const neto = this.calcularNetoHistorial(pedido);
                const iva = Math.round(neto * 0.19);
                
                return iva;
            } catch (error) {
                console.error('❌ Error en calcularIVAHistorial:', error, pedido);
                return 0;
            }
        },
        
        calcularTotalHistorial(pedido) {
            try {
                // El total siempre es NETO + IVA
                const neto = this.calcularNetoHistorial(pedido);
                const iva = this.calcularIVAHistorial(pedido);
                
                return neto + iva;
            } catch (error) {
                console.error('❌ Error en calcularTotalHistorial:', error, pedido);
                return 0;
            }
        }
    }
}
</script>

<style scoped>
/* Importar estilos base responsivos de pedidos.css */
@import '../assets/css/pedidos.css';

/* ==================== PANTALLA DE BIENVENIDA ==================== */
.welcome-screen {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
}

.welcome-content {
    text-align: center;
    max-width: 600px;
    animation: fadeInUp 0.6s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.welcome-icon {
    width: 120px;
    height: 120px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.welcome-icon i {
    font-size: 60px;
    color: #667eea;
}

.welcome-title {
    font-size: 42px;
    color: white;
    margin: 0 0 15px 0;
    font-weight: 700;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.welcome-subtitle {
    font-size: 20px;
    color: rgba(255,255,255,0.9);
    margin: 0 0 20px 0;
    font-weight: 300;
}

.welcome-description {
    font-size: 16px;
    color: rgba(255,255,255,0.8);
    line-height: 1.6;
    margin-bottom: 40px;
}

.btn-iniciar {
    padding: 18px 50px;
    background: white;
    color: #667eea;
    border: none;
    border-radius: 50px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

.btn-iniciar:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.3);
}

.btn-iniciar i {
    margin-right: 10px;
}

.welcome-info {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 40px;
    flex-wrap: wrap;
}

.info-badge {
    background: rgba(255,255,255,0.2);
    padding: 10px 20px;
    border-radius: 20px;
    color: white;
    font-size: 14px;
    backdrop-filter: blur(10px);
}

.info-badge i {
    margin-right: 8px;
}

/* ==================== INTERFAZ PRINCIPAL ==================== */
.pedido-final-container {
    background: #f5f7fa;
    min-height: 100vh;
}

.pedido-final-main {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px 20px 100px 20px;
}

/* Header Moderno */
.modern-header {
    background: white;
    border-radius: 12px;
    padding: 25px 30px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.modern-header h2 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 28px;
}

.header-subtitle {
    margin: 0;
    color: #7f8c8d;
    font-size: 14px;
}

.header-right {
    display: flex;
    gap: 15px;
    align-items: center;
}

.btn-historial {
    padding: 12px 24px;
    background: white;
    border: 2px solid #667eea;
    color: #667eea;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-historial:hover {
    background: #667eea;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102,126,234,0.3);
}

.total-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 15px 30px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.total-label {
    color: rgba(255,255,255,0.9);
    font-size: 14px;
}

.total-value {
    color: white;
    font-size: 24px;
    font-weight: 700;
}

/* ==================== HISTORIAL DE PEDIDOS ==================== */
.historial-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 25px;
}

.historial-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.historial-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 22px;
}

.historial-header h3 i {
    margin-right: 10px;
    color: #667eea;
}

.btn-refresh-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: #f5f7fa;
    color: #667eea;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-refresh-small:hover:not(:disabled) {
    background: #667eea;
    color: white;
    transform: rotate(180deg);
}

.btn-refresh-small:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #95a5a6;
}

.empty-state i {
    color: #e0e6ed;
    margin-bottom: 20px;
}

.empty-state p {
    font-size: 18px;
    margin: 0;
}

.historial-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.historial-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid #e0e6ed;
    transition: all 0.3s;
}

.historial-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    border-color: #667eea;
}

.historial-card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pedido-numero {
    color: white;
    font-weight: 700;
    font-size: 18px;
}

.pedido-status {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-nuevo {
    background: #3498db;
    color: white;
}

.status-preparando {
    background: #f39c12;
    color: white;
}

.status-listo {
    background: #27ae60;
    color: white;
}

.status-entregado {
    background: #95a5a6;
    color: white;
}

.status-cancelado {
    background: #e74c3c;
    color: white;
}

/* Badge de estado de pago */
.payment-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.payment-badge i {
    font-size: 10px;
}

.payment-pending {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.payment-paid {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.payment-failed {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Botón de pagar en historial */
.btn-pagar-historial {
    transition: all 0.3s ease;
}

.btn-pagar-historial:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.btn-pagar-historial:active {
    transform: translateY(0);
}

.historial-card-body {
    padding: 20px;
}

.historial-info {
    margin-bottom: 20px;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    color: #2c3e50;
}

.info-row i {
    color: #667eea;
    width: 20px;
}

.historial-productos {
    background: #f5f7fa;
    padding: 15px;
    border-radius: 8px;
}

.historial-productos h5 {
    margin: 0 0 12px 0;
    color: #2c3e50;
    font-size: 14px;
    font-weight: 600;
}

.producto-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #e0e6ed;
}

.producto-item:last-child {
    border-bottom: none;
}

.producto-qty {
    background: #667eea;
    color: white;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    min-width: 40px;
    text-align: center;
}

.producto-name {
    flex: 1;
    color: #2c3e50;
    font-size: 14px;
}

.producto-price {
    color: #27ae60;
    font-weight: 600;
    font-size: 14px;
}

.historial-card-footer {
    background: #f8f9fa;
    padding: 20px;
    border-top: 2px solid #e0e6ed;
}

.historial-breakdown {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.historial-breakdown-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    background: white;
    border-radius: 8px;
    transition: all 0.3s;
}

.historial-breakdown-row:hover {
    background: #e9ecef;
    transform: translateX(3px);
}

.historial-breakdown-label {
    font-size: 13px;
    font-weight: 700;
    color: #2c3e50;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.historial-breakdown-value {
    font-size: 15px;
    font-weight: 800;
    color: #495057;
}

.historial-breakdown-separator {
    height: 2px;
    background: linear-gradient(90deg, transparent 0%, #667eea 50%, transparent 100%);
    margin: 8px 0;
    border-radius: 2px;
}

.historial-breakdown-row.historial-breakdown-total {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    padding: 12px 15px;
    margin-top: 5px;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    border: none;
}

.historial-breakdown-row.historial-breakdown-total:hover {
    transform: scale(1.02);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.historial-breakdown-label-total {
    font-size: 16px;
    font-weight: 900;
    color: white;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.historial-breakdown-value-total {
    font-size: 22px;
    font-weight: 900;
    color: white;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.pedido-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 16px;
    color: #2c3e50;
}

.pedido-total strong {
    color: #27ae60;
    font-size: 20px;
}

/* Formulario Compacto */
.form-compact {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.form-field label {
    display: block;
    margin-bottom: 8px;
    color: #2c3e50;
    font-weight: 500;
    font-size: 14px;
}

.form-field label i {
    margin-right: 8px;
    color: #667eea;
}

.form-field input,
.form-field select,
.form-field textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e6ed;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
}

.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}

.form-field.full-width {
    grid-column: 1 / -1;
}

/* Sección de Productos */
.productos-section {
    margin-bottom: 25px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 22px;
}

.btn-refresh {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: white;
    border: 2px solid #e0e6ed;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-refresh:hover {
    border-color: #667eea;
    color: #667eea;
    transform: rotate(180deg);
}

.loading-modern {
    text-align: center;
    padding: 60px;
    background: white;
    border-radius: 12px;
    color: #7f8c8d;
    font-size: 16px;
}

.loading-modern i {
    font-size: 24px;
    margin-right: 10px;
}

/* Categorías */
.categoria-group {
    margin-bottom: 30px;
}

.categoria-title {
    color: #2c3e50;
    font-size: 20px;
    font-weight: 800;
    margin: 0 0 20px 0;
    padding: 12px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    display: inline-block;
    box-shadow: 0 4px 15px rgba(102,126,234,0.3);
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Grid de Productos */
.productos-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.producto-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 3px solid transparent;
    position: relative;
    overflow: hidden;
}

.producto-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    opacity: 0;
    transition: opacity 0.3s;
}

.producto-card:hover::before {
    opacity: 1;
}

.producto-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 15px 35px rgba(102,126,234,0.3), 0 5px 15px rgba(0,0,0,0.1);
    border-color: rgba(102,126,234,0.2);
}

.producto-card.selected {
    border-color: #667eea;
    background: linear-gradient(145deg, rgba(102,126,234,0.08) 0%, rgba(118,75,162,0.08) 100%);
    box-shadow: 0 8px 25px rgba(102,126,234,0.25);
}

.card-header {
    margin-bottom: 15px;
    position: relative;
    padding-bottom: 12px;
    border-bottom: 2px solid #f0f0f0;
}

.producto-nombre {
    margin: 0 0 8px 0;
    color: #2c3e50;
    font-size: 17px;
    font-weight: 700;
    line-height: 1.3;
}

.producto-unidad {
    display: inline-block;
    color: white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(102,126,234,0.3);
}

.card-precio {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding: 15px;
    background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
    border-radius: 12px;
    border: 2px solid #e0e6ed;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.precio-label {
    color: #7f8c8d;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.precio-valor {
    color: #27ae60;
    font-size: 22px;
    font-weight: 800;
    text-shadow: 0 2px 4px rgba(39,174,96,0.2);
}

.card-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 12px;
}

.btn-qty {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(145deg, #667eea 0%, #764ba2 100%);
    color: white;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    box-shadow: 0 4px 12px rgba(102,126,234,0.3);
}

.btn-qty:hover:not(:disabled) {
    transform: scale(1.15) rotate(5deg);
    box-shadow: 0 6px 20px rgba(102,126,234,0.5);
}

.btn-qty:active:not(:disabled) {
    transform: scale(0.95);
}

.btn-qty:disabled {
    opacity: 0.3;
    cursor: not-allowed;
    background: #e0e6ed;
    box-shadow: none;
}

.input-qty {
    flex: 1;
    max-width: 100px;
    text-align: center;
    padding: 12px;
    border: 3px solid #e0e6ed;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 700;
    background: white;
    transition: all 0.3s;
}

.input-qty:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
    transform: scale(1.05);
}

.card-subtotal {
    text-align: center;
    padding: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(102,126,234,0.4);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 4px 15px rgba(102,126,234,0.4);
    }
    50% {
        box-shadow: 0 6px 25px rgba(102,126,234,0.6);
    }
}

.card-subtotal strong {
    font-size: 18px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Footer Fijo */
.footer-fixed {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-top: 2px solid #e0e6ed;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
    z-index: 1000;
}

.footer-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.footer-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.items-count {
    color: #7f8c8d;
    font-size: 13px;
}

.footer-total {
    color: #2c3e50;
    font-size: 16px;
}

.footer-total strong {
    color: #27ae60;
    font-size: 24px;
    margin-left: 10px;
}

.btn-finalizar {
    padding: 15px 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 50px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(102,126,234,0.4);
}

.btn-finalizar:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102,126,234,0.5);
}

.btn-finalizar:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-finalizar i {
    margin-right: 10px;
}

/* Responsive */
@media (max-width: 1400px) {
    .productos-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    .productos-grid-responsive {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 1024px) {
    .productos-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .productos-grid-responsive {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .modern-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .productos-grid {
        grid-template-columns: 1fr;
    }
    
    .productos-grid-responsive {
        grid-template-columns: 1fr;
    }
    
    .footer-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn-finalizar {
        width: 100%;
    }
}

/* Estilos adicionales específicos para pedidofinal */
.total-badge-responsive {
    background: linear-gradient(45deg, #28a745, #20c997);
    padding: 1rem 1.5rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    color: white;
}

.total-badge-responsive .total-label {
    display: block;
    font-size: 0.9rem;
    opacity: 0.9;
    margin-bottom: 0.25rem;
}

.total-badge-responsive .total-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
}

.categoria-group {
    margin-bottom: 2rem;
}

.categoria-title {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--primary-color, #007bff);
    padding-bottom: 0.5rem;
    border-bottom: 3px solid var(--primary-color, #007bff);
}

.productos-grid-responsive {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.producto-card-modern {
    background: white;
    border: 2px solid #e0e6ed;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.producto-card-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.producto-card-modern.selected {
    border-color: #28a745;
    background: linear-gradient(135deg, #ffffff 0%, #f0fff4 100%);
}

.producto-card-modern.producto-sin-stock {
    opacity: 0.6;
    background: #f8f9fa;
    border-color: #dc3545;
    position: relative;
}

.producto-card-modern.producto-sin-stock::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 10px,
        rgba(220, 53, 69, 0.05) 10px,
        rgba(220, 53, 69, 0.05) 20px
    );
    pointer-events: none;
    border-radius: 12px;
}

.stock-badge-bloqueado {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

.stock-badge-disponible {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}

.card-header-modern {
    margin-bottom: 1rem;
}

.card-header-modern .producto-nombre {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #212529;
}

.card-header-modern .producto-unidad {
    font-size: 0.85rem;
    color: #6c757d;
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    display: inline-block;
}

.card-precio-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.card-precio-modern .precio-label {
    font-size: 0.9rem;
    color: #6c757d;
}

.card-precio-modern .precio-valor {
    font-size: 1.2rem;
    font-weight: 700;
    color: #28a745;
}

.card-controls-modern {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.btn-qty-modern {
    width: 40px;
    height: 40px;
    border: 2px solid #007bff;
    background: white;
    color: #007bff;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-qty-modern:hover:not(:disabled) {
    background: #007bff;
    color: white;
}

.btn-qty-modern:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.input-qty-modern {
    flex: 1;
    height: 40px;
    text-align: center;
    border: 2px solid #e0e6ed;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
}

.card-subtotal-modern {
    text-align: right;
    padding: 0.75rem;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border-radius: 8px;
    font-size: 0.95rem;
}

.card-subtotal-modern strong {
    font-size: 1.1rem;
}

/* Historial grid responsivo */
.historial-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

@media (max-width: 576px) {
    .historial-grid {
        grid-template-columns: 1fr;
    }
    
    .total-badge-responsive .total-value {
        font-size: 1.2rem;
    }
    
    .categoria-title {
        font-size: 1.1rem;
    }
    
    .productos-grid-responsive {
        grid-template-columns: 1fr;
    }
}

/* Clase col-span-full para el textarea */
.col-span-full {
    grid-column: 1 / -1;
}

/* ==================== DESGLOSE DE TOTALES ==================== */
.total-badge-responsive {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    padding: 15px 30px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 20px rgba(40, 167, 69, 0.4);
}

.total-badge-responsive .total-label {
    font-size: 16px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: white;
}

.total-badge-responsive .total-value {
    font-size: 28px;
    font-weight: 900;
    color: white;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

/* Desglose de Precios */
.price-breakdown {
    animation: slideInUp 0.5s ease;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.breakdown-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    border: 3px solid #e0e6ed;
}

.breakdown-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
}

.breakdown-header i {
    font-size: 24px;
}

.breakdown-header h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.breakdown-body {
    padding: 25px;
}

.breakdown-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    margin-bottom: 12px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.3s;
}

.breakdown-row:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.breakdown-label {
    font-size: 16px;
    font-weight: 700;
    color: #2c3e50;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.breakdown-value {
    font-size: 20px;
    font-weight: 800;
    color: #495057;
}

.breakdown-separator {
    height: 3px;
    background: linear-gradient(90deg, transparent 0%, #667eea 50%, transparent 100%);
    margin: 20px 0;
    border-radius: 2px;
}

.breakdown-row.breakdown-total {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    padding: 20px 25px;
    margin-top: 15px;
    margin-bottom: 0;
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    border: none;
}

.breakdown-row.breakdown-total:hover {
    transform: scale(1.02);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.5);
}

.breakdown-label-total {
    font-size: 20px;
    font-weight: 900;
    color: white;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.breakdown-value-total {
    font-size: 32px;
    font-weight: 900;
    color: white;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    animation: pulseValue 2s infinite;
}

@keyframes pulseValue {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

/* Responsive para desglose */
@media (max-width: 768px) {
    .breakdown-header h4 {
        font-size: 16px;
    }
    
    .breakdown-label {
        font-size: 14px;
    }
    
    .breakdown-value {
        font-size: 18px;
    }
    
    .breakdown-label-total {
        font-size: 18px;
    }
    
    .breakdown-value-total {
        font-size: 26px;
    }
    
    .breakdown-row {
        padding: 12px 15px;
    }
    
    /* Desglose en historial responsive */
    .historial-breakdown-label {
        font-size: 12px;
    }
    
    .historial-breakdown-value {
        font-size: 14px;
    }
    
    .historial-breakdown-label-total {
        font-size: 14px;
    }
    
    .historial-breakdown-value-total {
        font-size: 18px;
    }
    
    .historial-breakdown-row {
        padding: 8px 12px;
    }
}

/* ==================== PRESUPUESTO DE PEDIDOS ==================== */
.presupuesto-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
    border: 2px solid rgba(102, 126, 234, 0.15);
    animation: fadeInUp 0.5s ease;
}

.presupuesto-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}

.presupuesto-header i {
    font-size: 20px;
}

.presupuesto-body {
    padding: 20px;
}

.presupuesto-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    margin-bottom: 10px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s;
}

.presupuesto-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.presupuesto-label {
    font-size: 14px;
    font-weight: 600;
    color: #495057;
}

.presupuesto-value {
    font-size: 16px;
    font-weight: 700;
    color: #2c3e50;
}

.presupuesto-value.highlight {
    color: #667eea;
    font-size: 18px;
}

.presupuesto-progress {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 2px dashed #dee2e6;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #495057;
}

.progress-bar-container {
    width: 100%;
    height: 24px;
    background: #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.progress-bar-fill {
    height: 100%;
    transition: width 0.5s ease, background 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    color: white;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.progress-bar-fill.progress-ok {
    background: linear-gradient(90deg, #10b981 0%, #34d399 100%);
}

.progress-bar-fill.progress-warning {
    background: linear-gradient(90deg, #f59e0b 0%, #fbbf24 100%);
}

.progress-bar-fill.progress-danger {
    background: linear-gradient(90deg, #ef4444 0%, #f87171 100%);
}

.alert-exceso {
    margin-top: 10px;
    padding: 10px 15px;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-left: 4px solid #ef4444;
    border-radius: 8px;
    color: #991b1b;
    font-weight: 600;
    font-size: 13px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Modal de Presupuesto */
.modal-overlay-presupuesto {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(8px);
    animation: fadeIn 0.3s ease-out;
}

.modal-dialog-presupuesto {
    max-width: 700px;
    width: 90%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.4s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-content-presupuesto {
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
}

.modal-header-presupuesto {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 16px 16px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title-presupuesto {
    margin: 0;
    font-size: 1.4rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-close-presupuesto {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-close-presupuesto:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.modal-body-presupuesto {
    padding: 2rem;
    overflow-y: auto;
    flex: 1;
}

.presupuesto-info .info-header {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border-radius: 10px;
}

.presupuesto-info .info-header i {
    font-size: 2rem;
    color: #667eea;
}

.presupuesto-info .info-header h6 {
    margin: 0;
    font-size: 1.2rem;
    color: #1f2937;
    font-weight: 600;
}

.info-section {
    margin-bottom: 1.5rem;
}

.info-intro {
    font-size: 1rem;
    line-height: 1.6;
    color: #4b5563;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    color: #667eea;
}

.ejemplo-card {
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border-radius: 12px;
    padding: 1.5rem;
    border: 2px solid #e5e7eb;
}

.ejemplo-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-weight: 700;
    color: #667eea;
    font-size: 1rem;
}

.ejemplo-content {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.ejemplo-step {
    display: flex;
    gap: 1rem;
    align-items: start;
}

.step-number {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    flex-shrink: 0;
}

.step-check {
    font-size: 1.5rem;
    flex-shrink: 0;
    line-height: 1;
}

.step-arrow {
    font-size: 1.2rem;
    flex-shrink: 0;
    line-height: 1;
}

.step-value {
    margin: 0.3rem 0 0 0 !important;
    color: #1f2937 !important;
    font-weight: 600 !important;
    font-size: 0.95rem;
}

.step-content {
    flex: 1;
}

.step-content strong {
    display: block;
    color: #1f2937;
    margin-bottom: 0.3rem;
}

.step-content p {
    margin: 0;
    color: #6b7280;
    line-height: 1.5;
}

.text-primary {
    color: #667eea !important;
}

.text-success {
    color: #10b981 !important;
}

.text-warning {
    color: #f59e0b !important;
}

.alert-info-custom {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-left: 4px solid #3b82f6;
    padding: 1rem;
    border-radius: 8px;
    display: flex;
    gap: 0.8rem;
}

.alert-info-custom i {
    color: #3b82f6;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.alert-tip-custom {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-left: 4px solid #f59e0b;
    padding: 1.2rem;
    border-radius: 12px;
    display: flex;
    gap: 1rem;
    align-items: start;
}

.tip-icon {
    font-size: 2rem;
    flex-shrink: 0;
    line-height: 1;
}

.tip-content {
    flex: 1;
}

.tip-content strong {
    color: #92400e;
    font-size: 1.05rem;
    display: block;
    margin-bottom: 0.5rem;
}

.tip-content {
    color: #78350f;
    line-height: 1.6;
}

.alert-info-custom strong {
    display: block;
    color: #1e40af;
    margin-bottom: 0.3rem;
}

.alert-info-custom p {
    margin: 0;
    color: #1e3a8a;
    font-size: 0.95rem;
}

.recomendaciones-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.recomendaciones-list li {
    padding: 0.8rem;
    background: white;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.recomendaciones-list li i {
    color: #10b981;
    font-size: 1.2rem;
}

.modal-footer-presupuesto {
    padding: 1.5rem 2rem;
    background: #f9fafb;
    border-radius: 0 0 16px 16px;
    display: flex;
    justify-content: center;
}

.btn-entendido {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 0.8rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.btn-entendido:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

</style>

