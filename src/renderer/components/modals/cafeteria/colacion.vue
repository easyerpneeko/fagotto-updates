<template>
  <div v-show="isVisible" class="colacion-overlay" @click.self="closeModal">
    <div class="colacion-modal">
      <!-- Header -->
      <div class="colacion-header">
        <div class="header-content">
          <div class="header-left">
            <i class="fas fa-utensils logo-icon"></i>
            <div>
              <h3 class="header-title">Colación del Día</h3>
              <p class="header-date">{{ fechaHoy }}</p>
            </div>
          </div>
          <button @click="closeModal" class="btn-close-colacion">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="header-greeting">
          <i class="fas fa-hand-sparkles"></i>
          Hola cajero, esta será tu colación el día de hoy
        </div>
      </div>

      <!-- Body -->
      <div class="colacion-body">
        <!-- Selección de Pasta -->
        <div class="selection-group">
          <h4 class="selection-title">
            <i class="fas fa-wheat-awn"></i>
            Elige tu pasta
          </h4>
          <div class="options-grid">
            <div 
              @click="selectedPasta = 'bigoli'"
              :class="['option-card', { 'selected': selectedPasta === 'bigoli' }]">
              <div v-if="selectedPasta === 'bigoli'" class="option-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="option-name">Bigoli</div>
            </div>
            <div 
              @click="selectedPasta = 'fettuccini'"
              :class="['option-card', { 'selected': selectedPasta === 'fettuccini' }]">
              <div v-if="selectedPasta === 'fettuccini'" class="option-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="option-name">Fettuccini</div>
            </div>
          </div>
        </div>

        <!-- Selección de Salsa -->
        <div class="selection-group">
          <h4 class="selection-title">
            <i class="fas fa-bowl-food"></i>
            Selecciona la salsa
          </h4>
          <div class="options-grid">
            <div 
              @click="selectedSalsa = 'alfredo'"
              :class="['option-card', { 'selected': selectedSalsa === 'alfredo' }]">
              <div v-if="selectedSalsa === 'alfredo'" class="option-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="option-name">Alfredo</div>
            </div>
            <div 
              @click="selectedSalsa = 'bolonesa'"
              :class="['option-card', { 'selected': selectedSalsa === 'bolonesa' }]">
              <div v-if="selectedSalsa === 'bolonesa'" class="option-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="option-name">Boloñesa</div>
            </div>
            <div 
              @click="selectedSalsa = 'cheddar'"
              :class="['option-card', { 'selected': selectedSalsa === 'cheddar' }]">
              <div v-if="selectedSalsa === 'cheddar'" class="option-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="option-name">Salsa Cheddar</div>
            </div>
          </div>
        </div>

        <!-- Nombre del Empleado -->
        <div class="selection-group">
          <h4 class="selection-title">
            <i class="fas fa-user"></i>
            Nombre del empleado a retiro
          </h4>
          <input 
            v-model="empleadoNombre"
            type="text"
            class="input-empleado"
            placeholder="Escribe el nombre del empleado..."
            @keyup.enter="agregarColacion"
          />
        </div>
      </div>

      <!-- Footer -->
      <div class="colacion-footer">
        <div class="footer-info">
          <i class="fas fa-gift"></i>
          <span>Colación gratuita - Costo: $0</span>
        </div>
        <button 
          @click="agregarColacion" 
          :disabled="!canAdd"
          class="btn-agregar">
          <i class="fas fa-plus-circle"></i>
          Agregar Colación
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import moment from 'moment';
import 'moment/locale/es'; // Importar español

moment.locale('es'); // Configurar español

export default {
  name: 'Colacion',
  data() {
    return {
      isVisible: false,
      selectedPasta: null,
      selectedSalsa: null,
      empleadoNombre: '',
      fechaHoy: moment().format('dddd, D [de] MMMM [de] YYYY')
    };
  },
  computed: {
    canAdd() {
      return this.selectedPasta && this.selectedSalsa && this.empleadoNombre.trim() !== '';
    }
  },
  methods: {
    openModal() {
      this.isVisible = true;
      // Resetear valores
      this.selectedPasta = null;
      this.selectedSalsa = null;
      this.empleadoNombre = '';
    },
    
    closeModal() {
      this.isVisible = false;
    },

    agregarColacion() {
      if (!this.canAdd) {
        if (this.$parent.$awn) {
          this.$parent.$awn.warning('Por favor completa todos los campos');
        }
        return;
      }

      // Construir nombre del producto
      const pastaNombre = this.selectedPasta === 'bigoli' ? 'Bigoli' : 'Fettuccini';
      const salsaNombre = this.selectedSalsa === 'alfredo' ? 'Alfredo' : 
                         this.selectedSalsa === 'bolonesa' ? 'Boloñesa' : 'Salsa Cheddar';
      
      const nombreProducto = `${pastaNombre} con ${salsaNombre}`;

      // Crear producto para el carro
      const colacionData = {
        id: `colacion_${Date.now()}`, // ID único con timestamp
        name: nombreProducto,
        price: 0, // SIEMPRE GRATIS
        quantity: 1,
        type: 'colacion',
        is_colacion: true, // Flag importante
        empleado_retira: this.empleadoNombre.trim(),
        pasta: this.selectedPasta,
        salsa: this.selectedSalsa,
        description: `Colación del día - ${this.empleadoNombre}`
      };

      console.log('✅ Agregando colación:', colacionData);

      // Emitir al padre (catalog.vue) para que agregue al carro
      this.$emit('addColacion', colacionData);

      // Mostrar confirmación
      if (this.$parent.$awn) {
        this.$parent.$awn.success(`Colación agregada para ${this.empleadoNombre}`, {
          labels: { success: 'COLACIÓN AGREGADA' }
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
.colacion-overlay {
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
.colacion-modal {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 600px;
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
.colacion-header {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
  font-size: 18px;
  background: rgba(255, 255, 255, 0.2);
  padding: 8px;
  border-radius: 8px;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.header-title {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  line-height: 1.2;
}

.header-date {
  margin: 5px 0 0 0;
  font-size: 14px;
  opacity: 0.9;
}

.btn-close-colacion {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 18px;
}

.btn-close-colacion:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.header-greeting {
  display: flex;
  align-items: center;
  gap: 10px;
  background: rgba(255, 255, 255, 0.15);
  padding: 12px 18px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
}

.header-greeting i {
  font-size: 16px;
}

/* Body */
.colacion-body {
  padding: 30px;
  max-height: 500px;
  overflow-y: auto;
}

.selection-group {
  margin-bottom: 30px;
}

.selection-group:last-child {
  margin-bottom: 0;
}

.selection-title {
  font-size: 16px;
  font-weight: 700;
  color: #333;
  margin: 0 0 12px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.selection-title i {
  color: #f5576c;
  font-size: 18px;
}

.options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
}

.option-card {
  background: #f8f9fa;
  border: 3px solid transparent;
  border-radius: 12px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s;
  text-align: center;
}

.option-card:hover {
  background: #fff0f5;
  border-color: #f5576c;
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(245, 87, 108, 0.2);
}

.option-card.selected {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  border-color: #f5576c;
  color: white;
}

.option-icon {
  font-size: 24px;
  margin-bottom: 5px;
  color: white;
}

.option-card.selected .option-icon {
  opacity: 1;
}

.option-name {
  font-size: 16px;
  font-weight: 600;
}

/* Input Empleado */
.input-empleado {
  width: 100%;
  padding: 15px 20px;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  font-size: 16px;
  transition: all 0.3s;
  outline: none;
  box-sizing: border-box;
}

.input-empleado:focus {
  border-color: #f5576c;
  box-shadow: 0 0 0 4px rgba(245, 87, 108, 0.1);
}

/* Footer */
.colacion-footer {
  background: #f8f9fa;
  padding: 20px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
}

.footer-info {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #28a745;
  font-weight: 600;
  font-size: 14px;
}

.footer-info i {
  font-size: 16px;
}

.btn-agregar {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  border: none;
  color: white;
  padding: 15px 30px;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-agregar:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(245, 87, 108, 0.3);
}

.btn-agregar:disabled {
  background: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

/* Scrollbar */
.colacion-body::-webkit-scrollbar {
  width: 8px;
}

.colacion-body::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.colacion-body::-webkit-scrollbar-thumb {
  background: #f5576c;
  border-radius: 10px;
}

.colacion-body::-webkit-scrollbar-thumb:hover {
  background: #e04060;
}
</style>
