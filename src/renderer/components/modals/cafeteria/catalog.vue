<template>
  <div class="modal fade catalog-modal" id="modalCatalog" tabindex="-1" role="dialog" aria-labelledby="modalCatalog"
    aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
      <div class="modal-content modern-modal">
        <!-- Header moderno con glassmorphism -->
        <div class="modal-header">
          <div class="header-content">
            <div class="header-icon-container">
              <div class="header-icon">
                <i class="fas fa-store"></i>
              </div>
              <div class="icon-glow"></div>
            </div>
            <div class="header-text">
              <h4 class="modal-title">Catálogo de Productos</h4>
              <p class="header-subtitle">Selecciona productos para tu pedido</p>
            </div>
          </div>
          <div class="header-actions">
            <div class="stats-badge">
              <i class="fas fa-box"></i>
              <span>{{ products ? products.length : 0 }}</span>
            </div>
            <!-- Indicador de método de pago especial -->
            <div v-if="isSpecialPaymentDay" class="stats-badge" style="background: #10b981; color: white;" 
                 :title="'Método de pago Banco De Chile 20% DESCUENTO disponible - ' + (specialPaymentInfo ? specialPaymentInfo.day : 'Hoy')">
              <i class="fas fa-percentage"></i>
              <span>-20%</span>
            </div>
            <button type="button" class="btn-close" @click="closeModal(false)" aria-label="Close">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>

        <!-- Body con grid moderno -->
        <div class="modal-body">
          <div class="catalog-container">
            
            <!-- Sidebar de categorías con animaciones -->
            <div v-if="(categoriesInstalled && Allcategories && Allcategories.length > 0)"
              class="categories-sidebar">
              
              <div class="categories-section">
                <div class="categories-header">
                  <h6 class="categories-title">
                    <i class="fas fa-tags"></i>
                    Categorías
                  </h6>
                  <div class="categories-count">{{ Allcategories.length }}</div>
                </div>
                
                <div class="categories-list">
                  <button 
                    @click="changeCategorie(categorie.id)"
                    :class="['category-btn', (categorieNow == categorie.id) ? 'category-btn-active' : '']"
                    v-for="(categorie, index) in Allcategories" 
                    :key="index">
                    <div class="category-icon">
                      <i :class="getCategoryIcon(categorie.name)"></i>
                    </div>
                    <span class="category-name">{{ categorie.name }}</span>
                    <div class="category-arrow">
                      <i class="fas fa-chevron-right"></i>
                    </div>
                    <div class="category-glow"></div>
                  </button>
                </div>
              </div>
            </div>

            <!-- Área principal de productos -->
            <div class="products-area">
              <!-- Grid de productos -->
              <div class="products-grid">
                <div v-for="(product, index) in filteredList" :key="index"
                  v-if="(products && products.length > 0 && (!cecinaInstalled || (cecinaInstalled && product.cecina)))"
                  class="product-item">
                  <card-product-orders :product="product" @clickEmit="AddProduct" />
                </div>
                <div v-if="!products || products.length === 0" class="no-results">
                  <div class="no-results-content">
                    <i class="fas fa-search-minus no-results-icon"></i>
                    <h3>Sin resultados</h3>
                    <p>No se encontraron productos que coincidan con tu búsqueda</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Panel lateral del carrito con glassmorphism -->
            <div v-if="jsonTable.items.length > 0" class="cart-panel">
   

              <div class="cart-content">
                <!-- Campo de cliente si está habilitado -->
                <div v-if="ticket_sell_client" class="client-section">
                  <label class="form-label">
                    <i class="fas fa-user me-2"></i>
                    Nombre del cliente
                  </label>
                  <input v-model="clientTicket" class="form-control modern-input" 
                    type="text" placeholder="Ingresa el nombre del cliente..." />
                </div>

                <!-- Campo de descripción si está habilitado -->
                <div v-if="ticket_description" class="description-section">
                  <label class="form-label">
                    <i class="fas fa-file-text me-2"></i>
                    Descripción del pedido
                  </label>
                  <input v-model="ticketDescription" class="form-control modern-input"
                    type="text" placeholder="Agregar descripción del pedido..." />
                </div>

                <!-- Tabla de productos en el carrito -->
                <div class="cart-table-container">
                  <div class="table-scroll">
                    <custom-table v-model="jsonTable" v-slot="props" v-on:onBlur="FunctionBlurInputEditable">
                      <div class="action-buttons">
                        <button class="btn-comment" @click="openCommentProduct(props.item)" 
                          :title="'Agregar comentario'">
                          <i class="far fa-comment"></i>
                        </button>
                        <button class="btn-remove" @click="removeProduct(props.item)" 
                          :title="'Eliminar producto'">
                          <i class="fas fa-times"></i>
                        </button>
                      </div>
                    </custom-table>
                  </div>
                </div>

                <!-- Total del pedido -->
                <div class="cart-total">
                  <div class="total-row">
                    <span class="total-label">TOTAL</span>
                    <span class="total-amount">${{ formatNumber(total) }}</span>
                  </div>
                </div>

                <!-- Campo de comentario si está activo -->
                <div v-if="product_comentario" class="comment-section">
                  <label class="form-label">
                    <i class="fas fa-comment me-2"></i>
                    Comentario del producto
                  </label>
                  <textarea 
                    v-model="product_comentario_text" 
                    @keyup.enter="addCommentProduct(productComment)"
                    class="form-control modern-textarea"
                    placeholder="Agregar comentario especial..."></textarea>
                </div>
              </div>
            </div>

            <!-- Estado vacío del carrito con animación -->
            <div v-else class="empty-cart">
              <div class="empty-cart-content">
                <div class="empty-cart-animation">
                  <i class="fas fa-shopping-cart empty-cart-icon"></i>
                  <div class="floating-dots">
                    <div class="dot dot-1"></div>
                    <div class="dot dot-2"></div>
                    <div class="dot dot-3"></div>
                  </div>
                </div>
                <h4 class="empty-cart-title">Tu carrito está vacío</h4>
                <p class="empty-cart-description">Agrega productos seleccionándolos del catálogo</p>
                <div class="empty-cart-cta">
                  <i class="fas fa-hand-point-left"></i>
                  <span>Explora nuestros productos</span>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer">

          <button type="button" class="btn bg-dark text-white" @click="closeModal(false)">
            {{ (board && board.order == null || !board) ? 'Cerrar' : 'Volver' }}
          </button>

          <!-- Si hay una mesa seleccionada... -->
          <template>
            <span v-if="!ver_ticket && !solo_crear_ticket" class="m-0 p-0">
              <button v-if="ticket_sell && settingBoletaLocal" type="button" class="btn bg-primario text-white"
                @click="viewTicket('boleta_local')">
                Ticket + Efectivo
              </button>
              <button v-if="ticket_sell && settingBoleta" type="button" class="btn bg-dark text-white"
                @click="viewTicket('boleta')">
                Ticket + boleta (SII)
              </button>
              <button v-if="settingDebito" @click="viewTicket('debito')" type="button"
                class="btn bg-primario text-white">
                Ticket + Debito
              </button>
              <button v-if="settingTransferencia" @click="viewTicket('transferencia')" type="button"
                class="btn bg-primario text-white">
                Ticket + Transferencia
              </button>
              <button v-if="settingRappi" @click="viewTicket('rappi')" type="button" class="btn bg-primario text-white">
                Ticket + Rappi
              </button>
              <button v-if="settingJunaeb" @click="viewTicket('Junaeb')" type="button" class="btn bg-primario text-white">
                Ticket + Junaeb
              </button>
              <button v-if="settingUber" @click="viewTicket('uber')" type="button" class="btn bg-primario text-white">
                Ticket + Uber
              </button>
              <button v-if="settingNotaCredito" @click="viewTicket('nota_de_credito')" type="button"
                class="btn bg-primario text-white">
                Ticket + Nota de Credito
              </button>
              <button v-if="settingCredito" @click="viewTicket('credito')" type="button"
                class="btn bg-primario text-white">
                Ticket + Credito
              </button>
              <button v-if="settingCheque" @click="viewTicket('cheque')" type="button"
                class="btn bg-primario text-white">
                Ticket + Cheque
              </button>
              <button v-if="settingBanco" @click="viewTicket('banco')" type="button" class="btn bg-primario text-white">
                Ticket + Banco
              </button>
              <button v-if="settingSodexo" @click="viewTicket('sodexo')" type="button"
                class="btn bg-primario text-white">
                Ticket + Sodexo
              </button>
              <button v-if="ticket_sell && settingAmipass" @click="viewTicket('amipass')" type="button"
                class="btn bg-primario text-white">
                Ticket + Amipass
              </button>
              <button v-if="settingEdenred" @click="viewTicket('edenred')" type="button"
                class="btn bg-primario text-white">
                Ticket + Edenred
              </button>
              <button v-if="settingConvenio" @click="viewTicket('convenio_empresa')" type="button"
                class="btn bg-primario text-white">
                Ticket + Convenio
              </button>
              <button v-if="settingMulticaja" @click="viewTicket('multicaja')" type="button"
                class="btn bg-primario text-white">
                Ticket + Multicaja
              </button>
              <button v-if="settingPedidosYa" @click="viewTicket('pedidos_ya')" type="button"
                class="btn bg-primario text-white">
                Ticket + Boleta Pedidos Ya
              </button>
              <button v-if="settingPluxee" @click="viewTicket('pluxee')" type="button"
                class="btn bg-primario text-white">
                Ticket + Boleta Pluxee
              </button>
              
              <!-- Botón especial para Banco De Chile 20% (solo lunes y martes) -->
              <button v-if="settingBancoChile20 && isSpecialPaymentDay" @click="viewTicket('banco_chile_20')" type="button"
                class="btn bg-success text-white" style="font-weight: bold;">
                <i class="fas fa-percentage me-2"></i>
                Ticket + Banco De Chile 20% DESC
                <small class="d-block" style="font-size: 0.75em;">
                  Solo {{ specialPaymentInfo ? specialPaymentInfo.day : 'lunes y martes' }}
                </small>
              </button>
              
              <button v-if="ticket_sell_close" type="button" class="btn bg-primario text-white"
                @click="viewTicket('ticket_venta')">
                Ticket + Cerrar venta
              </button>

              <button v-if="ticket_sell && settingFactura" type="button" class="btn bg-secundario text-white"
                @click="viewTicket('factura')">
                Ticket + factura
              </button>
              <button v-if="order_kitchen_pending == false" type="button" class="btn bg-primario text-white"
                @click="viewTicket('ticket')">
                Crear ticket
              </button>


            </span>
            <span v-if="!ver_ticket && solo_crear_ticket" class="m-0 p-0">
              <button type="button" class="btn bg-primario text-white" @click="viewTicket('ticket')">
                Crear ticket
              </button>
            </span>

            <button v-else @click="viewTicket(false)" type="button" class="btn bg-primario text-white">
              Ver ticket
            </button>
          </template>

          <!-- Si hay un mesero seleccionado y solo eso... (Deshabilitado)-->
          <!-- <template v-if="onlyWaiter && false">
          <button v-if="ticket_sell && settingBoletaLocal" type="button" class="btn bg-primario text-white" @click="viewTicket('boleta_local')">
            Boleta local
          </button>
        </template> -->

        </div>

      </div>
    </div>
    <modalVerify :propVerify="propVerify" @refreshData="refreshData" />
    <ticket :typeCreateTicket="typeCreateTicket" v-model="ticketData" @changeValue="changeValue"
      @closeModal="closeModal" />
  </div>
</template>

<script>
// Componentes
import customTable from '@/components/tables/table.vue';
import cardProductOrders from '@/components/cards/card_product_orders.vue';
import ticket from '@/components/modals/cafeteria/createTicket.vue';
import modalVerify from '@/components/modals/verifyDelete.vue';

// Helpers y plugins
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
import Loader from '@/helpers/Loader';

export default {
  data() {
    return {
      product_comentario: false,
      product_comentario_text: '',
      product_comentario_id: '',
      typeCreateTicket: false,
      categorieNow: null,
      productsRequest: [],
      productComment: {},
      ticketData: {},
      ticketDescription: '',
      clientTicket: '',
      // data de nueva venta
      products: [],
      productSearch: '',
      productoSend: [],
      filters: ['name', 'barcode'],
      total: 0,
      timeoutT: null,
      gananciaTotal: null,
      propVerify: null,
      order: false,
      editOrder: false,
      type_sell: null,
      promoCategorieId:6,
      bebidasCategorieId:3,
      // salsaExtraId:11,
      promo_active:false,
      // Datos para método de pago especial Banco De Chile 20%
      chileTime: null,
      isSpecialPaymentDay: false,
      jsonTable: {
        btn: true,
        items: [],
        rows: [
          { key: 'name', class: '', permission: 'default' },
          { key: 'quantity', class: 'text-center', permission: 'default', edit: true },
          // { key: 'comment', class: 'text-center', permission: 'default' }, // Oculto temporalmente
        ],
        titles: [
          { label: 'Nombre', class: '', permission: 'default', type: false },
          { label: 'Cantidad', class: 'text-center w-30', permission: 'default', type: false },
          // { label: 'Comentario', class: 'text-center', permission: 'default', type: false }, // Oculto temporalmente
          { label: 'Acciones', class: 'text-center', permission: 'default', type: false },
        ]
      }
    }
  },
  components: {
    cardProductOrders,

    // Componentes de nueva venta
    modalVerify,
    ticket,
    customTable,
  },
  mounted() {
    //HavePermission
    this.refreshData(false, true);
    this.product_comentario = false;
    
    // Verificar la hora de Chile para el método de pago especial
    this.checkChileTime();
    
    // Verificar la hora cada 5 minutos por si cambia el día
    setInterval(() => {
      this.checkChileTime();
    }, 300000); // 5 minutos = 300,000 ms
  },
  methods: {
    // tickets (require board)

    //JC FECHA 2022-12-14
    viewTicket(val = false) {
      if (this.productoSend.length == 0) {
        this.$awn.alert("Es necesario agregar algun producto");
        return false;
      }
      if (this.gananciaInstalled && (this.gananciaTotal == null || this.gananciaTotal == '')) this.gananciaTotal = 0;

      // Si es el método de pago especial con 20%, aplicar el descuento
      if (val === 'banco_chile_20') {
        if (!this.isSpecialPaymentDay) {
          this.$awn.alert('Este método de pago solo está disponible los lunes y martes');
          return false;
        }
        
        // Calcular el 20% de descuento
        const originalTotal = this.total;
        const twentyPercentDiscount = originalTotal * 0.20;
        const totalWithDiscount = originalTotal - twentyPercentDiscount;
        
        // Confirmar con el usuario
        const confirmMessage = `¿Confirmar pago con Banco De Chile 20% DESCUENTO?\n\nTotal original: $${this.formatNumber(originalTotal)}\n20% descuento: -$${this.formatNumber(twentyPercentDiscount)}\nTotal final: $${this.formatNumber(totalWithDiscount)}`;
        
        if (!confirm(confirmMessage)) {
          return false;
        }
        
        // Actualizar el total con el 20% de descuento
        this.total = totalWithDiscount;
      }

      // Datos basicos
      this.ticketData = {
        products: this.productoSend,
        total: this.total,
        gananciaTotal: this.gananciaTotal
      }
      
      // Si es el método de pago especial con 20%, agregar información extra para reportes
      if (val === 'banco_chile_20') {
        this.ticketData.specialPayment = {
          paymentType: 'banco_chile_20',
          method: 'Banco De Chile 20%',
          description: 'Pago especial con 20% de descuento disponible solo lunes y martes',
          originalTotal: this.total / 0.80, // Total original antes del descuento
          discountAmount: (this.total / 0.80) - this.total, // Cantidad del descuento
          finalTotal: this.total,
          date: this.chileTime ? this.chileTime.toISOString() : new Date().toISOString(),
          dayOfWeek: this.chileTime ? this.chileTime.getDay() : new Date().getDay(),
          enabled: this.isSpecialPaymentDay
        };
      }
      
      if (this.ticket_description) {
        this.ticketData.description = this.ticketDescription;
      }
      if (this.ticket_sell_client) {
        this.ticketData.client_ticket = this.clientTicket;
      }

      console.log('SEARCH ticketData', this.ticketData);

      if (this.board && this.board.waiter && this.board.waiter.id) {
        // Si hay una mesa seleccionada y un mesero en dicha mesa
        this.ticketData.board_id = this.board.id;
        this.ticketData.waiter_id = this.board.waiter.id;
      } else if (this.onlyWaiter) {
        // Si solo contamos con un mesero pero no una mesa
        this.ticketData.board_id = null;
        this.ticketData.waiter_id = this.onlyWaiter;
      } else {
        this.$awn.alert("Error ni mesero ni mesa seleccionada.");
        return false;
      }

      if (this.board && this.board.order) this.ticketData.order = this.board.order;
      else this.ticketData.order = false;

      if (val) this.typeCreateTicket = val;
      $('#createTicket').modal('show');

    },

    closeModal(refresh = false) {

      // Borramos el Mesero actual en caso de estar seleccionado un OnlyWaiter (para el modo garzon)
      this.$store.commit('cafeteria/clearOnlyWaiter');

      this.typeCreateTicket = false;


      if (this.board && this.board.order == null && refresh == false) {

        this.board.waiter = null;
        $('#modalCatalog').modal('hide');

        // Deseleccionamos la mesa
        this.$store.commit('cafeteria/clearBoard');

        //Cuando cierre el modal sin tener ninguna mesa con orden activa limpio las 2 variables que almacena productos @jesus
        this.productoSend = [];
        this.jsonTable.items = [];

        return;
      }

      // Deseleccionamos la mesa
      this.$store.commit('cafeteria/clearBoard');

      this.productoSend = [];
      this.ticketDescription = '';
      this.clientTicket = '',
        this.total = null;
      this.gananciaTotal = null;
      $('#createTicket').modal('hide');
      $('#modalCatalog').modal('hide');
      if (refresh != false) {
        this.jsonTable.items = [];
        $('#completeOrder').modal('hide');
        this.$emit('refresh', true);
      }

    },
    changeValue(values) {
      this.productoSend = values.products;
      this.total = values.total;
    },

    formatNumber(number) {
      return FormatNumber.format(number);
    },
    deFormatNumber(number, backend = true) {
      if (backend) {
        return FormatNumber.deFormatBackend(number);
      } else {
        return FormatNumber.deFormat(number);
      }
    },

    // Método para obtener la hora de Chile y verificar si es lunes o martes
    async checkChileTime() {
      try {
        // Obtener la hora actual del sistema en zona horaria de Chile
        const chileTimeZone = 'America/Santiago';
        const now = new Date();
        
        // Crear fecha en zona horaria de Chile
        const chileTime = new Date(now.toLocaleString("en-US", {timeZone: chileTimeZone}));
        
        // Obtener el día de la semana (0=domingo, 1=lunes, 2=martes, etc.)
        const dayOfWeek = chileTime.getDay();
        
        // Verificar si es lunes (1) o martes (2)
        const isMonday = dayOfWeek === 1;
        const isThursday = dayOfWeek === 2;
        this.isSpecialPaymentDay = isMonday || isThursday;
        this.chileTime = chileTime;
        
        const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        
        console.log('=== MÉTODO DE PAGO ESPECIAL BANCO DE CHILE 20% ===');
        console.log('Hora actual de Chile:', chileTime.toLocaleString('es-CL'));
        console.log('Día de la semana:', dayNames[dayOfWeek]);
        console.log('Es día especial (lunes o martes):', this.isSpecialPaymentDay);
        console.log('Método de pago disponible:', this.isSpecialPaymentDay ? 'SÍ' : 'NO');
        
        return this.isSpecialPaymentDay;
      } catch (error) {
        console.error('Error obteniendo hora de Chile:', error);
        this.isSpecialPaymentDay = false;
        return false;
      }
    },

    // Refrescando nueva venta y obteniendo productos
    async refreshData(notRefreshCart = false, loaderTrue = false) {
      // Iniciando refrescamiento (carga y botones disabled)
      this.product_comentario = false;
      if (!notRefreshCart)
        this.productoSend = [];

      this.ticketDescription = '';
      this.clientTicket = '';
      this.total = 0;
      if (this.tickets && this.productsGet) {
        // Iniciando peticion
        if (!loaderTrue) Loader.dinamic();
        var request = await this.$store.dispatch("products/getProductsOfSell2");
        if (!loaderTrue) Loader.hide();
        // Verificando respuesta
        if (request.success) {
          this.products = (request.data.length == 0) ? false : request.data;
          this.productsRequest = request.data;
        }
        else this.$awn.alert('Error al obtener los productos');
      } else {
        this.$router.push('/inicio');
      }
    },

    // Cambiar de categoria
    changeCategorie(id,promo = false) {
      if (this.categorieNow != id) this.categorieNow = id;
      else this.categorieNow = null;

      if (this.categorieNow != null) {
        var productsFind = [];

        // Verificar si el id es igual a promoCategorieId
        if (this.categorieNow === this.promoCategorieId) {
          productsFind = this.productsRequest.filter(product => product.promo_active === 1);
        } else {
          productsFind = this.productsRequest.filter(product => {
            if (product.category == this.categorieNow) {
              return product;
            }
            return false;
          });
        }

        this.products = productsFind;
      } else {
        this.products = this.productsRequest;
      }

      if(promo){
        this.promo_active = true;
      }else{
        this.promo_active = false;
      }
    },

    search(input) {
      this.productSearch = input;
      if (input != null && input != '' && input.length < 1) { return [] }
      var productsFind = this.products.filter(product => {
        var index = 0;
        for (var i = 0; i < product.name.length; i++) {
          if (product.name.toLowerCase().startsWith(input.toLowerCase(), i)) {
            index = i;
          };
        }
        return product.name.toLowerCase().startsWith(input.toLowerCase(), index);
      })
      return productsFind.filter(product => product.cecina == false);
    },

    getSearchValue(result) {
      return result.name + '';
    },

    submitAutocomplete(result) {
      this.quantityAdd({
        id: result.id,
        name: result.name,
        price: result.price,
        promo_price : result.promo_price,
        quantity: parseInt(1),
        prices: result.prices,
        cecina: (result.cecina) ? true : false,
      });

      this.$refs.productAutocomplete.setValue('');
    },

    // Modal para verificar monto mayor a USD 5000
    openVerify(thing) {
      this.propVerify = {
        params: thing,
        title: 'Confirmacion de venta',
        text: '¿Usted esta seguro de realizar esta venta por un monto de ' + this.total + ' pesos chilenos?',
        store: 'sells/newSell',
        success: 'Venta realizada con exito'
      };
      $('#verifyDelete').modal('show');
    },

    //Agregar producto
    addProductQuantity(i, data) {
      // El subtotal es la cantidad actual por el nuevo precio que le envio (#subtotal)
      if(this.promo_active){
        data.subtotal = parseFloat(data.quantity) * parseFloat(data.promo_price);
        data.price = parseFloat(data.quantity) * parseFloat(data.promo_price);
        data.product_promo = true;
      }else{
        data.subtotal = parseFloat(data.quantity) * parseFloat(data.price);
      }
      // Si la ganancia esta instalada, agrego la ganancia ✅
      if (this.gananciaInstalled) {
        if (!data.ganancia) data.ganancia = 0; //Esto antes era (this.gananciaInstalled && data.ganancia); pero creo asi es mas correcto -feredev
        data.ganancia = parseFloat(data.quantity) * parseFloat(data.ganancia);
      }
      this.productoSend.push(data);
      this.jsonTable.items = this.productoSend;
      // Ejecuto #calculatePlus ✅
      this.calculatePlus(i, data, (this.priceUnitaryInstalled) ? true : false);
    },

    quantityAdd(data) { //#fere-warp1

      // Precio Variante Anterior
      if (!data.LastVariantPrice) data.LastVariantPrice = null;
      //#if (!data.LastVariantPrice) data.LastVariantPrice = 0;

      // Si hay mas de un producto agregado
      if (this.productoSend.length != 0) {
        var encontrado = false;
        // Recorro el arreglo de productos
        let conteo = 0;
        for (var i = 0; i < this.productoSend.length; i++) {

          // Si encuentra el producto
          if (data.id == this.productoSend[i].id) {
            // La cantidad es la antigua cantidad + la nueva cantidad ✅

            if (data.desde == "inputedit") {
              this.productoSend[i].quantity = parseFloat(data.quantity);
            } else {
              this.productoSend[i].quantity = parseFloat(this.productoSend[i].quantity) + parseFloat(data.quantity);

            }

            // El subtotal es la cantidad actual por el nuevo precio que le envio (#subtotal)
            if(this.promo_active){
              this.productoSend[i].subtotal = this.productoSend[i].quantity * parseFloat(data.promo_price);
            }else{
              this.productoSend[i].subtotal = this.productoSend[i].quantity * parseFloat(data.price);
            }
            
            // Si la ganancia esta instalada, agrego la ganancia ✅
            if (this.gananciaInstalled) {
              if (!data.ganancia) data.ganancia = 0; //🤔
              this.productoSend[i].ganancia = parseFloat(data.quantity) * parseFloat(data.ganancia);
            }
            // Ejecuto #calculatePlus ✅
            this.calculatePlus(i, this.productoSend[i], (this.priceUnitaryInstalled) ? true : false);
            encontrado = true;
            conteo++;
          }

        }


        // Si no ha encontrado el producto
        if (!encontrado) {
          this.addProductQuantity(this.productoSend.length, data);
        }

      } else { // Si no hay mas de un producto
        this.addProductQuantity(0, data);
      }
      console.log(this.productoSend);
      
    },

    AddProduct(data) {
      console.log(data);
      let price = 0;
      if (data.prices) {
        if (data.prices.length) {
          var precios = data.prices;
          for (var i = 0; i < precios.length; i++) {
            if (precios[i + 1]) {
              if (1 >= parseFloat(precios[i].cantidad) && 1 < parseFloat(precios[i + 1].cantidad)) {
                price = precios[i].precio;
                break;
              }
            } else {
              price = precios[i].precio;
            }
          }
        } else {
          price = data.price;
        }
      } else {
        price = data.price;
      }

      var product = {
        id: data.id,
        name: data.name,
        price: price,
        promo_price:data.promo_price,
        quantity: 1,
        prices: data.prices,
        cecina: (data.cecina) ? true : false,
        ganancia: data.ganancia,
        category: data.category,
        comment: null,
        product_variable_category: (data.product_variable_category) ? data.product_variable_category : false
      };

      // if(!this.promoInstalled){
        this.quantityAdd(product);
      // }
      // else if(this.promoInstalled){
        // if(data.id != this.salsaExtraId){
          // this.quantityAdd(product);
        // }
      // }


      if (this.isPromoTab) {
        if(data.product_variable_category != 0 && data.product_variable_category != null){
          this.changeCategorie(data.product_variable_category,true);
        }
      }else if(data.category == this.bebidasCategorieId){
        this.changeCategorie(null);
      }
    },

    removeProduct(item) {
      console.log("Producto a removeeerrr", item);
      for (var i = 0; i < this.productoSend.length; i++) {
        if (this.productoSend[i].id == item.id) this.productoSend.splice(i, 1);
      }
      this.product_comentario = false;
      this.jsonTable.items = this.productoSend;
      this.calculateTotal();
    },
    openCommentProduct(item) {
      this.product_comentario_text = item.comment;
      this.product_comentario_text != null ? item.comment : null;
      this.product_comentario_id = item.id;
      this.product_comentario = this.product_comentario = !this.product_comentario;

      if (this.product_comentario) this.productComment = { id: item.id }
      return;
    },
    addCommentProduct(item) {
      this.product_comentario = this.product_comentario = !this.product_comentario;
      console.log(item.id);
      console.log(this.product_comentario_text);
      console.log(this.productoSend);

      for (var i = 0; i < this.productoSend.length; i++) {
        if (this.productoSend[i].id == item.id) {
          this.productoSend[i].comment = this.product_comentario_text;
        }
      }
      this.jsonTable.items = this.productoSend;

    },

    //Agregar Ganancia y SubTotal en precio variante
    addGainSubTotalVariantPrice(index, isCecina = false, precioActual, cantidad, gananciaActual) { //No tengo creatividad para nombres bonitos justo ahora...

      //addGainSubTotalVariantPrice(index, data.cecina, precioActual, cantidad, gananciaActual);
      //index ---> this.productoSend[index]
      //isCecina
      //precioActual
      //cantidad
      //gananciaActual

      if (this.cecinaInstalled && isCecina) {
        this.productoSend[index].subtotal = (precioActual / 1000) * cantidad;
        if (this.gananciaInstalled) {
          this.productoSend[index].ganancia = (parseFloat(gananciaActual) / 1000) * cantidad;
        }
      } else {
        this.productoSend[index].subtotal = precioActual * cantidad;
        if (this.gananciaInstalled) {
          this.productoSend[index].ganancia = parseFloat(gananciaActual) * cantidad;
        }
      }


      //PD: hay una forma de reducir esto mas todavia pero no me quiero arriesgar

    },

    // Calculate plus //#fere-warp1
    calculatePlus(index, data, unitary_price = false) {
      // Obtengo el producto
      var productActual = Object.assign({}, this.products.find(element => element.id == data.id));
      console.log(this.promo_active);
      
      if(this.promo_active){
        var precioDeEntrada = this.productoSend[index].promo_price;
      }else{
        var precioDeEntrada = this.productoSend[index].price;
      }
      var precioVarianteDiferenteDeUnitario = false;

      if(this.promo_active){
        if (unitary_price) productActual.price = this.productoSend[index].promo_price;
      }else{
        if (unitary_price) productActual.price = this.productoSend[index].price;
      }
      

      // Obtenemos la CANTIDAD
      var cantidad = parseFloat(data.quantity);

      if (this.productoSend[index].LastVariantPrice)
        if (parseFloat(precioDeEntrada) !== parseFloat(this.productoSend[index].LastVariantPrice)) {
          precioVarianteDiferenteDeUnitario = true;
        }

      // Si tiene precios variantes
      if (productActual.prices && productActual.prices.length) {

        var precios = productActual.prices;
        // <INICIO DE RECORRIDO DE LOS PRECIOS VARIANTES>
        for (var p = 0; p < precios.length; p++) {

          var precioActual = parseFloat(precios[p].precio);//Precio actual pero del recorrido
          var gananciaActual = parseFloat(precios[p].ganancia);//Ganancia actual pero del recorrido

          if (precios[p + 1]) {
            var pCantidad = precios[p].cantidad;
            var pNextCantidad = precios[p + 1].cantidad;

            //Aqui es cuando hay mas de un precio
            if (cantidad >= parseFloat(pCantidad) && cantidad < parseFloat(pNextCantidad)) {

              var precioVarianteActual = precioActual;
              // Si hay un PRECIO UNITARIO PERSONALIZADO por encima de un PRECIO VARIANTE ACTUAL
              if (precioVarianteDiferenteDeUnitario) {
                //! Detectamos si el PRECIO VARIANTE ACTUAL es el mismo que el PRECIO VARIANTE NUEVO
                if (parseFloat(this.productoSend[index].LastVariantPrice) === parseFloat(precioVarianteActual)) {
                  // Si PVA == PVN; entonces hacemos que el PRECIO UNITARIO PERSONALIZADO prevalezca
                  precioActual = precioDeEntrada; //<--- Precio de entrada viene desde arriba al principio de la funcion
                }
              }

              //Aqui es cuando encontro lo que buscaba, sale del ciclo al final
              this.addGainSubTotalVariantPrice(index, data.cecina, precioActual, cantidad, gananciaActual);
              this.productoSend[index].price = precioActual;
              //ESTABLECEMOS EL ELEGIDO DEL PRECIO VARIANTE:
              this.productoSend[index].LastVariantPrice = precioVarianteActual;
              break;

            }
          } else {

            var precioVarianteActual = precioActual;
            // Si hay un PRECIO UNITARIO PERSONALIZADO por encima de un PRECIO VARIANTE ACTUAL
            if (precioVarianteDiferenteDeUnitario) {
              //! Detectamos si el PRECIO VARIANTE ACTUAL es el mismo que el PRECIO VARIANTE NUEVO
              if (parseFloat(this.productoSend[index].LastVariantPrice) === parseFloat(precioVarianteActual)) {
                // Si PVA == PVN; entonces hacemos que el PRECIO UNITARIO PERSONALIZADO prevalezca
                precioActual = precioDeEntrada; //<--- Precio de entrada viene desde arriba al principio de la funcion
              }
            }

            //Aqui es cuando llego al ultimo precio
            this.addGainSubTotalVariantPrice(index, data.cecina, precioActual, cantidad, gananciaActual);
            this.productoSend[index].price = precioActual;
            //ESTABLECEMOS EL ELEGIDO DEL PRECIO VARIANTE:
            this.productoSend[index].LastVariantPrice = precioVarianteActual;

          }

        }
        // <FINALIZACION DE RECORRIDO DE LOS PRECIOS VARIANTES>
      } else { // No recorro los precios variantes si no que uso un solo precio...
        if(this.promo_active){
          this.addGainSubTotalVariantPrice(index, data.cecina, productActual.promo_price, cantidad, productActual.ganancia);
        }else{
          this.addGainSubTotalVariantPrice(index, data.cecina, productActual.price, cantidad, productActual.ganancia);
        }
        
      }
      
      if(this.promo_active){
        this.productoSend[index].precioAnterior = this.productoSend[index].promo_price;
      }else{
        this.productoSend[index].precioAnterior = this.productoSend[index].price;
      }
      

      this.productoSend[index].quantity = cantidad;
      this.calculateTotal();
    },
    calculateTotal() {
      console.log("calculandoo totalll");
      this.total = 0;
      this.gananciaTotal = 0;
      for (var i = 0; i < this.productoSend.length; i++) {
        var price = 0;
        var ganancia = 0;
        price = this.productoSend[i].subtotal;
        this.total = parseFloat(this.total) + parseFloat(price);

        if (this.gananciaInstalled) {
          ganancia = this.productoSend[i].ganancia;
          this.gananciaTotal = this.gananciaTotal + parseFloat(ganancia);
        }
      }
    },

    FunctionBlurInputEditable(prod) {
      prod.desde = "inputedit";
      this.quantityAdd(prod);
    },

    getCategoryIcon(categoryName) {
      const icons = {
        'Pastas': 'fas fa-utensils',
        'Pasta': 'fas fa-utensils',
        'Bigoli': 'fas fa-utensils',
        'Fetuccini': 'fas fa-utensils',
        'Bebidas': 'fas fa-glass-cheers',
        'Bebida': 'fas fa-glass-cheers',
        'Extras': 'fas fa-plus-circle',
        'Extra': 'fas fa-plus-circle',
        'Focaccias': 'fas fa-bread-slice',
        'Focaccia': 'fas fa-bread-slice',
        'Promociones': 'fas fa-fire',
        'Promoción': 'fas fa-fire',
        'Promo': 'fas fa-fire',
        'Salsas': 'fas fa-pepper-hot',
        'Salsa': 'fas fa-pepper-hot',
        'Rappi': 'fas fa-motorcycle',
        'Postres': 'fas fa-ice-cream',
        'Postre': 'fas fa-ice-cream',
        'Cafe': 'fas fa-coffee',
        'Café': 'fas fa-coffee',
        'Pizza': 'fas fa-pizza-slice',
        'Pizzas': 'fas fa-pizza-slice',
        'Ensaladas': 'fas fa-leaf',
        'Ensalada': 'fas fa-leaf'
      };
      
      return icons[categoryName] || 'fas fa-folder';
    }

  },
  computed: {

    board: {
      get() { return this.$store.getters['cafeteria/getBoard'] },
      set(value) { this.$store.commit('cafeteria/setProperty', { key: 'board', data: value }) }
    },
    onlyWaiter: {
      get() { return this.$store.getters['cafeteria/getOnlyWaiter'] },
      set(value) { this.$store.commit('cafeteria/setProperty', { key: 'onlyWaiter', data: value }) }
    },//onlyWaiter

    categoriesInstalled: { get() { return ConfigHelper.ConfStr('modulos.productos.submodulos.categorias'); } },
    Allcategories: { get() { return this.$store.getters['products/categories']; } },

    // Computeds de nueva venta
    productsGet: { get() { return ConfigHelper.HavePermission('productos_obtener'); } },
    tickets: { get() { return ConfigHelper.HavePermission('getionar_tickets'); } },
    cecinaInstalled: { get() { return ConfigHelper.ConfStr('modulos.productos.ajustes.permitir_cecina'); } },
    gananciaInstalled: { get() { return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_ganancia'); } },
    priceUnitaryInstalled: { get() { return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_precio_unitario'); } },
    // Ajuste de procesar venta con ticket
    ticket_sell: { get() { return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.ticket_sell'); } },
    ticket_sell_close: { get() { return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.ticket_sell_proccess_close'); } },
    // Previsualizacion de la orden
    ver_ticket: { get() { return ConfigHelper.ConfStr('modulos.ventas.submodulos.ticket.ajustes.ver_ticket'); } },

    // Permitir descripcion en ticket
    ticket_description: { get() { return ConfigHelper.ConfStr('modulos.ventas.submodulos.ticket.ajustes.ticket_description'); } },
    //permitir cliente en ticket
    ticket_sell_client: { get() { return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.ticket_sell_client'); } },
    order_kitchen_pending: { get() { return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.order_kitchen_pending'); } },
    solo_crear_ticket: { get() { return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.solo_crear_ticket'); } },

    promoInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.productos.submodulos.precio_promo');}},

    settingBoleta: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta');
      }
    },
    settingBoletaLocal: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.boleta_local');
      }
    },
    settingFactura: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.factura');
      }
    },
    settingRappi: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.rappi');
      }
    },
    settingJunaeb: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.junaeb');
      }
    },
    settingUber: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.uber');
      }
    },
    settingNotaCredito: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.nota_de_credito');
      }
    },
    settingCredito: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.credito');
      }
    },
    settingEfectivo: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.efectivo');
      }
    },

    settingDebito: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.debito');
      }
    },

    settingTransferencia: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.transferencia');
      }
    },

    settingCheque: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.cheque');
      }
    },

    settingBanco: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.banco');
      }
    },

    settingSodexo: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.sodexo');
      }
    },

    settingAmipass: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.amipass');
      }
    },
    settingMulticaja: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.multicaja');
      }
    },
    settingEdenred: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.edenred');
      }
    },
    settingConvenio: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.convenio_empresa');
      }
    },

    settingPedidosYa: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.pedidos_ya');
      }
    },

    settingPluxee: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.pluxee');
      }
    },

    settingBancoChile20: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.banco_chile_20');
      }
    },

    settingVenderSinStock: {
      get() {
        return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_venta_sin_stock');
      }
    },

    filteredList: {
      get() {
        clearTimeout(this.timeoutT);

        const _this = this;
        this.timeoutT = setTimeout(function () {
          var findProduct = (_this.products) ? _this.products.find(element => element.barcode == _this.productSearch) : null;
          if (findProduct) {
            _this.quantityAdd({
              id: findProduct.id,
              name: findProduct.name,
              price: findProduct.price,
              promo_price:findProduct.promo_price,
              quantity: parseInt(1),
              prices: findProduct.prices,
              cecina: (findProduct.cecina) ? true : false,
            });
            _this.productSearch = '';
            _this.$refs.productAutocomplete.setValue('');
          }
          if (findProduct == undefined && _this.filteredList.length == 0) {
            _this.productSearch = '';
            _this.$refs.productAutocomplete.setValue('');
            _this.$awn.info('Sin resultados');
          }
        }, 200);
        if (!this.products || this.products.length == 0 || this.products == undefined) return false;
        return this.products.filter(product => {

          for (var filter of this.filters) {
            if (product[filter]) {
              var response = product[filter].toLowerCase().includes(this.productSearch.toLowerCase());
              if (response) return response;
            }
          }
          return false;

        });
      },
    },

    isPromoTab: {
      get() {
        return this.categorieNow == this.promoCategorieId
      }
    },

    // Computed property para mostrar información del día especial
    specialPaymentInfo: {
      get() {
        if (!this.chileTime) return null;
        
        const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        const dayOfWeek = this.chileTime.getDay();
        
        return {
          day: dayNames[dayOfWeek],
          date: this.chileTime.toLocaleDateString('es-CL'),
          time: this.chileTime.toLocaleTimeString('es-CL'),
          isSpecialDay: this.isSpecialPaymentDay
        };
      }
    },
  },
}
</script>

<style>
@import '../../../css/catalog-modal.css';
</style>

