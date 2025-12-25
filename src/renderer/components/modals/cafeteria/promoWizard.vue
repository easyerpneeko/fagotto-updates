<template>
  <div class="modal fade promo-wizard-modal" id="modalPromoWizard" tabindex="-1" role="dialog" 
    aria-labelledby="modalPromoWizard" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
      <div class="modal-content wizard-content">
        
        <!-- Header del Wizard -->
        <div class="wizard-header">
          <div class="wizard-progress">
            <div class="progress-bar-container">
              <div class="progress-bar-fill" :style="{ width: progressPercentage + '%' }"></div>
            </div>
            <div class="progress-steps">
              <div v-for="(step, index) in steps" :key="index" 
                :class="['progress-step', { 'active': currentStep >= index + 1, 'current': currentStep === index + 1 }]">
                <div class="step-number">{{ index + 1 }}</div>
                <div class="step-label">{{ step }}</div>
              </div>
            </div>
          </div>
          <button type="button" class="btn-close-wizard" @click="closeWizard" aria-label="Close">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <!-- Body del Wizard -->
        <div class="wizard-body">
          
          <!-- PASO 0: Bienvenida IA -->
          <div v-if="currentStep === 0" class="wizard-step step-welcome">
            <div class="ai-welcome">
              <div class="ai-avatar">
                <i class="fas fa-robot"></i>
              </div>
              <h2 class="ai-greeting">👋 ¡Hola Cajero!</h2>
              <p class="ai-message">Soy la IA de Fagotto y estoy aquí para ayudarte.</p>
              <p class="ai-subtitle">¿Qué desea el cliente hoy?</p>
            </div>
            
            <div class="mode-selection">
              <button @click="selectMode('promo')" class="mode-card mode-promo">
                <div class="mode-icon">
                  <i class="fas fa-gift"></i>
                </div>
                <h3>Promoción</h3>
                <p>Pasta + Salsa + Bebida</p>
                <span class="mode-badge">¡Combo Completo!</span>
              </button>
              
              <button @click="selectMode('bigoli')" class="mode-card mode-bigoli">
                <div class="mode-icon">
                  <i class="fas fa-utensils"></i>
                </div>
                <h3>Bigoli</h3>
                <p>Elige tu salsa</p>
                <span class="mode-price">Desde $3.990</span>
              </button>
              
              <button @click="selectMode('fetuccini')" class="mode-card mode-fetuccini">
                <div class="mode-icon">
                  <i class="fas fa-utensils"></i>
                </div>
                <h3>Fetuccini</h3>
                <p>Elige tu salsa</p>
                <span class="mode-price">Desde $3.990</span>
              </button>
            </div>
          </div>
          
          <!-- PASO 1: Elegir Pasta (SOLO en modo promo) o Elegir Salsa (en modo bigoli/fetuccini) -->
          
          <!-- Si es modo PROMO: mostrar pastas -->
          <div v-if="currentStep === 1 && selectedMode === 'promo' && !loadingMasterData && allPastas.length > 0" class="wizard-step step-pastas">
            <div class="step-header">
              <h2 class="step-title">🍝 Elige tu Pasta</h2>
              <p class="step-subtitle">Selecciona entre Fetuccini o Bigoli</p>
            </div>
            
            <div class="options-grid">
              <div v-for="(pasta, index) in allPastas" :key="`pasta-${pasta.id}-${index}`" 
                @click="selectPasta(pasta)"
                :class="['option-card', { 'selected': selectedPasta && selectedPasta.id === pasta.id }]">
                <div class="option-icon">
                  <i class="fas fa-utensils"></i>
                </div>
                <h3 class="option-name">{{ pasta.name }}</h3>
              </div>
            </div>
          </div>
          
          <!-- Si es modo BIGOLI o FETUCCINI: mostrar salsas directamente -->
          <div v-if="currentStep === 1 && (selectedMode === 'bigoli' || selectedMode === 'fetuccini')" class="wizard-step step-salsas">
            <div class="step-header">
              <h2 class="step-title">🌶️ Selecciona tu Salsa</h2>
              <p class="step-subtitle">Elige la salsa para tu {{ selectedPasta.name }}</p>
            </div>
            <div class="options-grid grid-salsas">
              <div v-for="salsa in allSalsas" :key="salsa.id" 
                @click="selectSalsa(salsa)"
                :class="['option-card-small', { 'selected': selectedSalsa && selectedSalsa.id === salsa.id }]">
                <div class="option-icon-small">
                  <i class="fas fa-pepper-hot"></i>
                </div>
                <h4 class="option-name-small">{{ salsa.name }}</h4>
              </div>
            </div>
          </div>
          
          <!-- Loading Paso 1 -->
          <div v-if="currentStep === 1 && loadingMasterData" class="wizard-step step-pastas">
            <div style="text-align: center; padding: 40px;">
              <i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #8b5cf6;"></i>
              <p style="margin-top: 20px; color: #64748b;">Cargando...</p>
            </div>
          </div>

          <!-- PASO 2: Elegir Salsa (solo para promoción) -->
          <div v-if="currentStep === 2 && selectedMode === 'promo'" class="wizard-step step-salsas">
            <div class="step-header">
              <h2 class="step-title">🌶️ Selecciona tu Salsa</h2>
              <p class="step-subtitle">Elige la salsa que acompañará tu promoción</p>
            </div>
            <div class="options-grid grid-salsas">
              <div v-for="salsa in allSalsas" :key="salsa.id" 
                @click="selectSalsa(salsa)"
                :class="['option-card-small', { 'selected': selectedSalsa && selectedSalsa.id === salsa.id }]">
                <div class="option-icon-small">
                  <i class="fas fa-pepper-hot"></i>
                </div>
                <h4 class="option-name-small">{{ salsa.name }}</h4>
              </div>
            </div>
          </div>

          <!-- PASO 2 o 3: Elegir Bebida -->
          <div v-if="(currentStep === 2 && selectedMode !== 'promo') || (currentStep === 3 && selectedMode === 'promo')" class="wizard-step step-bebidas">
            <div class="step-header">
              <h2 class="step-title">🥤 Selecciona tu Bebida</h2>
              <p class="step-subtitle">¿Qué bebida prefieres para tu combo?</p>
            </div>
            <div class="options-grid grid-bebidas">
              <div v-for="bebida in bebidas" :key="bebida.id" 
                @click="selectBebida(bebida)"
                :class="['option-card-medium', { 'selected': selectedBebida && selectedBebida.id === bebida.id }]">
                <div class="option-icon-medium">
                  <i :class="bebida.icon"></i>
                </div>
                <h4 class="option-name-medium">{{ bebida.name }}</h4>
                <p class="option-size">{{ bebida.size }}</p>
              </div>
            </div>
          </div>

        </div>

          <!-- PASO Extras: mostrar en step 4 para promo o step 3 para modos no-promo -->
          <div v-if="(currentStep === 4 && selectedMode === 'promo') || (currentStep === 3 && selectedMode !== 'promo')" class="wizard-step step-extras">
            <div class="step-header">
              <h2 class="step-title">➕ ¿Quieres algo extra?</h2>
              <p class="step-subtitle">Añade queso extra o salsa adicional</p>
            </div>
            <div class="extras-grid">
              <button @click="handleQuesoClick" :class="['option-card-extra', { 'selected': hasQuesoExtra }]">
                <i class="fas fa-cheese"></i>
                <div>Queso extra</div>
              </button>
              <button v-if="!selectingSalsaExtra" @click="startSelectingSalsaExtra" :class="['option-card-extra', { 'selected': hasSalsaExtra }]">
                <i class="fas fa-pepper-hot"></i>
                <div>Salsa extra</div>
              </button>
              <button @click="cancelExtras" class="option-card-extra">
                <i class="fas fa-times"></i>
                <div>No, gracias</div>
              </button>
            </div>

            <div v-if="selectingSalsaExtra" class="options-grid grid-salsas extras-salsa-list">
              <div v-for="salsa in allSalsas" :key="salsa.id"
                @click="chooseSalsaExtra(salsa)"
                :class="['option-card-small', { 'selected': selectedSalsaExtra && selectedSalsaExtra.id === salsa.id }]">
                <div class="option-icon-small">
                  <i class="fas fa-pepper-hot"></i>
                </div>
                <h4 class="option-name-small">{{ salsa.name }}</h4>
              </div>
            </div>
          </div>

        <!-- Footer del Wizard -->
        <div class="wizard-footer">
          <!-- Botón de confirmación: paso 4 para promo, paso 2 para bigoli/fetuccini -->
              <button v-if="(currentStep === 4 && selectedMode === 'promo') || (currentStep === 3 && selectedMode !== 'promo')" 
                @click="confirmOrder" class="btn-wizard btn-confirm" :disabled="isConfirming" :class="{ 'disabled': isConfirming }">
            <i class="fas fa-check"></i>
            Agregar al Carrito - ${{ formatNumber(totalPrice) }}
          </button>
          
          <!-- Botón para saltar bebida y confirmar sin bebida -->
              <button v-if="(currentStep === 4 && selectedMode === 'promo') || (currentStep === 3 && selectedMode !== 'promo')" 
                @click="confirmOrder" class="btn-wizard btn-skip" :disabled="isConfirming" :class="{ 'disabled': isConfirming }">
            <i class="fas fa-forward"></i>
            Sin bebida
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import FormatNumber from '@/helpers/FormatNumber.js';
import Connection from '@/helpers/Connection.js';
import BaseUrl from '@/helpers/baseUrl.js';

export default {
  name: 'PromoWizard',
  props: {
    products: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      currentStep: 0, // Empezar en paso 0 (bienvenida)
      selectedMode: null, // 'promo', 'bigoli', 'fetuccini'
      steps: ['Bienvenida', 'Salsa', 'Bebida', 'Extras', 'Confirmar'],

      // Selecciones del usuario
      selectedPasta: null,
      selectedSalsa: null,
      selectedBebida: null,
      // Extras
      hasQuesoExtra: false,
      hasSalsaExtra: false,
      // UI state for selecting which salsa extra
      selectingSalsaExtra: false,
      selectedSalsaExtra: null,
      // Prevent multiple confirmations
      isConfirming: false,
      
      // Precios según salsa y pasta
      precioBase: 0,
      precioBebida: 0,
      // Precios de extras
      precioExtraQueso: 0,
      precioExtraSalsa: 0,
      
      // Productos desde DB maestra easyerp
      masterPastas: [],
      masterSalsas: [],
      
      // Productos del negocio (desde props)
      bebidas: [],
      
      // Loading states
      loadingMasterData: false
    };
  },
  computed: {
    // Concatenar Fetuccini y Bigoli de la DB maestra
    allPastas() {
      return this.masterPastas;
    },
    
    // Todas las salsas de la DB maestra
    allSalsas() {
      return this.masterSalsas;
    },
    
    progressPercentage() {
      return (this.currentStep / this.steps.length) * 100;
    },
    canContinue() {
      switch(this.currentStep) {
        case 0: return this.selectedMode !== null;
        case 1: 
          // Si es promo, debe seleccionar pasta. Si es bigoli/fetuccini, debe seleccionar salsa
          if (this.selectedMode === 'promo') {
            return this.selectedPasta !== null;
          } else {
            return this.selectedSalsa !== null;
          }
        case 2: return true; // Bebida es opcional
        default: return true;
      }
    },
    totalPrice() {
      let total = this.precioBase;
      if (this.selectedBebida) total += this.precioBebida;
      // Añadir extras si existen
      total += (this.precioExtraQueso || 0) + (this.precioExtraSalsa || 0);
      console.log('💰 Total calculado:', total, 'Base:', this.precioBase, 'Bebida:', this.precioBebida);
      return total;
    }
  },
  methods: {
    formatNumber(number) {
      return FormatNumber.format(number);
    },
    
    selectMode(mode) {
      console.log('🎯 Modo seleccionado:', mode);
      this.selectedMode = mode;
      
      // Configurar pasta según modo
      if (mode === 'bigoli') {
        this.selectedPasta = { id: 'bigoli', name: 'Bigoli' };
      } else if (mode === 'fetuccini') {
        this.selectedPasta = { id: 'fetuccini', name: 'Fetuccini' };
      }
      
      // Avanzar al paso de salsas
      this.currentStep = 1;
    },
    
    selectPasta(pasta) {
      console.log('🍝 selectPasta() llamado con:', pasta);
      this.selectedPasta = pasta;
      console.log('🍝 Pasta seleccionada asignada:', this.selectedPasta);
      
      // Cargar salsas después de seleccionar pasta
      this.loadSalsas();
    },
    
    selectSalsa(salsa) {
      console.log('🌶️ selectSalsa() llamado con:', salsa);
      this.selectedSalsa = salsa;
      console.log('🌶️ Salsa seleccionada asignada:', this.selectedSalsa);
      
      // Calcular precio base según salsa
      const salsaEconomica = ['Alfredo', 'Boloñesa'].includes(salsa.name);
      this.precioBase = salsaEconomica ? 3990 : 4990; // Alfredo/Boloñesa => 3990, resto => 4990
      console.log('💰 Precio base asignado:', this.precioBase);
      
      // Nota: no fijamos precio de bebida aquí — el precio real de la bebida se toma cuando el usuario la selecciona
      
      console.log('⏱️ Esperando 300ms antes de avanzar...');
      // Avanzar automáticamente al siguiente paso
      setTimeout(() => {
        console.log('➡️ Llamando nextStep()');
        this.nextStep();
      }, 300);
    },
    
    selectBebida(bebida) {
      console.log('🥤 selectBebida() llamado con:', bebida);
      this.selectedBebida = bebida;
      console.log('🥤 Bebida seleccionada asignada:', this.selectedBebida);
      // En modos de promo/Bigoli/Fetuccini la bebida debe sumar +1000 (ignorar precio del producto)
      const promoModes = ['promo', 'bigoli', 'fetuccini'];
      if (promoModes.includes(this.selectedMode)) {
        this.precioBebida = 1000;
        console.log('🥤 modo promo/Bigoli/Fetuccini: precioBebida fijado en 1000');
      } else {
        // Usar el precio real del producto bebida si está disponible, si no fallback a 1000
        const bebidaPrecio = (bebida && (bebida.price || bebida.precio)) ? parseFloat(bebida.price || bebida.precio) : 1000;
        this.precioBebida = isNaN(bebidaPrecio) ? 1000 : bebidaPrecio;
        console.log('🥤 precioBebida aplicado:', this.precioBebida, '(origen:', bebida && (bebida.price ? 'price' : (bebida.precio ? 'precio' : 'fallback')) + ')');
      }
      // Avanzar a Extras para mostrar sugerencias en todos los modos
      console.log('➡️ Avanzando a Extras para mostrar sugerencias');
      this.nextStep();
      // En otros modos, no avanzamos automáticamente — el usuario confirma directamente
    },
    
    selectQuesoExtra(value) {
      if (this.isConfirming) return;
      this.hasQuesoExtra = value;
      this.precioExtraQueso = value ? 500 : 0; // precio fijo de ejemplo
      console.log('🧀 Queso extra:', this.hasQuesoExtra, 'precioExtraQueso:', this.precioExtraQueso);
      // Confirm immediately when queso extra selected
      if (value) {
        console.log('🧀 Queso extra seleccionado: confirmando orden');
        this.confirmOrder();
      } else {
        this.nextStep();
      }
    },
    
    selectSalsaExtra(value) {
      // Deprecated direct toggle path: keep for compatibility but prefer startSelectingSalsaExtra
      this.hasSalsaExtra = value;
      this.precioExtraSalsa = value ? 700 : 0;
      console.log('🌶️ Salsa extra (direct):', this.hasSalsaExtra, 'precioExtraSalsa:', this.precioExtraSalsa);
      if (value) {
        this.startSelectingSalsaExtra();
      } else {
        this.nextStep();
      }
    },

    handleQuesoClick() {
      if (this.isConfirming) return;
      // User clicked Queso extra — set and finalize
      this.hasQuesoExtra = true;
      this.precioExtraQueso = 500;
      console.log('🧀 handleQuesoClick -> precioExtraQueso:', this.precioExtraQueso);
      this.confirmOrder();
    },

    startSelectingSalsaExtra() {
      this.selectingSalsaExtra = true;
      this.hasSalsaExtra = true;
      console.log('🌶️ startSelectingSalsaExtra -> opening salsa choices');
    },

    cancelExtras() {
      this.hasQuesoExtra = false;
      this.hasSalsaExtra = false;
      this.selectingSalsaExtra = false;
      this.precioExtraQueso = 0;
      this.precioExtraSalsa = 0;
      // go to confirm step (or stay) — advance to confirm so user can still click
      this.nextStep();
    },

    chooseSalsaExtra(salsa) {
      console.log('🌶️ chooseSalsaExtra() elegido:', salsa);
      this.selectedSalsaExtra = salsa;
      this.precioExtraSalsa = 700; // could vary per salsa if needed
      // attach name of extra salsa to selectedSalsaExtra
      this.selectingSalsaExtra = false;
      // finalize order immediately after choosing salsa extra
      this.confirmOrder();
    },
    
    nextStep() {
      console.log('🔄 nextStep() llamado. Step actual:', this.currentStep, 'canContinue:', this.canContinue);
      const maxStep = this.steps.length - 1;
      if (this.canContinue && this.currentStep < maxStep) {
        this.currentStep++;
        console.log('✅ Avanzado a step:', this.currentStep);
      } else {
        console.log('❌ No se puede avanzar. canContinue:', this.canContinue, 'currentStep:', this.currentStep, 'maxStep:', maxStep);
      }
    },
    
    previousStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
      }
    },
    
    confirmOrder() {
      if (this.isConfirming) {
        console.log('⏳ confirmOrder() ignorada porque ya está confirmando');
        return;
      }
      this.isConfirming = true;
      console.log('✅ Confirmando orden...');
      console.log('Pasta:', this.selectedPasta);
      console.log('Salsa:', this.selectedSalsa);
      console.log('Bebida:', this.selectedBebida);
      console.log('Precio Base:', this.precioBase);
      console.log('Precio Bebida:', this.precioBebida);
      console.log('Total:', this.totalPrice);
      
      const productName = `${this.selectedPasta.name} ${this.selectedSalsa.name}${this.selectedBebida ? ' + ' + this.selectedBebida.name : ''}`;
      
      const order = {
        id: `pasta_${Date.now()}`,
        name: productName,
        price: parseFloat(this.totalPrice) || 0,
        quantity: 1,
        type: 'pasta_promo',
        mode: this.selectedMode,
        details: {
          pasta: this.selectedPasta ? this.selectedPasta.name : null,
          salsa: this.selectedSalsa ? this.selectedSalsa.name : null,
          bebida: this.selectedBebida ? this.selectedBebida.name : null,
          queso_extra: this.hasQuesoExtra,
          salsa_extra: this.hasSalsaExtra,
          salsa_extra_name: this.selectedSalsaExtra ? this.selectedSalsaExtra.name : null,
          precio_extra_queso: this.precioExtraQueso,
          precio_extra_salsa: this.precioExtraSalsa
        }
      };
      
      console.log('📦 Orden final:', order);
      this.$emit('orderConfirmed', order);
      // give a short delay to avoid double-click race, modal will reset isConfirming
      setTimeout(() => {
        this.closeWizard();
      }, 80);
    },
    
    closeWizard() {
      // Preguntar si está seguro de salir si ya empezó
      if (this.currentStep > 1 && this.selectedPasta) {
        if (!confirm('¿Estás seguro de cancelar este pedido?')) {
          return;
        }
      }
      $('#modalPromoWizard').modal('hide');
      // Resetear después de cerrar para evitar parpadeos
      setTimeout(() => {
        this.resetWizard();
      }, 300);
    },
    
    resetWizard() {
      this.currentStep = 0;
      this.selectedMode = null;
      this.selectedPasta = null;
      this.selectedSalsa = null;
      this.selectedBebida = null;
      this.precioBase = 0;
      this.precioBebida = 0;
      // Reset extras state
      this.hasQuesoExtra = false;
      this.hasSalsaExtra = false;
      this.selectingSalsaExtra = false;
      this.selectedSalsaExtra = null;
      this.precioExtraQueso = 0;
      this.precioExtraSalsa = 0;
      this.isConfirming = false;
    },
    
    openWizard() {
      console.log('📱 openWizard() llamado');
      this.resetWizard();
      console.log('🔄 Wizard reseteado, abriendo modal...');
      
      // Abrir modal primero
      $('#modalPromoWizard').modal('show');
      console.log('✅ Modal show ejecutado');
      
      // CARGAR DATOS después de que el modal esté completamente abierto
      setTimeout(() => {
        console.log('🚀 Cargando datos de DB maestra...');
        this.loadMasterData();
        this.loadLocalProducts();
      }, 300);
      
      // Verificar después de show
      setTimeout(() => {
        const modalElement = document.getElementById('modalPromoWizard');
        const hasShowClass = $('#modalPromoWizard').hasClass('show');
        console.log('🔍 Modal tiene clase "show":', hasShowClass);
        console.log('🔍 Modal display después de show:', modalElement ? modalElement.style.display : 'N/A');
      }, 100);
    },
    
    // Manejar ESC key
    handleEscape(e) {
      if (e.key === 'Escape' && this.currentStep === 1) {
        this.closeWizard();
      }
    },
    
    // Cargar datos desde DB maestra easyerp
    async loadMasterData() {
      try {
        this.loadingMasterData = true;
        console.log('🌍 Consultando DB maestra para PASTAS...');
        
        const url = BaseUrl.getUrl('api/promo/master/data');
        console.log('📍 URL completa:', url);
        
        const response = await Connection.fetch(url, 'GET', null, null, false);
        console.log('📨 Respuesta completa:', response);
        
        if (response && response.ok && response.data && response.data.success) {
          const promoData = response.data.data;
          
          const pastas = promoData.pastas.map(p => ({
            id: p.id,
            name: p.name,
            description: p.description,
            price: 4990
          }));
          
          this.masterPastas.splice(0, this.masterPastas.length, ...pastas);
          console.log('✅ Pastas maestras cargadas desde DB:', this.masterPastas);
        } else {
          const errorMsg = (response && response.data && response.data.message) || 'Sin mensaje';
          console.error('❌ Error en la respuesta:', errorMsg);
          throw new Error('Respuesta inválida de la API');
        }
      } catch (error) {
        console.error('❌ Error al cargar pastas desde DB maestra:', error);
        alert('Error: No se pueden cargar las pastas desde la DB maestra. Verifica la conexión.');
      } finally {
        this.loadingMasterData = false;
      }
    },
    
    // Cargar salsas desde DB maestra después de elegir pasta
    async loadSalsas() {
      try {
        console.log('🌶️ Consultando DB maestra para SALSAS...');
        
        const url = BaseUrl.getUrl('api/promo/master/data');
        
        const response = await Connection.fetch(url, 'GET', null, null, false);
        console.log('📨 Respuesta de salsas:', response);
        
        if (response && response.ok && response.data && response.data.success) {
          const salsas = response.data.data.salsas;
          this.masterSalsas.splice(0, this.masterSalsas.length, ...salsas);
          console.log('✅ Salsas maestras cargadas desde DB:', this.masterSalsas);
          
          // Avanzar al siguiente paso después de cargar
          this.nextStep();
        } else {
          throw new Error('Respuesta inválida');
        }
      } catch (error) {
        console.error('❌ Error al cargar salsas desde DB maestra:', error);
        alert('Error: No se pueden cargar las salsas desde la DB maestra. Verifica la conexión.');
      }
    },
    
    // Cargar productos del negocio (bebidas) desde props
    loadLocalProducts() {
      console.log('🏪 Cargando productos locales del negocio...');
      
      // Filtrar Bebidas (categoría 3)
      this.bebidas = this.products.filter(p => p.category == 3);
      console.log('Bebidas:', this.bebidas);
    }
  },
  mounted() {
    console.log('🎬 PromoWizard montado');
    
    // Agregar listener para ESC
    document.addEventListener('keydown', this.handleEscape);
    
    // Limpiar cuando el modal se cierra
    $('#modalPromoWizard').on('hidden.bs.modal', () => {
      this.resetWizard();
    });
  },
  beforeDestroy() {
    // Limpiar listeners
    document.removeEventListener('keydown', this.handleEscape);
    $('#modalPromoWizard').off('hidden.bs.modal');
  },
  watch: {
    // Recargar productos locales cuando cambien los props
    products: {
      handler() {
        this.loadLocalProducts();
      },
      deep: true
    }
  }
};
</script>

<style scoped>
@import '../../../css/promo-wizard.css';

/* Styles for Extras step and buttons (local to this component) */
.step-extras {
  padding: 16px 32px 16px; /* menos padding inferior para subir el contenido */
  text-align: center;
  min-height: 220px;
  display: flex;
  flex-direction: column;
  justify-content: flex-start; /* alinear contenido hacia arriba */
}
.extras-grid {
  display: flex;
  gap: 12px;
  justify-content: center;
  align-items: center;
  margin-top: 12px;
}
.option-card-extra {
  background: #ffffff;
  border: 1px solid #e6e6e6;
  padding: 10px 16px;
  border-radius: 8px;
  min-width: 120px;
  display: flex;
  gap: 8px;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.12s ease-in-out;
}
.option-card-extra.selected {
  background: #10b981;
  color: white;
  border-color: rgba(16,185,129,0.9);
  box-shadow: 0 6px 18px rgba(16,185,129,0.18);
}
.option-card-extra i { font-size: 18px; }

/* Footer buttons styling adjustments to match screenshot */
.wizard-footer { padding: 16px 24px; display:flex; gap:12px; justify-content:flex-start; }
.btn-wizard { border-radius: 8px; padding: 12px 20px; font-weight:600; }
.btn-confirm { background: #10b981; color: #fff; border: none; }
.btn-skip { background: transparent; border: 1px solid #e6e6e6; color: #374151; }

</style>
</style>
