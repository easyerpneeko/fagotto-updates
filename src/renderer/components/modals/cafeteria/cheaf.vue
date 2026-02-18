<template>
  <div v-show="isVisible" class="cheaf-overlay" @click.self="closeModal">
    <div class="cheaf-modal">
      <!-- Header -->
      <div class="cheaf-header">
        <div class="header-content">
          <div class="header-left">
            <i class="fas fa-fire logo-icon"></i>
            <div>
              <h3 class="header-title">Promociones Cheaf</h3>
              <p class="header-date">{{ fechaHoy }}</p>
            </div>
          </div>
          <button @click="closeModal" class="btn-close-cheaf">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="header-greeting">
          <i class="fas fa-fire-flame-curved"></i>
          ¡Ofertas especiales de nuestro Chef!
        </div>
      </div>

      <!-- Body -->
      <div class="cheaf-body">
        <!-- Promoción de Pastas -->
        <div class="promo-card" @click="selectPromo('pastas')">
          <div class="promo-header">
            <div class="promo-icon">
              <i class="fas fa-wheat-awn"></i>
            </div>
            <div class="promo-info">
              <h4 class="promo-title">2 Pastas</h4>
              <p class="promo-subtitle">Elige 2 pastas con tu salsa favorita</p>
            </div>
          </div>
          <div class="promo-price">
            <span class="currency">$</span>
            <span class="amount">5.990</span>
          </div>
          <div v-if="selectedPromo === 'pastas'" class="promo-selected">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>

        <!-- Promoción de Ciabattas -->
        <div class="promo-card" @click="selectPromo('ciabattas')">
          <div class="promo-header">
            <div class="promo-icon ciabatta-icon">
              <i class="fas fa-bread-slice"></i>
            </div>
            <div class="promo-info">
              <h4 class="promo-title">2 Ciabattas</h4>
              <p class="promo-subtitle">2 deliciosas ciabattas a elección</p>
            </div>
          </div>
          <div class="promo-price">
            <span class="currency">$</span>
            <span class="amount">3.890</span>
          </div>
          <div v-if="selectedPromo === 'ciabattas'" class="promo-selected">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>

        <!-- Detalles de la promoción seleccionada -->
        <div v-if="selectedPromo" class="promo-details">
          <div class="detail-section">
            <h5 class="detail-title">
              <i class="fas fa-info-circle"></i>
              Detalles de tu promoción
            </h5>
            <div v-if="selectedPromo === 'pastas'" class="detail-content">
              <p>✅ 2 pastas de cualquier tipo</p>
              <p>✅ Con la salsa que prefieras</p>
              <p>✅ Precio especial: $5.990</p>
            </div>
            <div v-else-if="selectedPromo === 'ciabattas'" class="detail-content">
              <p>✅ 2 ciabattas de tu elección</p>
              <p>✅ Variedad de sabores disponibles</p>
              <p>✅ Precio especial: $3.890</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="cheaf-footer">
        <button 
          @click="agregarPromo" 
          :disabled="!selectedPromo"
          class="btn-agregar"
          :class="{ 'disabled': !selectedPromo }">
          <i class="fas fa-cart-plus"></i>
          Agregar Promoción
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import moment from 'moment';
import 'moment/locale/es';

moment.locale('es');

export default {
  name: 'Cheaf',
  data() {
    return {
      isVisible: false,
      selectedPromo: null,
      fechaHoy: moment().format('dddd, D [de] MMMM [de] YYYY')
    };
  },
  methods: {
    openModal() {
      this.isVisible = true;
      this.selectedPromo = null;
    },
    
    closeModal() {
      this.isVisible = false;
    },

    selectPromo(tipo) {
      this.selectedPromo = tipo;
    },

    agregarPromo() {
      if (!this.selectedPromo) {
        if (this.$parent.$awn) {
          this.$parent.$awn.warning('Por favor selecciona una promoción');
        }
        return;
      }

      let promoData;

      if (this.selectedPromo === 'pastas') {
        promoData = {
          id: `cheaf_pastas_${Date.now()}`,
          name: 'Cheaf - 2 Pastas',
          price: 5990,
          quantity: 1,
          type: 'cheaf_promo',
          is_cheaf: true,
          promo_type: 'pastas',
          description: 'Promoción Cheaf: 2 Pastas por $5.990'
        };
      } else if (this.selectedPromo === 'ciabattas') {
        promoData = {
          id: `cheaf_ciabattas_${Date.now()}`,
          name: 'Cheaf - 2 Ciabattas',
          price: 3890,
          quantity: 1,
          type: 'cheaf_promo',
          is_cheaf: true,
          promo_type: 'ciabattas',
          description: 'Promoción Cheaf: 2 Ciabattas por $3.890'
        };
      }

      console.log('🔥 Agregando promoción Cheaf:', promoData);

      // Emitir al padre (catalog.vue) para que agregue al carro
      this.$emit('addCheaf', promoData);

      // Mostrar confirmación
      if (this.$parent.$awn) {
        const mensaje = this.selectedPromo === 'pastas' ? 
          '2 Pastas por $5.990 agregadas' : 
          '2 Ciabattas por $3.890 agregadas';
        this.$parent.$awn.success(mensaje, {
          labels: { success: 'PROMOCIÓN AGREGADA' }
        });
      }

      // Cerrar modal
      this.closeModal();
    }
  }
};
</script>

<style scoped>
/* Overlay */
.cheaf-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 20px;
}

/* Modal */
.cheaf-modal {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 650px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Header */
.cheaf-header {
  background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
  padding: 25px 30px;
  color: white;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 15px;
}

.logo-icon {
  font-size: 40px;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.header-title {
  margin: 0;
  font-size: 26px;
  font-weight: 700;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.header-date {
  margin: 5px 0 0 0;
  font-size: 13px;
  opacity: 0.9;
}

.btn-close-cheaf {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  color: white;
  font-size: 20px;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-close-cheaf:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.header-greeting {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 15px;
  padding: 12px 18px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  backdrop-filter: blur(10px);
}

/* Body */
.cheaf-body {
  padding: 30px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Promo Card */
.promo-card {
  position: relative;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 15px;
  padding: 25px;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
  box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
}

.promo-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
}

.promo-card:nth-child(2) {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  box-shadow: 0 5px 20px rgba(245, 87, 108, 0.3);
}

.promo-card:nth-child(2):hover {
  box-shadow: 0 10px 30px rgba(245, 87, 108, 0.5);
}

.promo-header {
  display: flex;
  align-items: center;
  gap: 20px;
  flex: 1;
}

.promo-icon {
  width: 60px;
  height: 60px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  backdrop-filter: blur(10px);
}

.promo-info {
  flex: 1;
}

.promo-title {
  margin: 0 0 5px 0;
  font-size: 22px;
  font-weight: 700;
}

.promo-subtitle {
  margin: 0;
  font-size: 14px;
  opacity: 0.9;
}

.promo-price {
  display: flex;
  align-items: baseline;
  gap: 5px;
  font-weight: 700;
}

.currency {
  font-size: 20px;
}

.amount {
  font-size: 32px;
}

.promo-selected {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(255, 255, 255, 0.3);
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
  animation: bounce 0.5s;
}

.promo-selected i {
  font-size: 24px;
  color: white;
}

@keyframes bounce {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.2); }
}

/* Detalles */
.promo-details {
  background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
  border-radius: 15px;
  padding: 20px;
  animation: fadeIn 0.3s;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.detail-title {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0 0 15px 0;
  color: #667eea;
  font-size: 16px;
}

.detail-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.detail-content p {
  margin: 0;
  padding: 8px 12px;
  background: white;
  border-radius: 8px;
  color: #333;
  font-size: 14px;
}

/* Footer */
.cheaf-footer {
  padding: 20px 30px;
  background: #f8f9fa;
  border-top: 1px solid #e0e0e0;
}

.btn-agregar {
  width: 100%;
  padding: 16px 24px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 12px;
  color: white;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.btn-agregar:hover:not(.disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
}

.btn-agregar:active:not(.disabled) {
  transform: translateY(0);
}

.btn-agregar.disabled {
  background: #ccc;
  cursor: not-allowed;
  box-shadow: none;
}

.btn-agregar i {
  font-size: 20px;
}
</style>
