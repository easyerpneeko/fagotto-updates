<template>
  <div class="catalog-wrapper">
    <!-- Modal principal de catálogo -->
    <div class="modal fade catalog-modal" id="modalCatalog" tabindex="-1" role="dialog" aria-labelledby="modalCatalog"
      aria-hidden="true" data-backdrop="false">
      <div class="modal-dialog modal-fullscreen" role="document">
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
            <!-- Indicador de descuento Exclusivo Fagotto 10% -->
            <div v-if="settingFagotto10" class="stats-badge" style="background: #dc3545; color: white;" 
                 title="Exclusivo Fagotto 10% de descuento disponible">
              <i class="fas fa-star"></i>
              <span>-10%</span>
            </div>
            <!-- Botón de Cupón -->
            <button v-if="cuponInstalled" type="button" class="btn-cupon" @click="openCupon" title="Aplicar cupón de descuento">
              <i class="fas fa-ticket-alt"></i>
              <span>Cupón</span>
            </button>
          </div>
        </div>

        <!-- Body con diseño McDonald's -->
        <div class="modal-body p-0">
          <div class="kiosko-layout">
            
            <!-- COLUMNA 1: CARRITO (lateral izquierdo) -->
            <aside class="mcd-cart-panel cart-left">

              <!-- Campos de cliente y descripción -->
              <div class="ticket-header">
                <div v-if="ticket_sell_client" class="ticket-field">
                  <label><i class="fas fa-user"></i> Cliente</label>
                  <input v-model="clientTicket" type="text" placeholder="Nombre del cliente..." />
                </div>
                <div v-if="ticket_description" class="ticket-field">
                  <label><i class="fas fa-file-text"></i> Descripción</label>
                  <input v-model="ticketDescription" type="text" placeholder="Descripción del pedido..." />
                </div>
              </div>

              <!-- Lista de productos estilo ticket -->
              <div class="ticket-items">
                <div v-if="jsonTable.items.length === 0" class="ticket-empty-state">
                  <i class="fas fa-receipt"></i>
                  <p>Agrega productos al ticket</p>
                </div>
                <div v-else v-for="(item, index) in jsonTable.items" :key="index" class="ticket-item">
                  <div class="ticket-item-header">
                    <span class="ticket-qty">{{ item.quantity }}</span>
                    <span class="ticket-name">{{ item.name }}</span>
                    <span class="ticket-price">${{ formatNumber(item.price) }}</span>
                  </div>
                  <div class="ticket-item-actions">
                    <button @click="openCommentProduct(item)" class="ticket-btn ticket-btn-comment">
                      <i class="far fa-comment"></i>
                    </button>
                    <button @click="removeProduct(item)" class="ticket-btn ticket-btn-remove">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                  <div v-if="item.comment" class="ticket-comment">
                    <i class="fas fa-quote-left"></i> {{ item.comment }}
                  </div>
                </div>
              </div>

              <!-- Total del ticket -->
              <div v-if="jsonTable.items.length > 0" class="ticket-total">
                <div class="ticket-total-row">
                  <span>TOTAL</span>
                  <span class="ticket-total-amount">${{ formatNumber(total) }}</span>
                </div>
              </div>

              <!-- Campo de comentario activo -->
              <div v-if="product_comentario" class="ticket-comment-input">
                <label><i class="fas fa-comment"></i> Comentario</label>
                <textarea 
                  v-model="product_comentario_text" 
                  @keyup.enter="addCommentProduct(productComment)"
                  placeholder="Agregar comentario..."></textarea>
              </div>
            </aside>

            <!-- COLUMNA 2: CATEGORÍAS (columna central) -->
            <aside v-if="categoriesInstalled && Allcategories && Allcategories.length > 0" class="categories-column">
              
              <!-- Buscador integrado arriba -->
              <div class="ticket-search-box">
                <i class="fas fa-search"></i>
                <input 
                  v-model="productSearch" 
                  type="text" 
                  :placeholder="searchPlaceholder"
                  @keyup.enter="search(productSearch)"
                  @focus="stopPlaceholderRotation"
                  @blur="startPlaceholderRotation"
                />
              </div>

              <!-- Banner de ayuda amarillo -->
              <div class="search-help-banner">
                <i class="fas fa-lightbulb"></i>
                ¡Ahora puedes buscar productos! 🔍
              </div>
<br>
              <div class="categories-header">
                <i class="fas fa-th-large"></i>
                <h3>Categorías</h3>
              </div>
              
              <div class="categories-list">
                <button 
                  v-for="(categorie, index) in Allcategories"
                  :key="index"
                  v-if="categorie.status === 0"
                  @click="changeCategorie(categorie.id)"
                  class="category-btn"
                  :class="{ active: categorieNow === categorie.id }">
                  <div class="category-icon">
                    <i :class="getCategoryIcon(categorie.name)"></i>
                  </div>
                  <span class="category-name">{{ categorie.name }}</span>
                </button>
                
                <button
                  v-if="gelateriaInstalled"
                  @click="openGelateria"
                  class="category-btn category-btn-special">
                  <div class="category-icon">
                    <i class="fas fa-ice-cream"></i>
                  </div>
                  <span class="category-name">Gelateria</span>
                </button>
              </div>
            </aside>

            <!-- COLUMNA 3: ÁREA DE PRODUCTOS (derecha) -->
            <main class="mcd-main">
              
              <!-- Pantalla de Bienvenida -->
              <div v-if="categorieNow === null && !productSearch" class="mcd-welcome">
                <div class="mcd-welcome-content">
                  <i class="fas fa-utensils mcd-welcome-icon"></i>
                  <h2>¡Hora del antojo!</h2>
                  <p>Selecciona una categoría del menú para comenzar</p>
                  <div class="mcd-arrow">
                    <i class="fas fa-arrow-right"></i>
                    <span>Explora el menú</span>
                  </div>
                </div>
              </div>
              
              <!-- Grid de productos estilo McDonald's -->
              <div v-else class="mcd-products-grid">
                <div v-for="(product, index) in filteredList" :key="index"
                  v-if="(products && products.length > 0 && (!cecinaInstalled || (cecinaInstalled && product.cecina)))"
                  class="mcd-product-card">
                  <card-product-orders :product="product" @clickEmit="AddProduct" />
                </div>
                <div v-if="!products || products.length === 0" class="mcd-no-results">
                  <i class="fas fa-search-minus"></i>
                  <h3>Sin resultados</h3>
                  <p>No se encontraron productos</p>
                </div>
              </div>
            </main>

          </div>
        </div>

        <div class="modal-footer">
          <!-- Botón único de Pagar -->
          <button 
            v-if="jsonTable.items.length > 0"
            type="button" 
            class="btn-pagar-main"
            @click="openPaymentModal">
            <i class="fas fa-credit-card"></i>
            <span>Pagar - ${{ formatNumber(total) }}</span>
          </button>
          
          <!-- Guardado para referencia: métodos ocultos -->
          <template v-if="false">
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
                Ticket + Rappi (Boleta SII)
              </button>
              <button v-if="settingJunaeb" @click="viewTicket('Junaeb')" type="button" class="btn bg-primario text-white">
                Ticket + Junaeb
              </button>
              <button v-if="settingUber" @click="viewTicket('uber')" type="button" class="btn bg-primario text-white">
                Ticket + Uber (Boleta SII)
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
                Ticket + Pedidos Ya (Boleta SII)
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
              
              <!-- Botón Mercado Pago -->
              <button v-if="jsonTable.items.length > 0" @click="procesarPagoMercadoPago" type="button"
                class="btn text-white" style="background: linear-gradient(135deg, #009ee3 0%, #0078a8 100%); font-weight: bold; box-shadow: 0 4px 15px rgba(0, 158, 227, 0.4);">
                <i class="fas fa-credit-card me-2"></i>
                Pagar con Mercado Pago
                <small class="d-block" style="font-size: 0.75em;">
                  Total: ${{ formatNumber(total) }}
                </small>
              </button>
              
              <!-- Botón exclusivo Fagotto 10% de descuento -->
              <button v-if="settingFagotto10" @click="viewTicket('fagotto_10')" type="button"
                class="btn text-white" style="background-color: #dc3545; font-weight: bold;">
                <i class="fas fa-star me-2"></i>
                Exclusivo Fagotto 10% al total
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
        </div>

      </div>
    </div>
  </div>
    
    <!-- Modal de Métodos de Pago -->
    <div class="modal fade" id="modalPaymentMethods" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="false">
      <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 600px;">
        <div class="modal-content payment-methods-modal">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-wallet"></i> Métodos de Pago</h5>
            <button type="button" class="close" @click="closePaymentModal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="payment-methods-grid">
              <!-- Efectivo -->
              <button v-if="ticket_sell && settingBoletaLocal" class="payment-method-btn" @click="selectPaymentMethod('boleta_local')">
                <i class="fas fa-money-bill-wave"></i>
                <span>Efectivo</span>
              </button>
              
              <!-- Boleta SII -->
              <button v-if="ticket_sell && settingBoleta" class="payment-method-btn" @click="selectPaymentMethod('boleta')">
                <i class="fas fa-receipt"></i>
                <span>Boleta SII</span>
              </button>
              
              <!-- Débito -->
              <button v-if="settingDebito" class="payment-method-btn" @click="selectPaymentMethod('debito')">
                <i class="fas fa-credit-card"></i>
                <span>Débito</span>
              </button>
              
              <!-- Transferencia -->
              <button v-if="settingTransferencia" class="payment-method-btn" @click="selectPaymentMethod('transferencia')">
                <i class="fas fa-exchange-alt"></i>
                <span>Transferencia</span>
              </button>
              
              <!-- Rappi -->
              <button v-if="settingRappi" class="payment-method-btn" @click="selectPaymentMethod('rappi')">
                <i class="fas fa-motorcycle"></i>
                <span>Rappi</span>
              </button>
              
              <!-- Uber Eats -->
              <button v-if="settingUber" class="payment-method-btn" @click="selectPaymentMethod('uber')">
                <i class="fas fa-car"></i>
                <span>Uber Eats</span>
              </button>
              
              <!-- Pedidos Ya -->
              <button v-if="settingPedidosYa" class="payment-method-btn" @click="selectPaymentMethod('pedidos_ya')">
                <i class="fas fa-pizza-slice"></i>
                <span>Pedidos Ya</span>
              </button>
              
              <!-- Mercado Pago -->
              <button class="payment-method-btn" @click="procesarPagoMercadoPago">
                <i class="fas fa-mobile-alt"></i>
                <span>Mercado Pago</span>
              </button>
              
              <!-- Banco de Chile 20% -->
              <button v-if="settingBancoChile20 && isSpecialPaymentDay" class="payment-method-btn special" @click="selectPaymentMethod('banco_chile_20')">
                <i class="fas fa-percentage"></i>
                <span>Banco Chile -20%</span>
              </button>
              
              <!-- Fagotto 10% -->
              <button v-if="settingFagotto10" class="payment-method-btn special" @click="selectPaymentMethod('fagotto_10')">
                <i class="fas fa-star"></i>
                <span>Fagotto -10%</span>
              </button>
              
              <!-- Junaeb -->
              <button v-if="settingJunaeb" class="payment-method-btn" @click="selectPaymentMethod('Junaeb')">
                <i class="fas fa-graduation-cap"></i>
                <span>Junaeb</span>
              </button>
              
              <!-- Sodexo -->
              <button v-if="settingSodexo" class="payment-method-btn" @click="selectPaymentMethod('sodexo')">
                <i class="fas fa-utensils"></i>
                <span>Sodexo</span>
              </button>
              
              <!-- Amipass -->
              <button v-if="settingAmipass" class="payment-method-btn" @click="selectPaymentMethod('amipass')">
                <i class="fas fa-id-card"></i>
                <span>Amipass</span>
              </button>
              
              <!-- Edenred -->
              <button v-if="settingEdenred" class="payment-method-btn" @click="selectPaymentMethod('edenred')">
                <i class="fas fa-ticket-alt"></i>
                <span>Edenred</span>
              </button>
              
              <!-- Pluxee -->
              <button v-if="settingPluxee" class="payment-method-btn" @click="selectPaymentMethod('pluxee')">
                <i class="fas fa-gift"></i>
                <span>Pluxee</span>
              </button>
              
              <!-- Multicaja -->
              <button v-if="settingMulticaja" class="payment-method-btn" @click="selectPaymentMethod('multicaja')">
                <i class="fas fa-wallet"></i>
                <span>Multicaja</span>
              </button>
              
              <!-- Cheque -->
              <button v-if="settingCheque" class="payment-method-btn" @click="selectPaymentMethod('cheque')">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Cheque</span>
              </button>
              
              <!-- Crédito -->
              <button v-if="settingCredito" class="payment-method-btn" @click="selectPaymentMethod('credito')">
                <i class="fas fa-credit-card"></i>
                <span>Crédito</span>
              </button>
              
              <!-- Nota de Crédito -->
              <button v-if="settingNotaCredito" class="payment-method-btn" @click="selectPaymentMethod('nota_de_credito')">
                <i class="fas fa-file-alt"></i>
                <span>Nota Crédito</span>
              </button>
              
              <!-- Convenio -->
              <button v-if="settingConvenio" class="payment-method-btn" @click="selectPaymentMethod('convenio_empresa')">
                <i class="fas fa-handshake"></i>
                <span>Convenio</span>
              </button>
              
              <!-- Factura -->
              <button v-if="settingFactura" class="payment-method-btn" @click="selectPaymentMethod('factura')">
                <i class="fas fa-file-invoice"></i>
                <span>Factura</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modales adicionales -->
    <modalVerify :propVerify="propVerify" @refreshData="refreshData" />
    <ticket :typeCreateTicket="typeCreateTicket" v-model="ticketData" @changeValue="changeValue"
      @closeModal="closeModal" />
    <venta-copas :products="products" @addCopa="handleAddCopa" />
    <modal-cupon @abrir-seleccion="abrirModalSeleccion" />
    <modal-seleccion-cupon ref="modalSeleccionCupon" @producto-seleccionado="agregarProductoCupon" />
  </div>
</template>

<script>
// Componentes
import customTable from '@/components/tables/table.vue';
import cardProductOrders from '@/components/cards/card_product_orders.vue';
import ticket from '@/components/modals/cafeteria/createTicket.vue';
import modalVerify from '@/components/modals/verifyDelete.vue';
import ventaCopas from '@/components/modals/cafeteria/ventaCopas.vue';
import modalCupon from '@/components/modals/cafeteria/modalCupon.vue';
import modalSeleccionCupon from '@/components/modals/cafeteria/modalSeleccionCupon.vue';

// Helpers y plugins
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
import Loader from '@/helpers/Loader';
import BaseUrl from '@/helpers/baseUrl.js';
import Connection from '@/helpers/Connection.js';

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
      // Placeholders rotativos para el buscador
      searchPlaceholders: [
        'Buscar producto...',
        'Prueba: Red Bull 🦅',
        'La bebida de Jimmy 😎',
        'Buscar por nombre...',
        'Buscar por código...'
      ],
      currentPlaceholderIndex: 0,
      placeholderInterval: null,
      searchPlaceholder: 'Buscar producto...',
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
    ventaCopas,
    modalCupon,
    modalSeleccionCupon,
  },
  mounted() {
    //HavePermission
    this.refreshData(false, true);
    this.product_comentario = false;
    
    // Verificar la hora de Chile para el método de pago especial
    this.checkChileTime();
    
    // Iniciar rotación de placeholders
    this.startPlaceholderRotation();
    
    // Verificar la hora cada 5 minutos por si cambia el día
    setInterval(() => {
      this.checkChileTime();
    }, 300000); // 5 minutos = 300,000 ms
  },
  methods: {
    // Abrir modal de cupón
    openCupon() {
      $('#modalCupon').modal('show');
    },
    
    // Abrir modal de selección después de validar cupón
    abrirModalSeleccion(cuponData) {
      console.log('🎫 Cupón válido, abriendo modal de selección:', cuponData);
      this.$refs.modalSeleccionCupon.openModal(cuponData);
    },
    
    // Agregar producto con cupón al carrito
    agregarProductoCupon(producto) {
      console.log('✅ Producto con cupón:', producto);
      this.quantityAdd(producto);
    },

    // Abrir modal de métodos de pago
    openPaymentModal() {
      if (this.productoSend.length == 0) {
        this.$awn.alert("Es necesario agregar algún producto");
        return false;
      }
      $('#modalPaymentMethods').modal('show');
    },

    // Cerrar modal de métodos de pago
    closePaymentModal() {
      $('#modalPaymentMethods').modal('hide');
    },

    // Seleccionar método de pago
    selectPaymentMethod(method) {
      $('#modalPaymentMethods').modal('hide');
      this.viewTicket(method);
    },

    // Métodos para placeholder rotativo
    startPlaceholderRotation() {
      this.placeholderInterval = setInterval(() => {
        this.currentPlaceholderIndex = (this.currentPlaceholderIndex + 1) % this.searchPlaceholders.length;
        this.searchPlaceholder = this.searchPlaceholders[this.currentPlaceholderIndex];
      }, 3000); // Cambia cada 3 segundos
    },

    stopPlaceholderRotation() {
      if (this.placeholderInterval) {
        clearInterval(this.placeholderInterval);
        this.placeholderInterval = null;
      }
    },

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
        
        // NO actualizar this.total aquí, se hará después de agregar la información especial
      }
      
      // Si es Exclusivo Fagotto 10% de descuento
      if (val === 'fagotto_10') {
        // Calcular el 10% de descuento
        const originalTotal = this.total;
        const tenPercentDiscount = originalTotal * 0.10;
        const totalWithDiscount = originalTotal - tenPercentDiscount;
        
        // Confirmar con el usuario
        const confirmMessage = `¿Confirmar venta con Exclusivo Fagotto 10% de descuento?\n\nTotal original: $${this.formatNumber(originalTotal)}\n10% descuento: -$${this.formatNumber(tenPercentDiscount)}\nTotal final: $${this.formatNumber(totalWithDiscount)}`;
        
        if (!confirm(confirmMessage)) {
          return false;
        }
        
        // NO actualizar this.total aquí, se hará después de agregar la información especial
      }
      
      // Si es Uber, confirmar que se genere boleta SII
      if (val === 'uber') {
        const confirmMessage = `¿Confirmar venta con Uber Eats?\n\nSe generará una boleta del SII\nTotal: $${this.formatNumber(this.total)}`;
        
        if (!confirm(confirmMessage)) {
          return false;
        }
      }

      // Si es Pedidos Ya, confirmar que se genere boleta SII
      if (val === 'pedidos_ya') {
        const confirmMessage = `¿Confirmar venta con Pedidos Ya?\n\nSe generará una boleta del SII\nTotal: $${this.formatNumber(this.total)}`;
        
        if (!confirm(confirmMessage)) {
          return false;
        }
      }

      // Si es Rappi, confirmar que se genere boleta SII
      if (val === 'rappi') {
        const confirmMessage = `¿Confirmar venta con Rappi?\n\nSe generará una boleta del SII\nTotal: $${this.formatNumber(this.total)}`;
        
        if (!confirm(confirmMessage)) {
          return false;
        }
      }

      // Datos basicos
      this.ticketData = {
        products: this.productoSend,
        total: this.total,
        gananciaTotal: this.gananciaTotal
      }
      
      // Si es el método de pago especial con 20%, agregar información extra para reportes
      if (val === 'banco_chile_20') {
        const originalTotal = this.total;
        const twentyPercentDiscount = originalTotal * 0.20;
        const totalWithDiscount = originalTotal - twentyPercentDiscount;
        
        this.ticketData.specialPayment = {
          paymentType: 'banco_chile_20',
          method: 'Banco De Chile 20%',
          description: 'Pago especial con 20% de descuento disponible solo lunes y martes',
          originalTotal: originalTotal,
          discountAmount: twentyPercentDiscount,
          finalTotal: totalWithDiscount,
          discountPercentage: 20,
          date: this.chileTime ? this.chileTime.toISOString() : new Date().toISOString(),
          dayOfWeek: this.chileTime ? this.chileTime.getDay() : new Date().getDay(),
          enabled: this.isSpecialPaymentDay
        };
        
        // Actualizar el total con el descuento para el procesamiento
        this.total = totalWithDiscount;
        this.ticketData.total = totalWithDiscount;
      }
      
      // Si es Exclusivo Fagotto 10% de descuento
      if (val === 'fagotto_10') {
        const originalTotal = this.total;
        const tenPercentDiscount = originalTotal * 0.10;
        const totalWithDiscount = originalTotal - tenPercentDiscount;
        
        this.ticketData.specialPayment = {
          paymentType: 'fagotto_10',
          method: 'Exclusivo Fagotto 10%',
          description: 'Descuento exclusivo de Fagotto del 10% aplicado al total',
          originalTotal: originalTotal,
          discountAmount: tenPercentDiscount,
          finalTotal: totalWithDiscount,
          discountPercentage: 10,
          date: new Date().toISOString(),
          enabled: true
        };
        
        // Actualizar el total con el descuento para el procesamiento
        this.total = totalWithDiscount;
        this.ticketData.total = totalWithDiscount;
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

      // Si es Uber, configurar para generar boleta SII pero con identificación especial
      if (val === 'uber') {
        this.typeCreateTicket = 'boleta';
        // ✅ IMPORTANTE: Agregar información específica de Uber para diferenciarlo en reportes
        this.ticketData.paymentMethod = 'uber_eats';
        this.ticketData.paymentDescription = 'Uber Eats - Boleta SII';
        this.ticketData.uberEatsOrder = true;
        this.ticketData.specialPaymentType = 'uber_eats';
        
        console.log('🚀 UBER EATS - Datos configurados:', {
          typeCreateTicket: this.typeCreateTicket,
          paymentMethod: this.ticketData.paymentMethod,
          paymentDescription: this.ticketData.paymentDescription,
          uberEatsOrder: this.ticketData.uberEatsOrder
        });
      } else if (val === 'pedidos_ya') {
        // 🍕 PEDIDOS YA: Generar boleta SII con identificación especial
        this.typeCreateTicket = 'boleta';
        this.ticketData.paymentMethod = 'pedidos_ya';
        this.ticketData.paymentDescription = 'Pedidos Ya - Boleta SII';
        this.ticketData.pedidosYaOrder = true;
        this.ticketData.specialPaymentType = 'pedidos_ya';
        
        console.log('🍕 PEDIDOS YA - Datos configurados:', {
          typeCreateTicket: this.typeCreateTicket,
          paymentMethod: this.ticketData.paymentMethod,
          paymentDescription: this.ticketData.paymentDescription,
          pedidosYaOrder: this.ticketData.pedidosYaOrder
        });
      } else if (val === 'rappi') {
        // 🛵 RAPPI: Generar boleta SII con identificación especial
        this.typeCreateTicket = 'boleta';
        this.ticketData.paymentMethod = 'rappi';
        this.ticketData.paymentDescription = 'Rappi - Boleta SII';
        this.ticketData.rappiOrder = true;
        this.ticketData.specialPaymentType = 'rappi';
        
        console.log('🛵 RAPPI - Datos configurados:', {
          typeCreateTicket: this.typeCreateTicket,
          paymentMethod: this.ticketData.paymentMethod,
          paymentDescription: this.ticketData.paymentDescription,
          rappiOrder: this.ticketData.rappiOrder
        });
      } else if (val === 'fagotto_10') {
        // 🎯 FAGOTTO 10%: Generar boleta SII con descuento
        this.typeCreateTicket = 'boleta';
        console.log('🎯 FAGOTTO 10% - Configurado para generar boleta SII:', {
          typeCreateTicket: this.typeCreateTicket,
          specialPayment: this.ticketData.specialPayment
        });
      } else {
        if (val) this.typeCreateTicket = val;
      }
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

    async procesarPagoMercadoPago() {
      if (!this.total || this.total <= 0) {
        this.$awn.alert("Agrega productos antes de procesar el pago");
        return;
      }

      try {
        // Mostrar loading
        this.$awn.info("Procesando pago con Mercado Pago...", {
          durations: { info: 0 }
        });

        // Preparar datos del pedido
        const orderData = {
          monto: Math.round(this.total), // Monto total en pesos
          productos: this.jsonTable.items.map(item => ({
            nombre: item.name,
            cantidad: item.quantity,
            precio: item.price,
            subtotal: item.subtotal
          })),
          cliente: this.clientTicket || 'Cliente Totem',
          descripcion: this.ticketDescription || 'Pedido desde Totem'
        };

        console.log('📤 Enviando pago a Mercado Pago:', orderData);

        // Enviar pago a Mercado Pago - usando subdominio dedicado con API JSON
        const apiUrl = 'https://apimercado.posfagotto.cl/api-pago.php';
        console.log('🌐 URL del API:', apiUrl);
        
        const response = await fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams(orderData)
        });

        const result = await response.json();
        console.log('📥 Respuesta de Mercado Pago:', result);

        if (result.success && result.orderId) {
          // Pago enviado exitosamente al terminal
          this.$awn.success(
            `¡Pago enviado! Monto: $${this.formatNumber(this.total)} | Order ID: ${result.orderId}. Por favor, completa el pago en el terminal Point Smart.`,
            {
              durations: { success: 8000 }
            }
          );

          // Opcional: Limpiar carrito después del pago exitoso
          // this.productoSend = [];
          // this.jsonTable.items = [];
          // this.total = 0;
          
        } else {
          throw new Error(result.message || 'Error al procesar el pago');
        }

      } catch (error) {
        console.error('❌ Error al procesar pago:', error);
        this.$awn.alert(error.message || 'No se pudo conectar con Mercado Pago. Verifica la conexión.');
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
          // Inicialmente no mostrar productos (pantalla de bienvenida)
          this.products = [];
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
        // Cuando deselecciona, volver a la pantalla de bienvenida
        this.products = [];
      }

      // Lógica de promo_active basada en el parámetro promo O si estamos en categoría promociones
      if(promo || this.categorieNow === this.promoCategorieId){
        this.promo_active = true;
      } else {
        this.promo_active = false;
      }
    },

    // Abrir modal de Gelateria
    openGelateria() {
      $('#modalVentaCopas').modal('show');
    },
    
    // Manejar orden confirmada del wizard de promociones
    // Manejar adición de copa desde modal ventaCopas
    handleAddCopa(copaData) {
      console.log('🍦 Datos recibidos de ventaCopas:', copaData);
      
      // Determinar si es un producto de categoría o copa personalizada
      const isProductoCatalogo = copaData.id && copaData.type === 'copa_producto';
      
      if (isProductoCatalogo) {
        // Es un producto del catálogo (categoria 55, 56, 57)
        this.quantityAdd({
          id: copaData.id,
          name: copaData.name,
          price: parseFloat(copaData.price),
          promo_price: copaData.promo_price ? parseFloat(copaData.promo_price) : null,
          quantity: 1,
          prices: copaData.prices || [],
          cecina: copaData.cecina || false,
          ganancia: copaData.ganancia || 0,
          category: copaData.category || null
        });
      } else {
        // Es una copa personalizada (sistema antiguo de sabores)
        this.quantityAdd({
          id: `copa_${Date.now()}`,
          name: copaData.name,
          price: parseFloat(copaData.price),
          promo_price: null,
          quantity: 1,
          prices: [{ price: parseFloat(copaData.price) }],
          cecina: false,
          is_copa: true,
          copa_details: {
            size: copaData.size,
            flavors: copaData.flavors
          }
        });
      }

      console.log('✅ Producto agregado al carrito');
      
      // NO cerrar el modal - dejar que ventaCopas.vue lo maneje
      // $('#modalVentaCopas').modal('hide');
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
      if(this.promo_active && data.promo_price && data.promo_price > 0){
        data.subtotal = parseFloat(data.quantity) * parseFloat(data.promo_price);
        data.price = parseFloat(data.promo_price);
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
      // Ejecuto #calculatePlus ✅ (skip para productos con cupón)
      if (!data.has_cupon) {
        this.calculatePlus(i, data, (this.priceUnitaryInstalled) ? true : false);
      } else {
        // Para productos con cupón, recalcular total directamente
        this.calculateTotal();
      }
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
            if(this.promo_active && data.promo_price && data.promo_price > 0){
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
      
      // Verificar si tiene precios especiales válidos
      if (data.prices && Array.isArray(data.prices) && data.prices.length > 0) {
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
        // Si no se encontró precio en el array, usar el precio normal
        if (!price || price === 0) {
          price = data.price;
        }
      } else {
        // No tiene precios especiales, usar precio normal
        price = data.price;
      }

      var product = {
        id: data.id,
        name: data.name,
        price: price,
        promo_price: data.promo_price,
        promo_active: data.promo_active,
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
      // Obtengo el producto (si existe) y defino un precio de entrada a partir del item agregado
      var productFound = this.products.find(element => element.id == data.id);
      var productActual = productFound ? Object.assign({}, productFound) : null;
      console.log(this.promo_active, 'productFound:', !!productFound);

      // Solo usar promo_price si this.promo_active es true
      var precioDeEntrada;
      if(this.promo_active && this.productoSend[index].promo_price != null && this.productoSend[index].promo_price !== '') {
        precioDeEntrada = parseFloat(this.productoSend[index].promo_price);
      } else {
        precioDeEntrada = parseFloat(this.productoSend[index].price || 0);
      }

      var precioVarianteDiferenteDeUnitario = false;

      if (productActual) {
        if (this.promo_active) {
          if (unitary_price) productActual.price = this.productoSend[index].promo_price;
        } else {
          if (unitary_price) productActual.price = this.productoSend[index].price;
        }
      }
      

      // Obtenemos la CANTIDAD
      var cantidad = parseFloat(data.quantity);

      if (this.productoSend[index].LastVariantPrice)
        if (parseFloat(precioDeEntrada) !== parseFloat(this.productoSend[index].LastVariantPrice)) {
          precioVarianteDiferenteDeUnitario = true;
        }

      // Si tiene precios variantes (asegurarse que productActual exista)
      if (productActual && productActual.prices && productActual.prices.length) {

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
      } else { // No recorro los precios variantes: usar precio de entrada (fallback al item si no existe el producto)
        var precioFallback = precioDeEntrada;
        var gananciaFallback = (productActual && productActual.ganancia) ? productActual.ganancia : 0;
        this.addGainSubTotalVariantPrice(index, data.cecina, precioFallback, cantidad, gananciaFallback);
        this.productoSend[index].price = precioFallback;
        // Registrar LastVariantPrice para consistencia
        this.productoSend[index].LastVariantPrice = precioFallback;
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

    settingFagotto10: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.fagotto_10');
      }
    },
    
    settingHalloween20: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.halloween_20');
      }
    },

    settingVenderSinStock: {
      get() {
        return ConfigHelper.ConfStr('modulos.ventas.ajustes.permitir_venta_sin_stock');
      }
    },

    cuponInstalled: { 
      get() { 
        return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.cupon'); 
      } 
    },

    gelateriaInstalled: { 
      get() { 
        return ConfigHelper.ConfStr('modulos.cafeteria.ajustes.gelateria'); 
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
  
  beforeDestroy() {
    // Limpiar intervalo de placeholders
    this.stopPlaceholderRotation();
  },
}
</script>

<style>
@import '../../../css/catalog-modal.css';

/* ===== ESTILOS MCDONALD'S KIOSKO ===== */
.kiosko-layout {
  display: grid;
  grid-template-columns: 2fr 1fr 2fr;
  grid-template-rows: 1fr;
  grid-column-gap: 0px;
  grid-row-gap: 0px;
  height: 80vh;
  overflow: hidden;
}

/* COLUMNA 1: CARRITO - 40% del ancho */
.mcd-cart-panel {
  grid-area: 1 / 1 / 2 / 2;
  background: #fff;
  border-right: none;
  overflow-y: auto;
  box-shadow: none;
  display: flex;
  flex-direction: column;
}

/* Carrito vacío ocupa menos espacio (columna 1) */
.mcd-cart-panel.empty-cart {
  grid-area: 1 / 1 / 2 / 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

/* COLUMNA 2: CATEGORÍAS - 20% del ancho */
.categories-column {
  grid-area: 1 / 2 / 2 / 3;
  background: #f8f9fa;
  border-left: 1px solid #e9ecef;
  border-right: 1px solid #e9ecef;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.categories-header {
  padding: 1rem;
  background: white;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 0.5rem;
}

.categories-header i {
  font-size: 1.25rem;
  color: #495057;
}

.categories-header h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: #2c2c2c;
}

.categories-list {
  flex: 1;
  padding: 0;
  overflow-y: auto;
  background: white;
}

/* Buscador en el carrito */
.ticket-search-box {
  padding: 1rem;
  position: relative;
  border-bottom: none;
  background: white;
}

.ticket-search-box i {
  position: absolute;
  left: 1.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  font-size: 1rem;
}

.ticket-search-box input {
  width: 100%;
  padding: 0.75rem 0.75rem 0.75rem 2.5rem;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s;
}

.ticket-search-box input:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
  background: white;
}

/* Banner de ayuda amarillo */
.search-help-banner {
  padding: 14px 15px;
  background: #ffc107;
  color: #000;
  font-weight: 500;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 10px;
  border-bottom: 2px solid #e0a800;
  margin-bottom: 0;
}

.search-help-banner i {
  font-size: 16px;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}

.category-btn {
  width: 100%;
  padding: 0.875rem 1rem;
  margin: 0;
  background: white;
  border: none;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.category-btn:hover {
  background: #f8f9fa;
}

.category-btn.active {
  background: #e7f3ff;
  border-left: 4px solid #007bff;
  padding-left: calc(1rem - 4px);
}

.category-btn.active::after {
  content: '';
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: #007bff;
}

.category-icon {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  color: #495057;
  flex-shrink: 0;
}

.category-btn.active .category-icon {
  color: #007bff;
}

.category-name {
  flex: 1;
  font-size: 0.95rem;
  font-weight: 500;
  color: #2c2c2c;
  text-align: left;
}

.category-btn.active .category-name {
  font-weight: 600;
  color: #007bff;
}

.category-btn::after {
  content: '\276F';
  font-size: 1rem;
  color: #adb5bd;
  margin-left: auto;
}

.category-btn.active::after {
  color: #007bff;
}

.category-btn-special {
  background: #fff3cd;
}

.category-btn-special:hover {
  background: #ffecb5;
}

.category-btn-special .category-icon {
  color: #856404;
}

.category-btn-special .category-name {
  color: #856404;
  font-weight: 600;
}

/* COLUMNA 3: ÁREA DE PRODUCTOS - 40% del ancho */
.mcd-main {
  grid-area: 1 / 3 / 2 / 4;
  background: linear-gradient(145deg, #f8f9fa 0%, #e9ecef 100%);
  overflow-y: auto;
  padding: 2rem;
}

.ticket-header {
  padding: 1rem;
  border-bottom: 2px dashed #ddd;
}

.ticket-field {
  margin-bottom: 0.75rem;
}

.ticket-field:last-child {
  margin-bottom: 0;
}

.ticket-field label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.25rem;
}

.ticket-field label i {
  margin-right: 0.25rem;
  color: #6c757d;
}

.ticket-field input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #dee2e6;
  border-radius: 5px;
  font-size: 0.9rem;
}

.ticket-field input:focus {
  outline: none;
  border-color: #ffbc0d;
  box-shadow: 0 0 0 2px rgba(255, 188, 13, 0.1);
}

.ticket-items {
  flex: 1;
  padding: 1rem;
  overflow-y: auto;
}

.ticket-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  color: #adb5bd;
  text-align: center;
}

.ticket-empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.ticket-empty-state p {
  font-size: 0.95rem;
  margin: 0;
}

.ticket-item {
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #e9ecef;
}

.ticket-item:last-child {
  border-bottom: none;
}

.ticket-item-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  font-family: 'Courier New', monospace;
}

.ticket-qty {
  background: #dc3545;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 5px;
  font-weight: 700;
  font-size: 0.9rem;
  min-width: 30px;
  text-align: center;
}

.ticket-name {
  flex: 1;
  font-weight: 600;
  font-size: 0.95rem;
  color: #2c2c2c;
}

.ticket-price {
  font-weight: 700;
  font-size: 1rem;
  color: #dc3545;
}

.ticket-item-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.ticket-btn {
  padding: 0.4rem 0.75rem;
  border: 1px solid #dee2e6;
  border-radius: 5px;
  background: #f8f9fa;
  cursor: pointer;
  transition: all 0.2s;
}

.ticket-btn:hover {
  background: #e9ecef;
}

.ticket-btn-comment {
  color: #007bff;
}

.ticket-btn-remove {
  color: #dc3545;
}

.ticket-comment {
  margin-top: 0.5rem;
  padding: 0.5rem;
  background: #f8f9fa;
  border-left: 3px solid #6c757d;
  font-size: 0.85rem;
  font-style: italic;
  color: #495057;
}

.ticket-comment i {
  font-size: 0.7rem;
  margin-right: 0.25rem;
  color: #6c757d;
}

.ticket-total {
  padding: 1rem;
  border-top: 3px double #2c2c2c;
  background: #f8f9fa;
}

.ticket-total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-family: 'Courier New', monospace;
  font-size: 1.25rem;
  font-weight: 700;
}

.ticket-total-amount {
  color: #dc3545;
  font-size: 1.5rem;
}

.ticket-comment-input {
  padding: 1rem;
  border-top: 2px dashed #ddd;
  background: #fffbf0;
}

.ticket-comment-input label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.5rem;
}

.ticket-comment-input textarea {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #dee2e6;
  border-radius: 5px;
  font-size: 0.9rem;
  resize: vertical;
  min-height: 60px;
}

.ticket-comment-input textarea:focus {
  outline: none;
  border-color: #ffbc0d;
  box-shadow: 0 0 0 2px rgba(255, 188, 13, 0.1);
}

/* ===== ESTILOS RESTANTES ===== */

.mcd-welcome {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.mcd-welcome-content {
  text-align: center;
  max-width: 500px;
}

.mcd-welcome-icon {
  font-size: 6rem;
  color: #ffbc0d;
  margin-bottom: 2rem;
  animation: bounce 2s infinite;
}

.mcd-welcome-content h2 {
  font-size: 2.5rem;
  font-weight: 800;
  color: #2c2c2c;
  margin-bottom: 1rem;
}

.mcd-welcome-content p {
  font-size: 1.2rem;
  color: #666;
  margin-bottom: 2rem;
}

.mcd-arrow {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: #ffbc0d;
  font-weight: 600;
  animation: slideRight 1.5s infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}

@keyframes slideRight {
  0%, 100% { transform: translateX(0); }
  50% { transform: translateX(10px); }
}

.mcd-products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.mcd-product-card {
  transition: transform 0.3s;
}

.mcd-product-card:hover {
  transform: translateY(-8px);
}

.mcd-no-results {
  text-align: center;
  padding: 4rem 2rem;
  color: #999;
}

.mcd-no-results i {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.3;
}

.mcd-no-results h3 {
  margin-bottom: 0.5rem;
}

/* Estado vacío del carrito */
.empty-cart-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  text-align: center;
}

.empty-cart-animation {
  position: relative;
  margin-bottom: 2rem;
}

.empty-cart-icon {
  font-size: 5rem;
  color: #dee2e6;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-15px); }
}

.floating-dots {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.dot {
  width: 8px;
  height: 8px;
  background: #ffbc0d;
  border-radius: 50%;
  position: absolute;
  animation: floating 2s ease-in-out infinite;
}

.dot-1 {
  top: -30px;
  left: -20px;
  animation-delay: 0s;
}

.dot-2 {
  top: -30px;
  right: -20px;
  animation-delay: 0.3s;
}

.dot-3 {
  bottom: -30px;
  left: 0;
  animation-delay: 0.6s;
}

@keyframes floating {
  0%, 100% {
    transform: translateY(0) scale(1);
    opacity: 0.7;
  }
  50% {
    transform: translateY(-10px) scale(1.2);
    opacity: 1;
  }
}

.empty-cart-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #495057;
  margin-bottom: 0.5rem;
}

.empty-cart-description {
  font-size: 1rem;
  color: #6c757d;
  margin-bottom: 1.5rem;
}

.empty-cart-cta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #ffbc0d;
  font-weight: 600;
  animation: pulseArrow 1.5s infinite;
}

@keyframes pulseArrow {
  0%, 100% {
    transform: translateX(0);
    opacity: 0.7;
  }
  50% {
    transform: translateX(10px);
    opacity: 1;
  }
}

/* Scrollbar personalizado */
.mcd-main::-webkit-scrollbar,
.mcd-cart-panel::-webkit-scrollbar {
  width: 8px;
}

.mcd-main::-webkit-scrollbar-thumb,
.mcd-cart-panel::-webkit-scrollbar-thumb {
  background: #ffbc0d;
  border-radius: 4px;
}

/* ===== BOTÓN PAGAR PRINCIPAL ===== */
.btn-pagar-main {
  width: 100%;
  padding: 1.5rem;
  background: linear-gradient(135deg, #ffbc0d 0%, #ff9500 100%);
  border: none;
  border-radius: 12px;
  color: #2c2c2c;
  font-size: 1.5rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 20px rgba(255, 188, 13, 0.4);
}

.btn-pagar-main:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 30px rgba(255, 188, 13, 0.6);
  background: linear-gradient(135deg, #ff9500 0%, #ffbc0d 100%);
}

.btn-pagar-main:active {
  transform: translateY(0);
}

.btn-pagar-main i {
  font-size: 2rem;
}

/* ===== MODAL DE MÉTODOS DE PAGO ===== */
.payment-methods-modal .modal-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-bottom: none;
  padding: 1.5rem;
}

.payment-methods-modal .modal-title {
  font-size: 1.5rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.payment-methods-modal .modal-body {
  padding: 2rem;
}

.payment-methods-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.payment-method-btn {
  padding: 1.5rem;
  background: white;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 1rem;
  font-weight: 600;
  color: #2c2c2c;
}

.payment-method-btn:hover {
  background: #f8f9fa;
  border-color: #ffbc0d;
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.payment-method-btn i {
  font-size: 2.5rem;
  color: #667eea;
}

.payment-method-btn.special {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-color: #764ba2;
  color: white;
}

.payment-method-btn.special i {
  color: white;
}

.payment-method-btn.special:hover {
  background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
  transform: translateY(-6px);
  box-shadow: 0 10px 30px rgba(118, 75, 162, 0.4);
}

/* Fullscreen modal */
.modal-fullscreen {
  max-width: 100%;
  margin: 0;
}

.modal-fullscreen .modal-content {
  height: 100vh;
  border: none;
  border-radius: 0;
}

.modal-fullscreen .kiosko-layout {
  height: calc(100vh - 180px);
}
</style>

