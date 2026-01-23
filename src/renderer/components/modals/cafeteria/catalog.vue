@ -1,1521 +1,1598 @@
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
            <!-- Indicador de descuento Exclusivo Fagotto 10% -->
            <div v-if="settingFagotto10" class="stats-badge" style="background: #dc3545; color: white;" 
                 title="Exclusivo Fagotto 10% de descuento disponible">
              <i class="fas fa-star"></i>
              <span>-10%</span>
            </div>
            <!-- Indicador de descuento Turbus 10% -->
            <div v-if="settingTurbus10" class="stats-badge" style="background: rgb(0, 51, 153); color: white;" 
                 title="Turbus 10% de descuento disponible">
              <i class="fas fa-bus"></i>
              <span>-10%</span>
            </div>
            <button type="button" class="btn-close" @click="closeModal(false)" aria-label="Close">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>

        <!-- Body con nuevo layout -->
        <div class="modal-body">
          <div class="catalog-container-new">
            
            <!-- Panel izquierdo del carrito (div1) -->
            <div class="cart-panel-left div1">
              <!-- Campo de descripción si está habilitado -->
              <div v-if="ticket_description" class="description-section mb-3">
                <label class="form-label">
                  <i class="fas fa-file-text me-2"></i>
                  Descripción
                </label>
                <input v-model="ticketDescription" class="form-control modern-input"
                  type="text" placeholder="Descripción del pedido..." />
              </div>

              <!-- Lista de productos en el carrito (siempre visible) -->
              <div class="cart-items-container">
                <div v-if="jsonTable.items.length > 0" class="cart-items-list">
                  <div v-for="(item, index) in jsonTable.items" :key="index" class="cart-item-card">
                    <div class="cart-item-header">
                      <span class="cart-item-badge">{{ item.quantity }}</span>
                      <span class="cart-item-name">{{ item.name }}</span>
                      <span class="cart-item-price">${{ formatNumber(item.price * item.quantity) }}</span>
                    </div>
                    <div class="cart-item-actions">
                      <button class="btn-comment-cart" @click="openCommentProduct(item)" 
                        :title="'Agregar comentario'">
                        <i class="far fa-comment"></i>
                      </button>
                      <button class="btn-remove-cart" @click="removeProduct(item)" 
                        :title="'Eliminar producto'">
                        <i class="fas fa-times"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Área central de búsqueda y categorías (div2) -->
            <div class="search-catalog-area div2">
              
              <!-- Buscador -->
              <div class="search-bar-container">
                <input type="text" class="form-control search-input" 
                  placeholder="La bebida de Jimmy 😋" 
                  v-model="productSearch">
              </div>

              <!-- Banner de ayuda -->
              <div class="alert-banner">
                <i class="fas fa-lightbulb"></i>
                <span>¡Ahora puedes buscar productos! 🔍</span>
              </div>

              <!-- Categorías siempre visibles -->
              <div v-if="(categoriesInstalled && Allcategories && Allcategories.length > 0)" 
                class="categories-list-center">
                <button 
                  @click="changeCategorie(categorie.id)"
                  :class="['category-item-center', (categorieNow == categorie.id) ? 'active' : '']"
                  v-for="(categorie, index) in Allcategories" 
                  :key="index">
                  <div class="category-icon">
                    <i :class="getCategoryIcon(categorie.name)"></i>
                  </div>
                  <span class="category-name">{{ categorie.name }}</span>
                  <div class="category-arrow">
                    <i class="fas fa-chevron-right"></i>
                  </div>
                </button>
                
                <!-- Botón especial Gelateria -->
                <button 
                  v-if="settingGelateria"
                  @click="openGelateria()"
                  class="category-item-center category-special">
                  <div class="category-icon">
                    <i class="fas fa-ice-cream"></i>
                  </div>
                  <span class="category-name">Gelateria</span>
                  <div class="category-arrow">
                    <i class="fas fa-chevron-right"></i>
                  </div>
                </button>
                
                <!-- Botón Colación -->
                <button 
                  @click="openColacion()"
                  class="category-item-center category-special category-colacion">
                  <div class="category-icon">
                    <i class="fas fa-utensils"></i>
                  </div>
                  <span class="category-name">Colación</span>
                  <div class="category-arrow">
                    <i class="fas fa-chevron-right"></i>
                  </div>
                </button>
                
                <!-- Botón Merchise -->
                <button 
                  v-if="settingMerchise"
                  @click="openTestMerchise()"
                  class="category-item-center category-special category-merchise">
                  <div class="category-icon">
                    <i class="fas fa-utensils"></i>
                  </div>
                  <span class="category-name">Merchise</span>
                  <div class="category-arrow">
                    <i class="fas fa-chevron-right"></i>
                  </div>
                </button>
              </div>
            </div>

            <!-- Área de productos (div3) -->
            <div class="products-area-right div3">
              <!-- Mensaje inicial cuando no hay categoría seleccionada -->
              <div v-if="categorieNow === null" class="welcome-message">
                <div class="welcome-content">
                  <i class="fas fa-hand-pointer welcome-icon"></i>
                  <h3 class="welcome-title">¡Bienvenido!</h3>
                  <p class="welcome-text">Selecciona una categoría para empezar el pedido del cliente</p>
                  <div class="welcome-arrow">
                    <i class="fas fa-arrow-left"></i>
                  </div>
                </div>
              </div>
              
              <!-- Grid de productos (solo se muestra cuando hay categoría seleccionada) -->
              <div v-else class="products-grid-right">
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
            <!-- Fin div3 -->

          </div>

          <!-- Panel de comentario flotante -->
          <div v-if="product_comentario" class="comment-section-float">
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

          <!-- Campo de cliente si está habilitado -->
          <div v-if="ticket_sell_client && jsonTable.items.length > 0" class="client-section-float">
            <label class="form-label">
              <i class="fas fa-user me-2"></i>
              Nombre del cliente
            </label>
            <input v-model="clientTicket" class="form-control modern-input" 
              type="text" placeholder="Ingresa el nombre del cliente..." />
          </div>

          <!-- Total y botón de pago fijo en el footer se maneja abajo -->
        </div>

        <!-- Footer renovado -->
        <div class="modal-footer-new">
          <div v-if="jsonTable.items.length > 0" class="footer-content">
            <button class="btn-pay-primary" @click="openPaymentModal()">
              Pagar
            </button>
          </div>
          
          <div v-else class="footer-empty">
            <button type="button" class="btn btn-secondary" @click="closeModal(false)">
              Cerrar
            </button>
          </div>


        </div>

        <!-- Footer viejo DESHABILITADO (usar solo el footer nuevo) -->
        <div v-if="false" class="modal-footer">

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
    <ticket 
      :key="`ticket-${ticketComponentKey}`"
      :typeCreateTicket="typeCreateTicket" 
      v-model="ticketData" 
      @changeValue="changeValue"
      @closeModal="closeModal" />
    <venta-copas @addCopa="handleAddCopa" />
    <test-merchise ref="testMerchise" :cart="jsonTable" @addMerchise="handleAddMerchise" />
    <colacion ref="colacion" @addColacion="handleAddColacion" />
    
    <!-- Modal de métodos de pago -->
    <div v-show="showPaymentModal" :key="`payment-modal-${ticketComponentKey}`" class="payment-modal-overlay" @click="closePaymentModal">
      <div class="payment-modal-content" @click.stop>
        <div class="payment-modal-header">
          <h5 class="payment-modal-title">
            <i class="fas fa-credit-card me-2"></i>
            Seleccionar método de pago
          </h5>
          <button type="button" class="payment-modal-close" @click="closePaymentModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="payment-modal-body">
          <!-- Total a pagar -->
          <div class="payment-total-display">
            <span class="payment-total-label">Total a pagar:</span>
            <span class="payment-total-value">${{ formatNumber(total) }}</span>
          </div>
          
          <!-- Grid de métodos de pago -->
          <div class="payment-methods-grid">
            <button v-if="ticket_sell && settingBoletaLocal" 
              @click="viewTicket('boleta_local'); closePaymentModal()" 
              class="payment-method-btn">
              <i class="fas fa-money-bill-wave"></i>
              <span>Efectivo + Boleta (SII)</span>
            </button>
            
            <button v-if="ticket_sell && settingBoleta" 
              @click="viewTicket('boleta'); closePaymentModal()" 
              class="payment-method-btn">
              <i class="fas fa-receipt"></i>
              <span>Boleta (SII)</span>
            </button>
            
            <button v-if="settingEfectivo" 
              @click="viewTicket('efectivo'); closePaymentModal()" 
              class="payment-method-btn">
              <i class="fas fa-money-bill-wave"></i>
              <span>Efectivo</span>
            </button>
            
            <button v-if="settingDebito" 
              @click="viewTicket('debito'); closePaymentModal()" 
              class="payment-method-btn">
              <i class="fas fa-credit-card"></i>
              <span>Débito</span>
            </button>
            
            <button v-if="settingCredito" 
              @click="viewTicket('credito'); closePaymentModal()" 
              class="payment-method-btn">
              <i class="fas fa-credit-card"></i>
              <span>Crédito</span>
            </button>
            
            <button v-if="settingTransferencia" 
              @click="viewTicket('transferencia'); closePaymentModal()" 
              class="payment-method-btn">
              <i class="fas fa-exchange-alt"></i>
              <span>Transferencia</span>
            </button>
            
            <button v-if="settingRappi" 
              @click="viewTicket('rappi'); closePaymentModal()" 
              class="payment-method-btn">
              <i class="fas fa-motorcycle"></i>
              <span>Rappi</span>
            </button>
            
            <button v-if="settingJunaeb" 
              @click="viewTicket('Junaeb'); closePaymentModal()" 
              class="payment-method-btn">
              <i class="fas fa-school"></i>
              <span>Junaeb</span>
            </button>
            
            <button v-if="settingUber" 
              @click="viewTicket('uber'); closePaymentModal()" 
              class="payment-method-btn">
              <i class="fas fa-car"></i>
              <span>Uber Eats</span>
            </button>
            
            <button v-if="settingSodexo" 
              @click="viewTicket('sodexo'); closePaymentModal()" 
              class="payment-method-btn">
              <i class="fas fa-ticket-alt"></i>
              <span>Sodexo</span>
            </button>
            
            <button v-if="settingAmipass" 
              @click="viewTicket('amipass'); closePaymentModal()" 
              class="payment-method-btn">
              <i class="fas fa-ticket-alt"></i>
              <span>Amipass</span>
            </button>
            
            <button v-if="settingEdenred" 
              @click="viewTicket('edenred'); closePaymentModal()" 
              class="payment-method-btn">
              <i class="fas fa-utensils"></i>
              <span>Edenred</span>
            </button>
            
            <button v-if="settingPedidosYa" 
              @click="viewTicket('pedidos_ya'); closePaymentModal()" 
              class="payment-method-btn">
              <i class="fas fa-utensils"></i>
              <span>Pedidos Ya</span>
            </button>
            
            <button v-if="settingPluxee" 
              @click="viewTicket('pluxee'); closePaymentModal()" 
              class="payment-method-btn">
              <i class="fas fa-wallet"></i>
              <span>Pluxee</span>
            </button>
            
            <button v-if="settingBancoChile20 && isSpecialPaymentDay" 
              @click="viewTicket('banco_chile_20'); closePaymentModal()" 
              class="payment-method-btn payment-special">
              <i class="fas fa-percentage"></i>
              <span>Banco Chile 20% DESC</span>
            </button>
            
            <button v-if="settingFagotto10" 
              @click="viewTicket('fagotto_10'); closePaymentModal()" 
              class="payment-method-btn payment-exclusive">
              <i class="fas fa-star"></i>
              <span>Exclusivo Fagotto 10%</span>
            </button>
            
            <button v-if="settingTurbus10" 
              @click="viewTicket('turbus_10'); closePaymentModal()" 
              class="payment-method-btn" style="background: rgb(0, 51, 153); color: white;">
              <i class="fas fa-bus"></i>
              <span>Turbus 10% DESC</span>
            </button>
            
            <button v-if="order_kitchen_pending == false" 
              @click="viewTicket('ticket'); closePaymentModal()" 
              class="payment-method-btn payment-ticket">
              <i class="fas fa-file-invoice"></i>
              <span>Crear Ticket</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// Componentes
import customTable from '@/components/tables/table.vue';
import cardProductOrders from '@/components/cards/card_product_orders.vue';
import ticket from '@/components/modals/cafeteria/createTicket.vue';
import modalVerify from '@/components/modals/verifyDelete.vue';
import ventaCopas from '@/components/modals/cafeteria/ventaCopas.vue';
import testMerchise from '@/components/modals/cafeteria/testMerchise.vue';
import colacion from '@/components/modals/cafeteria/colacion.vue';

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
      // Modal de categorías
      showCategoriesModal: false,
      // Modal de métodos de pago
      showPaymentModal: false,
      // Key para forzar re-render del componente ticket
      ticketComponentKey: 0,
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
      // Estado del módulo Gelateria
      gelateriaActive: true, // Activado por defecto hasta implementar backend
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
    testMerchise,
    colacion,
  },
  mounted() {
    //HavePermission
    this.refreshData(false, true);
    this.product_comentario = false;
    
    // Verificar la hora de Chile para el método de pago especial
    this.checkChileTime();
    
    // Verificar el estado del módulo Gelateria
    this.checkGelateriaStatus();
    
    // Verificar la hora cada 5 minutos por si cambia el día
    setInterval(() => {
      this.checkChileTime();
    }, 300000); // 5 minutos = 300,000 ms
  },
  methods: {
    // Toggle modal de categorías
    toggleCategoriesModal() {
      this.showCategoriesModal = !this.showCategoriesModal;
    },
    
    // Abrir modal de métodos de pago
    openPaymentModal() {
      if (this.productoSend.length == 0) {
        this.$awn.alert("Es necesario agregar algún producto");
        return false;
      }
      this.showPaymentModal = true;
    },
    
    // Cerrar modal de métodos de pago
    closePaymentModal() {
      this.showPaymentModal = false;
    },
    
    // tickets (require board)

    //JC FECHA 2022-12-14
    async viewTicket(val = false) {
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
      
      // Si es Turbus 10% de descuento
      if (val === 'turbus_10') {
        // Calcular el 10% de descuento
        const originalTotal = this.total;
        const tenPercentDiscount = originalTotal * 0.10;
        const totalWithDiscount = originalTotal - tenPercentDiscount;
        
        // Confirmar con el usuario
        const confirmMessage = `¿Confirmar venta con Turbus 10% de descuento?\n\nTotal original: $${this.formatNumber(originalTotal)}\n10% descuento: -$${this.formatNumber(tenPercentDiscount)}\nTotal final: $${this.formatNumber(totalWithDiscount)}`;
        
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
      console.log('🔍 ANTES DE ASIGNAR ticketData:');
      console.log('🔍 this.total:', this.total);
      console.log('🔍 this.productoSend:', this.productoSend);
      console.log('🔍 this.gananciaTotal:', this.gananciaTotal);
      
      this.ticketData = {
        products: this.productoSend,
        total: this.total,
        gananciaTotal: this.gananciaTotal
      }
      
      console.log('🔍 ticketData CONSTRUIDO:', this.ticketData);
      console.log('🔍 ticketData.total:', this.ticketData.total);
      console.log('🔍 ticketData.products:', this.ticketData.products);
      console.log('🔍 ticketData completo:', JSON.stringify(this.ticketData, null, 2));
      
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
      
      // Si es Turbus 10% de descuento
      if (val === 'turbus_10') {
        const originalTotal = this.total;
        const tenPercentDiscount = originalTotal * 0.10;
        const totalWithDiscount = originalTotal - tenPercentDiscount;
        
        this.ticketData.specialPayment = {
          paymentType: 'turbus_10',
          method: 'Turbus 10%',
          description: 'Descuento Turbus del 10% aplicado al total',
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

      // Verificar si todos los productos son de merchise (venta directa sin mesa)
      const todosProductosMerchise = this.productoSend.every(p => p.is_merchise === true);
      
      console.log('🔍 Verificando tipo de venta:');
      console.log('🔍 todosProductosMerchise:', todosProductosMerchise);
      console.log('🔍 this.board:', this.board);
      console.log('🔍 this.onlyWaiter:', this.onlyWaiter);

      if (todosProductosMerchise) {
        // Venta directa de merchise - sin mesa
        this.ticketData.board_id = null;
        // Usar mesero si está disponible, sino null (venta directa)
        this.ticketData.waiter_id = this.onlyWaiter || null;
        console.log('✅ Venta merchise: board_id=null, waiter_id=' + this.ticketData.waiter_id);
      } else if (this.board && this.board.waiter && this.board.waiter.id) {
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
      } else if (val === 'turbus_10') {
        // 🚌 TURBUS 10%: Generar boleta SII con descuento
        this.typeCreateTicket = 'boleta';
        console.log('🚌 TURBUS 10% - Configurado para generar boleta SII:', {
          typeCreateTicket: this.typeCreateTicket,
          specialPayment: this.ticketData.specialPayment
        });
      } else {
        if (val) this.typeCreateTicket = val;
      }
      
      // 🔧 FIX: Asegurar que el modal de pago esté cerrado antes de abrir createTicket
      this.showPaymentModal = false;
      
      // 🧹 LIMPIEZA PREVENTIVA: Eliminar backdrops residuales antes de abrir el modal
      $('.modal-backdrop').not(':last').remove();
      $('body').removeClass('modal-open').addClass('modal-open'); // Reset class
      
      // ⏱️ ESPERAR UN TICK para que Vue procese los cambios de typeCreateTicket
      await this.$nextTick();
      
      $('#createTicket').modal('show');

    },

    closeModal(refresh = false) {

      // Si está en modo "modal fijo" y se completó una venta, solo resetear el carrito
      if (this.keepModalOpen && refresh !== false) {
        // Limpiar carrito y datos
        this.productoSend = [];
        this.jsonTable.items = [];
        this.ticketDescription = '';
        this.clientTicket = '';
        this.total = null;
        this.gananciaTotal = null;
        this.categorieNow = null; // Resetear categoría para mostrar mensaje de bienvenida
        
        // 🔧 RESET CRÍTICO: Limpiar typeCreateTicket para que el watcher se dispare en la próxima venta
        this.typeCreateTicket = null;
        
        // 🔧 FIX: Cerrar modal de pagos primero si está abierto
        this.showPaymentModal = false;
        
        // 🧹 LIMPIEZA FORZADA: Eliminar todos los modales residuales de Bootstrap
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('padding-right', '');
        
        // 🧹 LIMPIAR NOTIFICACIONES AWN ACUMULADAS
        $('.awn-popup-async-block, .awn-popup-async').remove();
        
        // 🔄 FORZAR RE-RENDER: Incrementar key para destruir y recrear el componente ticket
        this.ticketComponentKey++;
        
        // Cerrar modales secundarios
        $('#createTicket').modal('hide');
        $('#completeOrder').modal('hide');
        
        // ⏱️ Pequeño delay para asegurar limpieza del DOM
        setTimeout(() => {
          $('.modal-backdrop').remove();
          $('.awn-popup-async-block, .awn-popup-async').remove();
        }, 100);
        
        // Emitir refresh pero NO cerrar el modal
        this.$emit('refresh', true);
        
        // Mostrar mensaje de éxito
        this.$awn.success('Venta completada. Puedes continuar con la siguiente orden');
        
        return; // NO ejecutar el resto del código que cierra el modal
      }

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

    // Verificar estado del módulo Gelateria
    async checkGelateriaStatus() {
      try {
        console.log('🔍 Consultando estado de Gelateria...');
        const response = await this.$Connection.getHttp(`${this.$Connection.route}/local/gelateria/status`);
        console.log('📡 Respuesta Gelateria:', response);
        
        if (response.ok && response.data) {
          this.gelateriaActive = response.data.is_active || false;
          console.log('🍦 Estado Gelateria:', this.gelateriaActive);
        } else {
          console.warn('⚠️ Respuesta no válida, usando valor por defecto (true)');
          this.gelateriaActive = true; // Mantener visible si falla
        }
      } catch (error) {
        console.error('❌ Error al verificar estado de Gelateria:', error);
        this.gelateriaActive = true; // Mantener visible si falla
      }
    },

    // Abrir modal de Gelateria
    openGelateria() {
      $('#modalVentaCopas').modal('show');
    },

    // Abrir modal de Test Merchise
    openTestMerchise() {
      this.$refs.testMerchise.openModal();
    },

    // Abrir modal de Colación
    openColacion() {
      this.$refs.colacion.openModal();
    },

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

    // Manejar adición de producto merchise desde modal testMerchise
    handleAddMerchise(merchiseData) {
      console.log('🍕 Datos recibidos de testMerchise:', merchiseData);
      
      // Determinar si es un producto simple o con modifiers
      const isSimpleProduct = merchiseData.type === 'merchise_product';
      
      if (isSimpleProduct) {
        // Producto simple sin modifiers
        this.quantityAdd({
          id: merchiseData.id,
          name: merchiseData.name,
          price: parseFloat(merchiseData.price),
          promo_price: null,
          quantity: 1,
          prices: [{ precio: parseFloat(merchiseData.price) }],
          cecina: false,
          ganancia: 0,
          is_merchise: true
        });
      } else {
        // Producto con modifiers personalizados
        this.quantityAdd({
          id: merchiseData.id,
          name: merchiseData.name,
          price: parseFloat(merchiseData.price),
          promo_price: null,
          quantity: 1,
          prices: [{ precio: parseFloat(merchiseData.price) }],
          cecina: false,
          ganancia: 0,
          is_merchise: true,
          merchise_details: {
            base_product: merchiseData.base_product,
            modifiers: merchiseData.modifiers
          }
        });
      }

      console.log('✅ Producto merchise agregado al carrito');
    },

    // Manejar adición de colación desde modal colacion
    handleAddColacion(colacionData) {
      console.log('🍝 Datos recibidos de colacion:', colacionData);
      
      // Agregar colación al carrito con precio $0
      this.quantityAdd({
        id: colacionData.id,
        name: colacionData.name,
        price: 0, // GRATIS
        promo_price: null,
        quantity: 1,
        prices: [{ precio: 0 }],
        cecina: false,
        ganancia: 0,
        is_colacion: true, // Flag importante
        empleado_retira: colacionData.empleado_retira,
        colacion_details: {
          pasta: colacionData.pasta,
          salsa: colacionData.salsa
        }
      });

      console.log('✅ Colación agregada al carrito para:', colacionData.empleado_retira);
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
      console.log('🔍 addProductQuantity - data recibido:', data);
      console.log('🔍 data.price:', data.price, 'data.quantity:', data.quantity);
      
      // El subtotal es la cantidad actual por el nuevo precio que le envio (#subtotal)
      if(this.promo_active){
        data.subtotal = parseFloat(data.quantity) * parseFloat(data.promo_price);
        data.price = parseFloat(data.quantity) * parseFloat(data.promo_price);
        data.product_promo = true;
      }else{
        data.subtotal = parseFloat(data.quantity) * parseFloat(data.price);
      }
      
      console.log('🔍 después de calcular subtotal:', data.subtotal);
      
      // Si la ganancia esta instalada, agrego la ganancia ✅
      if (this.gananciaInstalled) {
        if (!data.ganancia) data.ganancia = 0; //Esto antes era (this.gananciaInstalled && data.ganancia); pero creo asi es mas correcto -feredev
        data.ganancia = parseFloat(data.quantity) * parseFloat(data.ganancia);
      }
      this.productoSend.push(data);
      this.jsonTable.items = this.productoSend;
      
      console.log('🔍 antes de calculatePlus, productoSend[i]:', this.productoSend[i]);
      
      // Ejecuto #calculatePlus ✅
      this.calculatePlus(i, data, (this.priceUnitaryInstalled) ? true : false);
      
      console.log('🔍 después de calculatePlus, productoSend[i]:', this.productoSend[i]);
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
      console.log('🔍 calculatePlus INICIO - index:', index, 'data:', data);
      
      // Obtengo el producto
      var productActual = Object.assign({}, this.products.find(element => element.id == data.id));
      
      console.log('🔍 productActual encontrado:', productActual);
      
      // Si el producto no existe en this.products (ej: productos merchise), usar productoSend directamente
      if (!productActual || !productActual.id) {
        console.log('⚠️ Producto no encontrado en this.products, usando productoSend[index]');
        productActual = Object.assign({}, this.productoSend[index]);
        console.log('🔍 productActual después de fallback:', productActual);
      }
      
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
        console.log('🔍 Sin precios variantes - productActual.price:', productActual.price, 'productActual.ganancia:', productActual.ganancia);
        
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
      console.log("🔍 productoSend completo:", this.productoSend);
      this.total = 0;
      this.gananciaTotal = 0;
      for (var i = 0; i < this.productoSend.length; i++) {
        var price = 0;
        var ganancia = 0;
        price = this.productoSend[i].subtotal;
        console.log(`🔍 Producto ${i}: subtotal = ${this.productoSend[i].subtotal}, price = ${price}`);
        this.total = parseFloat(this.total) + parseFloat(price);

        if (this.gananciaInstalled) {
          ganancia = this.productoSend[i].ganancia;
          this.gananciaTotal = this.gananciaTotal + parseFloat(ganancia);
        }
      }
      console.log(`🔍 Total final calculado: ${this.total}`);
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
    
    keepModalOpen: {
      get() { return this.$store.getters['cafeteria/getKeepModalOpen'] }
    },

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

    settingMerchise: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.merchise');
      }
    },

    settingGelateria: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.gelateria');
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
    
    settingTurbus10: {
      get() {
        if (!ConfigHelper.ConfStr('modulos.ventas.submodulos.sii')) return false;
        return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii.ajustes.turbus_10');
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

<style scoped>
/* Botón especial Test */
.category-test {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
}

.category-test:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(245, 87, 108, 0.3) !important;
}

/* Botón especial Colación */
.category-colacion {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%) !important;
}

.category-colacion:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4) !important;
}
</style><style>
@import '../../../css/catalog-modal.css';
</style>

