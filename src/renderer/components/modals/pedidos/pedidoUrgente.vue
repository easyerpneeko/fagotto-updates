<template>
    <div class="modal fade" id="pedidoUrgente" tabindex="-1" role="dialog" aria-labelledby="pedidoUrgente"
        aria-hidden="true" data-backdrop="false">
        <div class="modal-dialog lg-modal modal-dialog-centered" role="document" style="max-width: 1000px; margin: auto;">
            <div class="modal-content">
                <div class="modal-header bg-gradient">
                    <h5 class="modal-title fw-bold">� Preparar pedido Individual</h5>
                    <button type="button" class="close text-white" @click="closeModal(false)" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" style="overflow: auto; max-height: 70vh;">
                    <div class="row g-3 p-3">
                        <div class="col-md-5 p-3">
                            <div class="row d-flex">
                                <div class="col-md-12 mb-3">
                                    <h5 class="modal-title fw-bold">📋 Seleccionar productos</h5>
                                    <hr class="mt-2">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <h6 class="text-muted mb-2 fw-bold">🍽️ Productos disponibles ({{ productos.length }} productos)</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button 
                                            v-for="(producto, index) in productos" 
                                            :key="index"
                                            type="button"
                                            @click="addProduct(producto)"
                                            class="btn btn-outline-primary btn-sm rounded-pill product-btn"
                                            :title="`${producto.name} - $${formatNumber(producto.compra)}`">
                                            🛒 {{ producto.name }} <i class="fa fa-plus ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7 p-3">
                            <h5 class="modal-title mb-3 fw-bold">📋 Detalle del pedido Individual</h5>
                            
                            <table class="table table-sm table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">Producto</th>
                                        <th scope="col">Cantidad</th>
                                        <th scope="col">Unidad medida</th>
                                        <th scope="col">Precio x medida</th>
                                        <th scope="col">Total</th>
                                        <th scope="col" class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-if="productosPedido.length > 0">
                                        <tr v-for="(producto, index) in productosPedido" :key="index">
                                            <!-- Nombre producto -->
                                            <td class="fw-bold">{{ producto.name }}</td>

                                            <!-- Cantidad (input simple) -->
                                            <td style="width: 100px;">
                                                <input 
                                                    min="1" 
                                                    step="1"
                                                    class="form-control form-control-sm" 
                                                    type="number" 
                                                    v-model.number="producto.quantity" 
                                                    @input="calcularMontos()" 
                                                />
                                            </td>

                                            <!-- Unidad de medida (de la imagen) -->
                                            <td class="text-muted">{{ producto.unidad_medida }}</td>

                                            <!-- Precio por medida -->
                                            <td class="fw-bold">${{ formatNumber(producto.compra) }}</td>

                                            <!-- Total = cantidad × precio -->
                                            <td class="fw-bold text-success">
                                                ${{ formatNumber(producto.quantity * producto.compra) }}
                                            </td>

                                            <!-- Botón eliminar -->
                                            <td class="text-center">
                                                <button class="btn btn-danger btn-sm rounded-pill" @click="removeProduct(index)">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr v-if="productosPedido.length === 0">
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fa fa-shopping-cart fa-2x mb-2"></i>
                                            <br>No hay productos añadidos
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Resumen de montos en card -->
                            <div class="card shadow-sm">
                                <div class="card-body p-3">
                                    <h6 class="card-title text-muted mb-2 fw-bold">💰 Resumen de costos</h6>
                                    <table class="table table-sm mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th scope="col">Descripción</th>
                                                <th scope="col" class="text-end">Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Monto neto</td>
                                                <td class="text-end fw-bold">${{ formatNumber(this.montoNeto) }}</td>
                                            </tr>
                                            <tr v-if="totalDiscountAmount > 0" class="table-success">
                                                <td class="fw-bold">💰 Descuento productos</td>
                                                <td class="text-end fw-bold text-success">-${{ formatNumber(totalDiscountAmount) }}</td>
                                            </tr>
                                            <tr>
                                                <td>IVA {{ this.iva * 100 }}%</td>
                                                <td class="text-end">${{ formatNumber(this.montoIva) }}</td>
                                            </tr>
                                            <tr class="table-danger">
                                                <td class="fw-bold">Total</td>
                                                <td class="text-end fw-bold fs-5">${{ formatNumber(this.totalPrice) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer p-3">
                    <div class="d-flex justify-content-end gap-3 w-100 mt-4">
                        <button type="button" class="btn btn-outline-dark btn-lg" @click="closeModal()" title="Cerrar sin guardar cambios">
                            <i class="fa fa-times-circle me-2"></i>Cerrar
                        </button>
                        <button type="button" @click="addProducts()" class="btn btn-primary btn-lg" title="Confirmar pedido Individual">
                            <i class="fa fa-check-circle me-2"></i>Agregar productos al pedido
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
// Helpers
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';

export default {
    products: {
        type: Array,
        required: true
    },
    data() {
        return {
            totalPrice: 0,
            productosPedido: [],
            // 🎯 PRODUCTOS HARDCODEADOS DE LA IMAGEN - NO DEPENDEN DE LA API
            productos: [
                { id: 1, name: 'Mezcla Harina huevo', compra: 11835, unidad_medida: 'Bolsa 2.9 KG', quantity: 1 },
                { id: 2, name: 'Vaso', compra: 61000, unidad_medida: 'caja 500', quantity: 1 },
                { id: 3, name: 'Sobre de tenedor', compra: 32400, unidad_medida: 'caja 500', quantity: 1 },
                { id: 4, name: 'Bolsa Boloñesa', compra: 9604, unidad_medida: 'Bolsa 2 k', quantity: 1 },
                { id: 5, name: 'Queso', compra: 10668, unidad_medida: 'Bolsa 1k', quantity: 1 },
                { id: 6, name: 'Salsa Alfredo', compra: 8603, unidad_medida: 'Bolsa 2 k', quantity: 1 },
                { id: 7, name: 'Salsa Camarón', compra: 13840, unidad_medida: 'Bolsa 2 k', quantity: 1 },
                { id: 8, name: 'Salsa Champiñón', compra: 13869, unidad_medida: 'Bolsa 2 k', quantity: 1 },
                { id: 9, name: 'Salsa Pesto', compra: 37125, unidad_medida: 'Bolsa 2 k', quantity: 1 },
                { id: 10, name: 'Ciabatta Pesto', compra: 1597, unidad_medida: 'Unidad', quantity: 1 },
                { id: 11, name: 'Ciabatta Queso crema salame', compra: 1597, unidad_medida: 'Unidad', quantity: 1 },
                { id: 12, name: 'Ciabatta Aliato', compra: 1080, unidad_medida: 'Unidad', quantity: 1 },
          //      { id: 13, name: 'Salsa Pollo Mostaza', compra: 0, unidad_medida: 'Bolsa 2 k', quantity: 1 }
            ],
            productoSend: [],
            montoNeto: 0,
            despacho: 0,
            iva: 0.19,
            montoDespacho: 0,
            montoIva: 0,
            montoTotal: 0,
            montoEmergencia: 0,
            montoOpcionales: 0
        }
    },
    components: {},
    methods: {
        // 🎯 CERRAR MODAL
        async closeModal(refresh = false) {
            this.resetModal();
            $('#pedidoUrgente').modal('hide');
        },

        resetModal() {
            this.montoDespacho = 0;
            this.montoIva = 0;
            this.montoEmergencia = 0;
            this.montoTotal = 0;
            this.montoNeto = 0;
            this.productosPedido = [];
            this.productoSend = [];
            this.totalPrice = 0;
        },

        // 🎯 AGREGAR PRODUCTO - Ya viene con su unidad de medida hardcodeada
        addProduct(producto) {
            console.log('➕ Agregando producto:', producto.name);
            
            const existe = this.productosPedido.find(p => p.name === producto.name);
            if (existe) {
                console.log('⚠️ Producto ya existe:', producto.name);
                this.$awn.info(`${producto.name} ya está en el pedido`);
                return;
            }

            // Copiar el producto completo (ya tiene unidad_medida)
            const productoCopia = { ...producto };
            productoCopia.quantity = 1; // Siempre empieza en 1
            
            console.log('✅ Producto agregado:', productoCopia);
            
            this.productosPedido.push(productoCopia);
            this.calcularMontos();
        },

        // 🎯 REMOVER PRODUCTO
        removeProduct(index) {
            if (index >= 0 && index < this.productosPedido.length) {
                this.productosPedido.splice(index, 1);
                this.calcularMontos();
            }
        },

        formatNumber(number) {
            return FormatNumber.format(number);
        },

        // 🎯 CALCULAR MONTOS - Lógica MUY simple: cantidad × precio
        calcularMontos() {
            // Sumar todos los productos: cantidad × precio de compra
            this.montoNeto = this.productosPedido.reduce((total, producto) => {
                const subtotal = (producto.quantity || 0) * (producto.compra || 0);
                return total + subtotal;
            }, 0);

            // IVA
            this.montoIva = Math.ceil(this.iva * this.montoNeto);

            // Sin cargos adicionales
            this.montoEmergencia = 0;
            this.montoDespacho = 0;

            // Descuento (si existe)
            const descuento = this.totalDiscountAmount;

            // Total final
            this.totalPrice = Math.ceil(
                this.montoNeto + 
                this.montoDespacho + 
                this.montoIva + 
                this.montoEmergencia - 
                descuento
            );
        },

        // 🎯 CONFIRMAR Y ENVIAR PRODUCTOS
        addProducts() {
            if (this.productosPedido.length === 0) {
                this.$awn.alert("Es necesario agregar algún producto");
                return;
            }

            // Emitir eventos
            this.$emit('update-products', this.productosPedido);
            this.$emit('update-total', this.totalPrice);
            this.$emit('update-subtotal', this.montoNeto);
            this.$emit('update-monto-iva', this.montoIva);
            this.$emit('update-monto-emergencia', this.montoEmergencia);
            this.$emit('update-monto-despacho', this.montoDespacho);
            this.$emit('update-montoOpcionales', this.montoOpcionales);

            this.resetModal();
            $('#pedidoUrgente').modal('hide');
        }
    },
    computed: {
        isDespachoGratis: {
            get() { return ConfigHelper.ConfStr('modulos.pedidos.ajustes.despacho_gratis'); }
        },

        // 🎯 Descuento simple
        totalDiscountAmount: {
            get() {
                if (!this.productosPedido || !Array.isArray(this.productosPedido)) return 0;
                
                return this.productosPedido.reduce((total, producto) => {
                    if (producto.discount_percentage && producto.discount_percentage > 0) {
                        const subtotal = (producto.quantity || 0) * (producto.compra || 0);
                        const descuento = (subtotal * producto.discount_percentage) / 100;
                        return total + descuento;
                    }
                    return total;
                }, 0);
            }
        }
    }
}
</script>

<style scoped media="screen">
/* Estilos personalizados para el modal de pedido Individual */
.bg-gradient {
    background: linear-gradient(to right, #0d6efd, #0dcaf0);
    color: white;
}

.table td,
.table th {
    padding: 0.5rem;
    vertical-align: middle;
}

.btn-m {
    margin: 0rem;
    padding: 0.3rem 0.5rem;
    font-size: 0.8rem;
}

.conteoVasos {
    padding: 10px;
    font-size: 14px;
    border-radius: 5px;
    box-shadow: 0px 2px 4px rgb(0 0 0 / 20%), 0px 4px 8px rgb(0 0 0 / 10%);
    background-color: #343a40;
    color: white;
}

.btn-outline-info {
    padding-top: 0.3rem !important;
    padding-bottom: 0.3rem !important;
}

.fieldEdit {
    width: 80px;
}

/* Mejoras para el espaciado y visual */
.gap-2 {
    gap: 0.5rem;
}

.gap-3 {
    gap: 1rem;
}

.rounded-pill {
    border-radius: 50rem !important;
}

.shadow-sm {
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important;
}

.fw-bold {
    font-weight: 700 !important;
}

.fs-5 {
    font-size: 1.25rem !important;
}

/* Estilo para botones de productos */
.product-btn {
    font-size: 0.85rem;
    padding: 0.3rem 0.8rem;
    transition: all 0.2s ease;
}

.product-btn:hover {
    background-color: #e3f2fd !important;
    transform: scale(1.03);
    border-color: #1976d2 !important;
}

/* Modal más centrado */
.modal-dialog {
    max-width: 1000px;
    margin: auto;
}

/* Para compatibilidad con gap en flex */
@supports not (gap: 0.5rem) {
    .d-flex.gap-2 > * + * {
        margin-left: 0.5rem;
    }
    .d-flex.gap-3 > * + * {
        margin-left: 1rem;
    }
}

/* Mejoras adicionales para accesibilidad */
.btn:focus {
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.me-2 {
    margin-right: 0.5rem;
}

.ms-1 {
    margin-left: 0.25rem;
}

.mt-4 {
    margin-top: 1.5rem;
}

/* Estilos específicos para pedido Individual */
.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.table-danger {
    background-color: rgba(220, 53, 69, 0.1);
}

.text-success {
    color: #198754 !important;
}
</style>