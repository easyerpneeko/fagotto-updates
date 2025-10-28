<template>
  <div class="modal fade" id="modalCatalog" tabindex="-1" role="dialog" aria-labelledby="modalCatalog"
    aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog lg-modal modal-dialog-centered" role="document" style="max-width: 1000px; margin: auto;">
      <div class="modal-content">
        <div class="modal-header bg-gradient">
          <h5 class="modal-title fw-bold">🛒 Preparar pedido</h5>
          <button type="button" class="close text-white" @click="closeModal(false)" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <!-- 🎃 Banner de descuentos activos -->
        <div v-if="Array.isArray(activeDiscounts) && activeDiscounts.length > 0" class="alert alert-warning mb-0" style="background: linear-gradient(135deg, #ff9a56 0%, #ff6a00 100%); border: none; border-radius: 0; color: white; text-align: center; padding: 15px;">
          <h5 class="mb-2" style="font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
            🎃 ¡FELICIDADES! HAY DESCUENTOS ACTIVOS 🎃
          </h5>
          <p class="mb-1" style="font-size: 1.1rem;">
            <strong>{{ activeDiscounts.length }}</strong> producto(s) con descuento especial
          </p>
          <p class="mb-0" style="font-size: 0.9rem; opacity: 0.95;">
            ⏰ Válido hasta: <strong>{{ formatFecha(activeDiscounts[0].end_date) }}</strong>
          </p>
        </div>
        
        <div class="modal-body p-0" style="overflow: auto; max-height: 70vh;">
          <div class="row g-3 p-3">
            <div class="col-md-6 p-3">
              <div class="row d-flex">
                <div class="col-md-12 mb-3">
                  <h5 class="modal-title">📋 Información de productos</h5>
                  <hr class="mt-2">
                </div>

                <div class="col-md-12 mb-3">
                  <table class="table table-sm table-striped table-hover text-nowrap">
                    <thead class="table-dark">
                      <tr>
                        <th scope="col">Producto</th>
                        <th scope="col">Cantidad</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(producto, id) in productosFijos" :key="id">
                        <template
                          v-if="producto.name == 'Queso' || producto.name == 'Harina' || producto.name == 'Vaso' || producto.name == 'Botella de Huevos 1L'">

                          <td>{{ producto.name }}</td>

                          <td v-if="producto.name == 'Queso'">
                            {{ formatearMonto(producto.price) }}gr
                          </td>

                          <td v-else-if="producto.name == 'Botella de Huevos 1L' || producto.name == 'Harina'">
                            {{ formatearMonto(producto.price) }} Vasos
                            <i class="fas fa-info-circle ms-1" data-toggle="tooltip" data-placement="top"
                              :title="`Esto equivale a ${formatearMonto(producto.price)} Vasos`"></i>
                          </td>

                          <td v-else>{{ formatearMonto(producto.price) }}</td>
                        </template>
                      </tr>
                    </tbody>
                  </table>
                </div>
                
                <div class="col-md-12 mb-3">
                  <!-- Mensaje informativo de precios -->
                  <div class="alert alert-info mb-3" style="background-color: #fff3cd; border-color: #ffeaa7; color: #856404;">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>💰 Información de precios:</strong><br>
                    <span style="color: #d63384; font-weight: bold;">• ALFREDO y BOLOÑESA: $1.508 por vaso</span><br>
                    <span style="color: #198754; font-weight: bold;">• Otras salsas: $1.875 por vaso</span>
                  </div>
                  
                  <h6 class="text-muted mb-2 fw-bold">🌶️ Salsas disponibles</h6>
                  <div class="d-flex flex-wrap gap-2">
                    <button v-for="(salsa, id) in salsasDisponibles" :key="id" type="button" @click="addSalsa(salsa)"
                      class="btn btn-sm rounded-pill product-btn"
                      :class="checkProductDiscount(salsa) ? 'btn-warning' : 'btn-outline-primary'"
                      :disabled="salsa.stock < salsa.min_stock"
                      :title="checkProductDiscount(salsa) ? '🎃 ¡Producto con descuento!' : 'Click para añadir al pedido'">
                      <span v-if="checkProductDiscount(salsa)" class="discount-badge">🎃 -{{ checkProductDiscount(salsa).discount_value }}{{ checkProductDiscount(salsa).discount_type === 'percentage' ? '%' : '$' }}</span>
                      🛒 {{ salsa.name }} <i class="fa fa-plus ms-1"></i>
                    </button>
                  </div>
                </div>
                
                <div class="col-md-12 mb-3">
                  <h6 class="text-muted mb-2 fw-bold">🍽️ Productos opcionales</h6>
                  <div class="d-flex flex-wrap gap-2">
                    <button v-for="(producto, id) in opcionalesDisponibles" :key="id" type="button"
                      @click="addOpcional(producto)"
                      class="btn btn-sm rounded-pill product-btn"
                      :class="checkProductDiscount(producto) ? 'btn-warning' : 'btn-outline-success'"
                      :title="checkProductDiscount(producto) ? '🎃 ¡Producto con descuento!' : 'Click para añadir al pedido'">
                      <span v-if="checkProductDiscount(producto)" class="discount-badge">🎃 -{{ checkProductDiscount(producto).discount_value }}{{ checkProductDiscount(producto).discount_type === 'percentage' ? '%' : '$' }}</span>
                      ➕ {{ producto.name }} <i class="fa fa-plus ms-1"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6 p-3">
              <h5 class="modal-title mb-3 fw-bold">📋 Detalle del pedido</h5>
              
              <table class="table table-sm table-striped table-hover">
                <thead class="table-dark">
                  <tr>
                    <th scope="col">Producto</th>
                    <th scope="col">Cantidad</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(producto, id) in productosPedido" :key="id">
                    <template v-if="producto.name == 'Botella de Huevos 1L'">
                      <td class="fw-bold">{{ producto.name }}</td>
                      <td class="fw-bold">{{ producto.quantity }} botellas</td>
                      <td class="d-none">${{ producto.costo = formatearMonto(producto.quantity * producto.compra) }}
                      </td>
                    </template>
                    <template v-if="producto.name == 'Queso' || producto.name == 'Harina'">
                      <td class="fw-bold">{{ producto.name }}</td>
                      <td class="fw-bold">{{ producto.name == 'Queso' ? producto.quantity + 'kg' : producto.quantity + ' unidades' }}</td>
                      <td class="d-none">${{ producto.costo = formatearMonto(producto.quantity * producto.compra) }}
                      </td>
                    </template>
                    <template v-if="producto.name == 'Vaso'">
                      <td class="bg-primary text-white fw-bold">{{ producto.name }}</td>
                      <td class="bg-primary text-white fw-bold">{{ producto.quantity }} unidades</td>
                      <td class="bg-primary d-none">${{ producto.costo = formatearMonto(producto.quantity *
                        producto.price )}}</td>
                    </template>
                  </tr>
                </tbody>
              </table>

              <!-- Tabla Salsas -->
              <div v-if="salsasPedido.length > 0" class="mb-3">
                <h6 class="text-muted mb-2 fw-bold">🌶️ Salsas seleccionadas</h6>
                <table class="table table-sm table-striped table-hover">
                  <thead class="table-dark">
                    <tr>
                      <th scope="col">Salsa</th>
                      <th scope="col">Cantidad</th>
                      <th scope="col">Vasos</th>
                      <th scope="col">Precio</th>
                      <th scope="col" class="text-center">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(salsa, index) in salsasPedido" :key="index">
                      <td>{{ salsa.name }}</td>
                      <td>{{ salsa.quantity }}kg</td>
                      <td>
                        <input @input="validateInput()" :min="salsa.min_quantity" @change="calcularVasosSalsas()"
                          :step="salsa.min_quantity" class="form-control form-control-sm" type="number" v-model="salsa.vasos" />
                      </td>
                      <td class="fw-bold text-success">${{ formatearMonto(salsa.costo = salsa.vasos * getPrecioSalsa(salsa)) }}</td>
                      <td class="text-center">
                        <button class="btn btn-danger btn-sm rounded-pill" @click="removeSalsa(index)" title="Eliminar salsa">
                          <i class="fa fa-times"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Tabla opcionales -->
              <div v-if="opcionalPedido.length > 0" class="mb-3">
                <h6 class="text-muted mb-2 fw-bold">🍽️ Productos opcionales</h6>
                <table class="table table-sm table-striped table-hover">
                  <thead class="table-dark">
                    <tr>
                      <th scope="col">Producto</th>
                      <th scope="col">Cantidad</th>
                      <th scope="col">Precio</th>
                      <th scope="col" class="text-center">Acción</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(producto, index) in opcionalPedido" :key="index">
                      <td>{{ producto.name }}</td>
                      <td>
                        <input min="1" @change="calcularPrecioOpcionales()" class="form-control form-control-sm" type="number"
                          v-model="producto.quantity" />
                      </td>
                      <td class="fw-bold text-success">${{ formatearMonto(producto.costo = producto.price * 1) }}</td>
                      <td class="text-center">
                        <button class="btn btn-danger btn-sm rounded-pill" @click="removeOpcional(index)" title="Eliminar producto">
                          <i class="fa fa-times"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
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
                        <td class="text-end fw-bold">${{ formatearMonto(montoNeto) }}</td>
                      </tr>
                      <tr class="table-warning">
                        <td class="fw-bold">🎃 Descuento Halloween</td>
                        <td class="text-end fw-bold text-success">-${{ formatearMonto(totalDescuento) }}</td>
                      </tr>
                      <tr>
                        <td>Despacho {{ this.despacho * 100 }}%</td>
                        <td class="text-end">${{ formatearMonto(montoDespacho) }}</td>
                      </tr>
                      <tr>
                        <td>IVA {{ this.iva * 100 }}%</td>
                        <td class="text-end">${{ formatearMonto(montoIva) }}</td>
                      </tr>
                      <tr class="table-success">
                        <td class="fw-bold">Total</td>
                        <td class="text-end fw-bold fs-5">${{ formatearMonto(montoTotal) }}</td>
                      </tr>
                      <tr v-if="halloween20Active" class="table-danger">
                        <td class="fw-bold">🎃 Descuento Halloween 20%</td>
                        <td class="text-end fw-bold text-danger">-${{ formatearMonto(descuentoHalloween20) }}</td>
                      </tr>
                      <tr v-if="halloween20Active" class="table-success">
                        <td class="fw-bold">💀 TOTAL FINAL</td>
                        <td class="text-end fw-bold fs-4 text-success">${{ formatearMonto(totalConDescuentoHalloween) }}</td>
                      </tr>
                      <tr v-if="totalDescuento > 0 || halloween20Active" class="table-info">
                        <td colspan="2" class="text-center fw-bold" style="color: #0c5460;">
                          💰 ¡Ahorraste ${{ formatearMonto(totalDescuento + descuentoHalloween20) }} con descuentos! 🎉
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  
                  <!-- 🎃 Botón Halloween 20% descuento -->

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
            <button type="button" @click="addProducts()" class="btn btn-success btn-lg" title="Confirmar y agregar productos al pedido">
              <i class="fa fa-check-circle me-2"></i>Agregar productos
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>

// Helpers y plugins
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
import Connection from '@/helpers/Connection.js';
import BaseUrl from '@/helpers/baseUrl.js';
// import Loader from '@/helpers/Loader';
import $ from 'jquery';

export default {
  products: {
    type: Array,
    required: true
  },
  data() {
    return {
      vasosMinimos: 0,
      vasos: 0,
      // queso: 4,
      // harina: 22,
      // huevo: 180,
      precioVaso: 0,
      kiloAgg: false,
      //Para saber si el pedido es completo o medio 1= completo, 2 = medio
      tipoPedido: 2,
      vasosSalsas: 0,
      totalVasos: 0,
      totalPrice: 0,
      productosPedido: {
        // 1: { name: 'Vasos', quantity: 162, vasos: 1 },
      },
      salsasPedido: [],
      opcionalPedido: [],
      productosFijos: {
        // //Producto | quantity | Vasos
        // 1: { name: 'Huevos', quantity: 1, vasos: 1 },
      },

      salsasDisponibles: {
        // 1: { name: 'Alfredo', quantity: 70, vasos: 1 },
      },
      opcionalesDisponibles: {

      },
      vasosSalsas: 0,
      kiloSalsas: 0,
      productoSend: [],
      montoNeto: 0,
      montoIva: 0,
      montoDespacho: 0,
      despacho: 0,
      iva: 0.19,
      montoTotal: 0,
      montoOpcionales: 0,
      isMultiplo: true,
      
      // 🎃 SISTEMA DE DESCUENTOS
      activeDiscounts: [],
      totalDescuento: 0,
      productosConDescuento: [],
      
      // 🎃 Halloween 20% descuento
      halloween20Active: false,
      descuentoHalloween20: 0,
      totalConDescuentoHalloween: 0,

    }
  },
  components: {

  },
  async mounted() {
    console.log('🔥🔥🔥 CATALOG MOUNTED - INICIANDO CARGA DE DESCUENTOS 🔥🔥🔥');
    //Trae los productos fijo de db 
    await this.cargarProductosFijos();
    // 🎃 Cargar descuentos activos
    await this.loadActiveDiscounts();
    console.log('🎃 Descuentos después de cargar:', this.activeDiscounts);
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
  },
  methods: {
    async closeModal(refresh = false) {
      //Volvemos los arreglos al estado inicial
      await this.cargarProductosFijos();
      this.tipoPedido = 1;
      this.vasosSalsas = 0;
      this.totalVasos = 0;
      this.kiloSalsas = 0;
      this.salsasPedido = [];
      this.opcionalPedido = [];
      this.totalPrice = 0;
      this.vasos = 0;
      this.montoIva = 0;
      this.montoDespacho = 0;
      this.montoNeto = 0;
      this.montoTotal = 0;
      this.totalDescuento = 0;
      this.productosConDescuento = [];
      $('#modalCatalog').modal('hide');
    },
    
    // 🎃 MÉTODOS DE DESCUENTOS
    async loadActiveDiscounts() {
      console.log('🎃 [1/5] Iniciando loadActiveDiscounts...');
      try {
        // Obtener el ID de la aplicación desde el store
        console.log('🎃 [2/5] Store completo:', this.$store.state);
        console.log('🎃 [2/5] Store.main:', this.$store.state.main);
        console.log('🎃 [2/5] Store.main.app:', this.$store.state.main.app);
        
        let applicationId = this.$store.state.main.app && this.$store.state.main.app.id;
        
        // 🔥 FALLBACK: Si no está en el store, intentar obtener desde ConfigHelper
        if (!applicationId) {
          console.warn('⚠️ App no en store, intentando ConfigHelper.Config()...');
          const appData = ConfigHelper.Config();
          console.log('🎃 [2.5/5] ConfigHelper.Config():', appData);
          if (appData && appData.Id) {
            applicationId = appData.Id;
            console.log('✅ ID obtenido exitosamente de ConfigHelper:', applicationId);
          }
        }
        
        console.log('🎃 [3/5] Application ID obtenido:', applicationId);
        
        if (!applicationId) {
          console.warn('⚠️ No se pudo obtener el ID de la sucursal');
          return;
        }
        
        const url = BaseUrl.getUrl(`api/local/discount/branch/${applicationId}`);
        console.log('🎃 [4/5] URL a consultar:', url);
        
        const response = await Connection.request('get', url);
        console.log('🎃 [4.5/5] Response recibido:', response);
        console.log('🎃 [4.6/5] response.data tipo:', typeof response.data);
        console.log('🎃 [4.7/5] response.data contenido completo:', JSON.stringify(response.data));
        console.log('🎃 [4.8/5] response.data.discounts existe?:', response.data.discounts);
        console.log('🎃 [4.9/5] Array.isArray(response.data):', Array.isArray(response.data));
        
        // Si hay error, mostrar detalles completos
        if (response && !response.success) {
          console.error('❌ ERROR DEL SERVIDOR:', response);
          console.error('❌ Status:', response.status);
          console.error('❌ Data completo:', JSON.stringify(response.data, null, 2));
          
          // Intentar mostrar mensaje de error específico
          if (response.data) {
            if (response.data.message) console.error('❌ Mensaje:', response.data.message);
            if (response.data.error) console.error('❌ Error:', response.data.error);
            if (response.data.exception) console.error('❌ Exception:', response.data.exception);
            if (response.data.file) console.error('❌ Archivo:', response.data.file);
            if (response.data.line) console.error('❌ Línea:', response.data.line);
          }
        }
        
        if (response && response.success) {
          // El servidor devuelve los descuentos en response.data.discounts
          const discountsData = response.data.discounts || response.data.data || response.data;
          
          // Asegurarse de que activeDiscounts sea siempre un array
          this.activeDiscounts = Array.isArray(discountsData) ? discountsData : [];
          console.log('✅ Descuentos cargados:', this.activeDiscounts);
          console.log('✅ Cantidad de descuentos:', this.activeDiscounts.length);
          console.log('✅ Primer descuento:', this.activeDiscounts[0]);
          console.log('✅ Array.isArray check:', Array.isArray(this.activeDiscounts));
          
          if (this.activeDiscounts.length > 0) {
            this.$awn.success(`🎃 ¡Hay ${this.activeDiscounts.length} descuentos activos para esta sucursal!`);
          } else {
            console.warn('⚠️ Array de descuentos está vacío');
          }
        } else {
          console.warn('🎃 Response sin success o sin data:', response);
          console.warn('🎃 response.success:', response.success);
          console.warn('🎃 response.data:', response.data);
          this.activeDiscounts = [];
        }
      } catch (error) {
        console.error('❌ Error cargando descuentos:', error);
        this.activeDiscounts = [];
      }
      console.log('🎃 [5/5] loadActiveDiscounts finalizado. activeDiscounts:', this.activeDiscounts);
    },
    
    checkProductDiscount(producto) {
      // Validar que activeDiscounts sea un array
      if (!Array.isArray(this.activeDiscounts) || this.activeDiscounts.length === 0) {
        return null;
      }
      
      // Buscar si el producto tiene descuento activo
      const descuentoActivo = this.activeDiscounts.find(discount => {
        const productoId = Number(producto.id);
        const discountProductId = Number(discount.product_id);
        return discountProductId === productoId;
      });
      
      if (descuentoActivo) {
        console.log('✅ ¡DESCUENTO ENCONTRADO!', producto.name, 'ID:', producto.id, '→', descuentoActivo.discount_percentage + '%');
        // Normalizar el formato del descuento para compatibilidad
        return {
          ...descuentoActivo,
          discount_type: 'percentage',
          discount_value: parseFloat(descuentoActivo.discount_percentage)
        };
      }
      
      return null;
    },
    
    aplicarDescuento(producto, descuento) {
      let montoDescuento = 0;
      
      if (descuento.discount_type === 'percentage') {
        montoDescuento = (producto.costo * descuento.discount_value) / 100;
      } else if (descuento.discount_type === 'fixed') {
        montoDescuento = descuento.discount_value;
      }
      
      return montoDescuento;
    },
    
    formatFecha(fecha) {
      if (!fecha) return '';
      const date = new Date(fecha);
      return date.toLocaleDateString('es-CL', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric' 
      });
    },
    addSalsa(salsa) {
      const existingSalsa = this.salsasPedido.find((s) => s.name === salsa.name);
      if (existingSalsa) {
        console.log(`Salsa ${salsa.name} ya existente.`);
      } else {
        const salsaCopia = Object.assign({}, salsa);
        salsaCopia.vasos = salsaCopia.min_quantity;
        
        // 🎃 Verificar si tiene descuento
        const descuento = this.checkProductDiscount(salsaCopia);
        
        if (descuento) {
          salsaCopia.descuento = descuento;
          this.productosConDescuento.push({
            nombre: salsaCopia.name,
            descuento: descuento.discount_value,
            tipo: descuento.discount_type
          });
          
          this.$awn.success(
            `🎃 ¡${salsaCopia.name} tiene ${descuento.discount_value}% de descuento hasta el ${this.formatFecha(descuento.end_date)}!`,
            { durations: { success: 5000 } }
          );
        }
        
        this.salsasPedido.push(salsaCopia);
      }
      this.calcularVasosSalsas()
    },
    removeSalsa(index) {
      if (index >= 0 && index < this.salsasPedido.length) {
        this.salsasPedido.splice(index, 1);
      }
      this.calcularVasosSalsas()
    },
    removeOpcional(index) {
      if (index >= 0 && index < this.opcionalPedido.length) {
        this.opcionalPedido.splice(index, 1);
      }
      this.calcularPrecioOpcionales();
    },
    addOpcional(opcional) {
      const existingOpcional = this.opcionalPedido.find((o) => o.name === opcional.name);
      if (existingOpcional) {
        // existingSalsa.quantity += 100;
        // existingSalsa.vasos += 1;
        console.log(`Opcional ${opcional.name} ya existente.`);
      } else {
        const opcionalCopia = Object.assign({}, opcional);
        opcionalCopia.vasos = 1;
        
        // 🎃 Verificar si tiene descuento
        const descuento = this.checkProductDiscount(opcionalCopia);
        if (descuento) {
          opcionalCopia.descuento = descuento;
          this.productosConDescuento.push({
            nombre: opcionalCopia.name,
            descuento: descuento.discount_value,
            tipo: descuento.discount_type
          });
          
          this.$awn.success(
            `🎃 ¡${opcionalCopia.name} tiene ${descuento.discount_value}${descuento.discount_type === 'percentage' ? '%' : '$'} de descuento hasta el ${this.formatFecha(descuento.end_date)}!`,
            { durations: { success: 5000 } }
          );
        }
        
        this.opcionalPedido.push(opcionalCopia);
        // console.log(this.opcionalPedido);
      }
      this.calcularPrecioOpcionales();
    },
    calcularCantidades() {

      for (const key in this.productosPedido) {
        if (this.productosPedido[key].name == "Vaso") {
          //Para mantener el minimo de vasos
          if (this.vasosSalsas > this.vasosMinimos) {
            this.vasos = this.vasosSalsas;
            this.productosPedido[key].quantity = this.vasos;
          } else if (this.vasosSalsas <= this.vasosMinimos) {
            this.vasos = this.vasosMinimos;
            this.productosPedido[key].quantity = this.vasos;
          }
        }
        else {

          if (this.productosPedido[key].name == "Queso") {
            this.productosPedido[key].quantity = (this.productosPedido[key].price * this.vasos) / 1000;

          }
          if (this.productosPedido[key].name == "Harina") {
            this.productosPedido[key].quantity = Math.ceil((this.vasos / this.productosPedido[key].price));
          }
          if (this.productosPedido[key].name == "Botella de Huevos 1L") {
            this.productosPedido[key].quantity = (this.vasos / this.productosPedido[key].price);

            // Para saber si el pedido es multiplo
            if ((this.vasos % this.productosPedido[key].price) != 0) {
              this.isMultiplo = false;
              this.$awn.info("El pedido debe ser de minimo " + this.vasosMinimos + " vasos y multiplo de " + this.productosPedido[key].price);
            } else {
              this.isMultiplo = true;
            }
          }
        }
      }
    },
    formatNumber(number) {
      return FormatNumber.format(number);
    },
    calcularVasosSalsas() {

      //Contamos los vasos 
      this.vasosSalsas = 0;
      this.kiloSalsas = 0;

      this.salsasPedido.forEach((salsa) => {
        //Contamos los vasos de salsa
        this.vasosSalsas += +salsa.vasos;
        let salsaDisponible;
        for (var key in this.salsasDisponibles) {
          if (this.salsasDisponibles[key].name == salsa.name) {
            salsaDisponible = this.salsasDisponibles[key];
          }
        }
        this.validateInput(salsa);
        salsa.quantity = ((salsa.vasos * salsaDisponible.quantity) / 1000).toFixed(2);
        // Calcular costo con precio específico por salsa
        salsa.costo = salsa.vasos * this.getPrecioSalsa(salsa);
      });

      this.calcularCantidades();
      this.calcularMontos(); // 🎃 Calcular montos con descuentos

    },
    calcularPrecioOpcionales() {
      this.opcionalPedido.forEach((producto) => {
        //Contamos los vasos de salsa
        // this.vasosSalsas += +salsa.vasos;
        let productoDisponible;
        for (var key in this.opcionalesDisponibles) {
          if (this.opcionalesDisponibles[key].name == producto.name) {
            productoDisponible = this.opcionalesDisponibles[key];
          }
        }

        producto.price = (producto.quantity * productoDisponible.price);
        this.montoOpcionales = 0;
        for (var key in this.opcionalPedido) {
          this.montoOpcionales += this.opcionalPedido[key].price;
        }

      });
      this.calcularMontos(); // 🎃 Calcular montos con descuentos
    },
    addProducts() {
      console.log('Productos pedido: ', this.productosPedido);
      console.log('Salsas: ', this.salsasPedido);
      console.log('kilos Salsas:', this.kiloSalsas);
      console.log('Opcionales:', this.opcionalPedido);

      console.log('multiplo:', this.isMultiplo);
      console.log('vasos:', this.vasosSalsas);

      if (this.vasosSalsas < this.vasosMinimos) {
        this.$awn.info("El pedido debe ser de minimo " + this.vasosMinimos + " vasos de salsa");
        return false;
      }

      if (!this.isMultiplo) {
        this.$awn.info("El pedido debe ser multiplo de la cantidad de vasos de la Botella de Huevos");
        return false;
      }

      for (var key in this.productosPedido) {
        this.salsasPedido.push(this.productosPedido[key]);
      }
      for (var key in this.opcionalPedido) {
        this.salsasPedido.push(this.opcionalPedido[key]);
      }
      this.productoSend = this.salsasPedido
      // console.log('Pedido: ', this.salsasPedido);

      if (this.productoSend.length == 0) {
        this.$awn.alert("Es necesario agregar algun producto");
        return false;
      }

      //El monto neto ya incluye el precio de los opcionales

      this.totalPrice = Math.ceil(this.montoNeto + this.montoDespacho + this.montoIva);

      this.$emit('update-products', this.productoSend);
      this.$emit('update-total', Math.ceil((this.totalPrice)));

      this.$emit('update-subtotal', Math.ceil((this.montoNeto)));
      this.$emit('update-monto-iva', Math.ceil((this.montoIva)));
      this.$emit('update-monto-despacho', Math.ceil((this.montoDespacho)));

      this.$emit('update-montoOpcionales', Math.ceil((this.montoOpcionales)));

      $('#modalCatalog').modal('hide');

      this.productoSend = [];
      // this.productosPedido = {};
      this.salsasPedido = [];
      this.opcionalPedido = [];
      this.totalPrice = 0;
    },
    async getProducts() {
      // Iniciando peticion
      // Loader.dinamic();
      var request = await this.$store.dispatch("products/getProductsOfIndex");
      // Loader.hide();
      // Verificando respuesta
      if (request.success) {
        this.productosFijos = request.data;

      }
      else this.$awn.alert('Error al obtener los productos');

    },
    formatearMonto(monto) {
      const montoSinDecimales = Math.ceil(monto);
      // const parteDecimal = monto.toFixed(2).split(".")[1];

      // // Eliminar "00" si son los dos últimos decimales
      // if (parteDecimal === "00") {
      //   return montoSinDecimales.toLocaleString();
      // }

      // Formatear con miles y decimales
      return `${montoSinDecimales.toLocaleString()}`;
    },
    async cargarProductosFijos() {

      await this.getProducts();
      for (const producto in this.productosFijos) {

        if (this.productosFijos[producto].name == 'Queso') {
          // const nuevoProductoPedido = {
          // name: this.productosFijos[producto].name,
          // quantity: this.convertirAKilogramosYRedondear(this.productosFijos[producto].price, this.vasos),
          // vasos: 1,
          // price: this.productosFijos[producto].price
          // };

          this.productosFijos[producto].quantity = this.convertirAKilogramosYRedondear(this.productosFijos[producto].price, this.vasos);
          this.productosFijos[producto].vasos = 1;

          this.$set(this.productosPedido, producto, this.productosFijos[producto]);

        } else if (this.productosFijos[producto].name === 'Botella de Huevos 1L' || this.productosFijos[producto].name === 'Harina') {
          // const nuevoProductoPedido = {
          //   name: this.productosFijos[producto].name,
          //   quantity: (this.vasos / this.productosFijos[producto].price),
          //   vasos: 1,
          //   price: this.productosFijos[producto].price
          // };
          this.productosFijos[producto].quantity = (this.vasos / this.productosFijos[producto].price);
          this.productosFijos[producto].vasos = 1;

          this.$set(this.productosPedido, producto, this.productosFijos[producto]);

        } else if (this.productosFijos[producto].name === 'Vaso') {
          // const nuevoProductoPedido = {
          //   name: this.productosFijos[producto].name,
          //   quantity: this.vasos,
          //   vasos: 1,
          //   price: this.productosFijos[producto].price
          // };

          this.productosFijos[producto].quantity = this.vasos;
          this.productosFijos[producto].vasos = 1;

          this.$set(this.productosPedido, producto, this.productosFijos[producto]);
          this.vasosMinimos = Math.ceil(this.productosFijos[producto].min_quantity);
          //para obtener el precio del vaso - CORREGIDO: usar precio fijo 1875 para salsas normales
          this.precioVaso = 1875; // Precio fijo para salsas normales (no ALFREDO/BOLOÑESA)

        } else if (this.productosFijos[producto].category === 2) {
          // const salsaDisponible = {
          //   name: this.productosFijos[producto].name,
          //   quantity: this.productosFijos[producto].price,
          //   vasos: 1,
          //   price: this.productosFijos[producto].price,
          //   min_quantity: this.productosFijos[producto].min_quantity,
          // };

          this.productosFijos[producto].vasos = 1;
          this.productosFijos[producto].quantity = this.productosFijos[producto].price;

          this.$set(this.salsasDisponibles, producto, this.productosFijos[producto]);

          this.productosFijos[producto] = []; // Opcional: Eliminar el elemento de productosFijos

        } else if (this.productosFijos[producto].name === 'Despacho') {
          //Obteniendo el precio de despacho
          if (this.isDespachoGratis) {
            this.despacho = 0;
          } else {
            this.despacho = this.productosFijos[producto].price;
          }

        } else if (this.productosFijos[producto].category === 3) {
          // const opcionalDisponible = {
          //   name: this.productosFijos[producto].name,
          //   quantity: 1,
          //   vasos: 1,
          //   price: this.productosFijos[producto].price
          // };
          this.productosFijos[producto].quantity = 1;
          this.productosFijos[producto].vasos = 1;

          this.$set(this.opcionalesDisponibles, producto, this.productosFijos[producto]);

          this.productosFijos[producto] = []; // Opcional: Eliminar el elemento de productosFijos
        }
        // console.log('salsas : ', this.salsasDisponibles);
        console.log('🌶️ SALSAS DISPONIBLES CON IDs:');
        for (let key in this.salsasDisponibles) {
          console.log(`  - ${this.salsasDisponibles[key].name}: ID ${this.salsasDisponibles[key].id}`);
        }
      }
    },
    convertirAKilogramosYRedondear(gramos, multiplicador) {
      const kilogramos = (gramos * multiplicador) / 1000;
      return Math.ceil(kilogramos * 1000) / 1000;
    },
    validateInput(salsa) {
      // Redondea al múltiplo más cercano
      salsa.vasos = Math.round(salsa.vasos / salsa.min_quantity) * salsa.min_quantity;
    },
    getPrecioSalsa(salsa) {
      // Precios específicos para ALFREDO y BOLOÑESA
      if (salsa.name === 'ALFREDO' || salsa.name === 'BOLOÑESA') {
        return 1508;
      }
      // Precio normal para las demás salsas
      return this.precioVaso;
    },
    calcularMontoNetoReal() {
      // Calcular el monto neto sumando el costo real de cada salsa
      let montoSalsas = 0;
      this.totalDescuento = 0;
      
      this.salsasPedido.forEach((salsa) => {
        let costoSalsa = salsa.vasos * this.getPrecioSalsa(salsa);
        
        // 🎃 Aplicar descuento si existe
        if (salsa.descuento) {
          const descuentoMonto = this.aplicarDescuento({ costo: costoSalsa }, salsa.descuento);
          this.totalDescuento += descuentoMonto;
          costoSalsa -= descuentoMonto;
        }
        
        montoSalsas += costoSalsa;
      });
      
      // Aplicar descuentos a productos opcionales
      let montoOpcionalesConDescuento = 0;
      this.opcionalPedido.forEach((producto) => {
        let costoProducto = producto.price;
        
        // 🎃 Aplicar descuento si existe
        if (producto.descuento) {
          const descuentoMonto = this.aplicarDescuento({ costo: costoProducto }, producto.descuento);
          this.totalDescuento += descuentoMonto;
          costoProducto -= descuentoMonto;
        }
        
        montoOpcionalesConDescuento += costoProducto;
      });
      
      // Agregar el costo de los productos opcionales
      return montoSalsas + montoOpcionalesConDescuento;
    },
    
    // 🎃 Método para calcular todos los montos (neto, IVA, despacho, total)
    calcularMontos() {
      this.montoNeto = this.calcularMontoNetoReal();
      this.montoDespacho = this.montoNeto * this.despacho;
      this.montoIva = this.montoNeto * this.iva;
      this.montoTotal = this.montoNeto + this.montoDespacho + this.montoIva;
      
      if (this.totalDescuento > 0) {
        console.log('💰 TOTAL DESCUENTO HALLOWEEN: $' + this.totalDescuento.toFixed(0));
      }
    },
    
    // 🎃 Activar/desactivar descuento Halloween 20%
    toggleHalloween20() {
      this.halloween20Active = !this.halloween20Active;
      
      if (this.halloween20Active) {
        this.$awn.success('🎃 Descuento Halloween 20% aplicado!', { durations: { success: 3000 } });
      } else {
        this.$awn.info('Descuento Halloween removido');
      }
    }
  },
  computed: {
    isDespachoGratis: { get() { return ConfigHelper.ConfStr('modulos.pedidos.ajustes.despacho_gratis'); } }
  },
}
</script>

<style scoped media="screen">
/* Estilos personalizados para el modal de catálogo */
.bg-gradient {
  background: linear-gradient(to right, #2196f3, #21cbf3);
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

.btn-outline-success.product-btn:hover {
  background-color: #e8f5e8 !important;
  border-color: #28a745 !important;
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
  box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.25);
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

/* 🎃 Estilos para sistema de descuentos */
.discount-badge {
  background: #ff6a00;
  color: white;
  padding: 2px 6px;
  border-radius: 10px;
  font-size: 0.7rem;
  font-weight: bold;
  margin-right: 4px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.btn-warning.product-btn {
  background: linear-gradient(135deg, #ffd93d 0%, #ff9a56 100%);
  border: 2px solid #ff6a00;
  color: #000;
  font-weight: bold;
  box-shadow: 0 4px 8px rgba(255, 106, 0, 0.3);
}

.btn-warning.product-btn:hover {
  background: linear-gradient(135deg, #ff9a56 0%, #ff6a00 100%);
  transform: scale(1.08);
  box-shadow: 0 6px 12px rgba(255, 106, 0, 0.5);
}

.table-warning {
  background-color: #fff3cd !important;
}

.table-info {
  background-color: #d1ecf1 !important;
}

/* 🎃 Botón Halloween 20% */
.btn-halloween-20 {
  background: linear-gradient(135deg, #ff6600 0%, #ff9933 100%);
  border: 2px solid #ff3300;
  color: white;
  font-weight: bold;
  padding: 12px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 10px rgba(255, 102, 0, 0.4);
}

.btn-halloween-20:hover:not(:disabled) {
  background: linear-gradient(135deg, #ff3300 0%, #ff6600 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(255, 51, 0, 0.6);
}

.btn-halloween-20.active {
  background: linear-gradient(135deg, #28a745 0%, #5cb85c 100%);
  border-color: #1e7e34;
}

.btn-halloween-20:disabled {
  background: #6c757d;
  border-color: #6c757d;
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
