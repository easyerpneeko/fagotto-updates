<template>
  <div class="arqueo-caja-container">
    <!-- Header -->
    <div class="page-header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h2 class="page-title">
            <i class="fas fa-cash-register me-3"></i>Arqueo de Caja
          </h2>
          <p class="page-subtitle">Control y registro de efectivo en caja</p>
        </div>
        <div class="header-actions">
          <span class="status-badge" :class="statusClass">
            <i class="fas fa-circle me-1"></i>{{ statusText }}
          </span>
          <button class="btn btn-outline-primary ms-2" @click="verReportes">
            <i class="fas fa-chart-bar me-1"></i>Reportes
          </button>
        </div>
      </div>
      
      <!-- Info del Arqueo -->
      <div class="arqueo-info mt-3">
        <div class="row">
          <div class="col-md-4">
            <div class="info-item">
              <i class="fas fa-store me-2"></i>
              <strong>Negocio:</strong> {{ negocioInfo.nombre }}
            </div>
          </div>
          <div class="col-md-4">
            <div class="info-item">
              <i class="fas fa-calendar me-2"></i>
              <strong>Fecha:</strong> {{ fechaActual }}
            </div>
          </div>
          <div class="col-md-4">
            <div class="info-item">
              <i class="fas fa-user me-2"></i>
              <strong>Usuario:</strong> {{ usuarioActual.nombre }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Panel de Conteo -->
      <div class="col-lg-8">
        <div class="card modern-card">
          <div class="card-body">
            <h4 class="card-title">
              <i class="fas fa-calculator me-2"></i>Conteo de Efectivo
            </h4>
            
            <!-- Billetes -->
            <div class="denomination-section">
              <h5 class="section-title">💵 Billetes</h5>
              <div class="row g-3">
                <div class="col-md-4" v-for="(valor, denominacion) in billetes" :key="denominacion">
                  <div class="denomination-card">
                    <div class="text-center">
                      <h6 class="denomination-value">${{ formatMoney(valor) }}</h6>
                      <input 
                        type="number" 
                        class="form-control denomination-input" 
                        v-model.number="conteo[denominacion]"
                        @input="calcularTotal"
                        min="0"
                        placeholder="0"
                      >
                      <small class="text-muted">Cantidad</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Monedas -->
            <div class="denomination-section">
              <h5 class="section-title">🪙 Monedas</h5>
              <div class="row g-3">
                <div class="col-md-3" v-for="(valor, denominacion) in monedas" :key="denominacion">
                  <div class="denomination-card">
                    <div class="text-center">
                      <h6 class="denomination-value">${{ formatMoney(valor) }}</h6>
                      <input 
                        type="number" 
                        class="form-control denomination-input" 
                        v-model.number="conteo[denominacion]"
                        @input="calcularTotal"
                        min="0"
                        placeholder="0"
                      >
                      <small class="text-muted">Cantidad</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Otros Medios de Pago -->
            <div class="denomination-section">
              <h5 class="section-title">💳 Otros Medios de Pago</h5>
              <p class="text-muted small mb-3">
                <i class="fas fa-user-secret me-1"></i>
                Ingresa lo que "tienes" de cada medio de pago (sin ver las ventas del sistema)
              </p>
              <div class="row g-3">
                <div 
                  class="col-md-4" 
                  v-for="method in availablePaymentMethods" 
                  :key="method.key"
                  v-if="method.key !== 'efectivo'"
                >
                  <div class="payment-method-card">
                    <div class="text-center">
                      <i 
                        :class="`${method.icon} text-${method.color} mb-2`" 
                        style="font-size: 1.5rem;"
                      ></i>
                      <h6 class="payment-method-name">{{ method.emoji }} {{ method.name }}</h6>
                      <input 
                        type="number" 
                        class="form-control payment-input" 
                        v-model.number="mediosPago[method.key]"
                        @input="calcularTotalGeneral"
                        min="0"
                        step="0.01"
                        placeholder="0.00"
                      >
                      <small class="text-muted">Monto Total</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total General -->
            <div class="total-section">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h4 class="mb-0 text-white">Total Efectivo:</h4>
                  <h4 class="mb-0 text-white">Total Otros Medios:</h4>
                  <h3 class="mb-0 text-white border-top pt-2">TOTAL GENERAL:</h3>
                </div>
                <div class="col-md-6 text-end">
                  <h4 class="mb-0 text-white">${{ formatMoney(totalContado) }}</h4>
                  <h4 class="mb-0 text-white">${{ formatMoney(totalOtrosMedios) }}</h4>
                  <h2 class="mb-0 text-white border-top pt-2">${{ formatMoney(totalGeneralContado) }}</h2>
                </div>
              </div>
            </div>

            <!-- Observaciones -->
            <div class="mt-4">
              <label for="observaciones" class="form-label">Observaciones</label>
              <textarea 
                class="form-control" 
                v-model="observaciones" 
                rows="3" 
                placeholder="Ingrese cualquier observación sobre el arqueo..."
              ></textarea>
            </div>

            <!-- Botones -->
            <div class="mt-4 text-center">
              <button 
                class="btn btn-primary btn-lg me-3" 
                @click="guardarArqueo"
                :disabled="loading || totalContado === 0"
              >
                <i class="fas fa-save me-2"></i>
                {{ loading ? 'Guardando...' : 'Guardar Arqueo' }}
              </button>
              <button class="btn btn-outline-secondary me-3" @click="limpiarFormulario">
                <i class="fas fa-broom me-2"></i>Limpiar
              </button>
              <button class="btn btn-outline-primary" @click="imprimirArqueo">
                <i class="fas fa-print me-2"></i>Imprimir
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Panel de Información -->
      <div class="col-lg-4">
        <!-- Resumen del Día - Solo se muestra DESPUÉS de guardar -->
        <div class="card modern-card" v-if="arqueoGuardado">
          <div class="card-body">
            <h5 class="card-title">
              <i class="fas fa-detective me-2"></i>🕵️‍♂️ Resultado de la Investigación
            </h5>
            <div class="alert" :class="alertaResultadoClass">
              <h6 class="mb-2">
                <i :class="iconoResultado" class="me-2"></i>
                {{ mensajeResultado }}
              </h6>
              <p class="mb-0">¡Comparando lo que dijiste tener vs. las ventas reales!</p>
            </div>
            
            <!-- EFECTIVO -->
            <div class="card border-primary mb-3">
              <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>💵 EFECTIVO</h6>
              </div>
              <div class="card-body">
                <div class="summary-item">
                  <div class="d-flex justify-content-between">
                    <span>Dijiste que tienes:</span>
                    <strong class="text-info">${{ formatMoney(totalContado) }}</strong>
                  </div>
                </div>
                <div class="summary-item">
                  <div class="d-flex justify-content-between">
                    <span>Ventas reales:</span>
                    <strong class="text-success">${{ formatMoney(getPaymentMethodSales('efectivo')) }}</strong>
                  </div>
                </div>
                <div class="summary-item border-top pt-2">
                  <div class="d-flex justify-content-between">
                    <span><strong>Diferencia:</strong></span>
                    <strong :class="diferencia >= 0 ? 'text-success' : 'text-danger'">${{ formatMoney(diferencia) }}</strong>
                  </div>
                </div>
              </div>
            </div>

            <!-- OTROS MEDIOS DE PAGO (DINÁMICO) -->
            <div 
              v-for="method in availablePaymentMethods" 
              :key="method.key"
              v-if="method.key !== 'efectivo'"
              :class="`card border-${method.color} mb-3`"
            >
              <div :class="`card-header bg-${method.color} text-white`">
                <h6 class="mb-0">
                  <i :class="`${method.icon} me-2`"></i>
                  {{ method.emoji }} {{ method.name.toUpperCase() }}
                </h6>
              </div>
              <div class="card-body">
                <div class="summary-item">
                  <div class="d-flex justify-content-between">
                    <span>Dijiste que tienes:</span>
                    <strong class="text-info">${{ formatMoney(mediosPago[method.key] || 0) }}</strong>
                  </div>
                </div>
                <div class="summary-item">
                  <div class="d-flex justify-content-between">
                    <span>Ventas reales:</span>
                    <strong class="text-success">${{ formatMoney(getPaymentMethodSales(method.key)) }}</strong>
                  </div>
                </div>
                <div class="summary-item border-top pt-2">
                  <div class="d-flex justify-content-between">
                    <span><strong>Diferencia:</strong></span>
                    <strong :class="getPaymentMethodDifference(method.key) >= 0 ? 'text-success' : 'text-danger'">
                      ${{ formatMoney(getPaymentMethodDifference(method.key)) }}
                    </strong>
                  </div>
                </div>
              </div>
            </div>

            <!-- TOTALES GENERALES -->
            <div class="card border-dark">
              <div class="card-header bg-dark text-white">
                <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>RESUMEN GENERAL</h6>
              </div>
              <div class="card-body">
                <div class="summary-item">
                  <div class="d-flex justify-content-between">
                    <span>Total que dijiste:</span>
                    <strong class="text-info">${{ formatMoney(totalGeneralContado) }}</strong>
                  </div>
                </div>
                <div class="summary-item">
                  <div class="d-flex justify-content-between">
                    <span>Total ventas reales:</span>
                    <strong class="text-success">${{ formatMoney(totalVentasSistema) }}</strong>
                  </div>
                </div>
                <div class="summary-item border-top pt-2">
                  <div class="d-flex justify-content-between">
                    <span><strong>Diferencia TOTAL:</strong></span>
                    <h5 :class="diferenciaGeneral >= 0 ? 'text-success' : 'text-danger'">
                      ${{ formatMoney(diferenciaGeneral) }}
                    </h5>
                  </div>
                </div>
                <div class="text-center mt-3">
                  <span class="badge badge-lg" 
                        :class="diferenciaGeneral === 0 ? 'bg-success' : (diferenciaGeneral > 0 ? 'bg-warning' : 'bg-danger')">
                    {{ diferenciaGeneral === 0 ? '🎉 ¡PERFECTO!' : (diferenciaGeneral > 0 ? '🤔 SOBRANTE' : '😱 FALTANTE') }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Mensaje de instrucción ANTES de guardar -->
        <div class="card modern-card" v-else>
          <div class="card-body text-center">
            <h5 class="card-title">
              <i class="fas fa-user-secret me-2"></i>🕵️‍♂️ Modo Detectivo
            </h5>
            <div class="alert alert-info">
              <i class="fas fa-eye-slash me-2"></i>
              <strong>Cuenta todo sin ver las ventas del sistema</strong>
              <p class="mb-2 mt-2">Para pillar cualquier discrepancia, ingresa lo que "tienes" de cada medio de pago.</p>
              <p class="mb-0"><strong>La comparación se revelará después de guardar... 🔍</strong></p>
            </div>
            <div class="mt-3">
              <h6>Total Contado Hasta Ahora:</h6>
              <h4 class="text-primary">${{ formatMoney(totalContado) }}</h4>
              <h6>Total Otros Medios:</h6>
              <h4 class="text-info">${{ formatMoney(totalOtrosMedios) }}</h4>
              <hr>
              <h5 class="text-success">TOTAL GENERAL: ${{ formatMoney(totalGeneralContado) }}</h5>
            </div>
          </div>
        </div>

        <!-- Historial de Arqueos -->
        <div class="card modern-card">
          <div class="card-body">
            <h5 class="card-title">
              <i class="fas fa-history me-2"></i>Últimos Arqueos
            </h5>
            <div class="historial-container">
              <div v-if="historial.length === 0" class="text-center text-muted py-3">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p>No hay arqueos registrados</p>
              </div>
              <div v-else>
                <div v-for="arqueo in historial" :key="arqueo.id" class="historial-item">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <span class="fw-bold">${{ formatMoney(arqueo.total_contado) }}</span>
                      <span class="badge ms-2" :class="getEstadoBadgeClass(arqueo.diferencia)">
                        {{ getEstadoText(arqueo.diferencia) }}
                      </span>
                    </div>
                    <small class="text-muted">
                      {{ formatDate(arqueo.created_at) }}<br>
                      {{ formatTime(arqueo.created_at) }}
                    </small>
                  </div>
                  <small class="text-muted d-block">
                    Diferencia: ${{ formatMoney(arqueo.diferencia) }}
                  </small>
                  <small class="text-muted">
                    Usuario: {{ arqueo.usuario_nombre || 'Sistema' }}
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmación -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmar Arqueo</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>¿Está seguro de guardar el arqueo con los siguientes datos?</p>
            <div class="alert alert-info">
              <h6><i class="fas fa-info-circle me-2"></i>Resumen del Conteo</h6>
              <div class="row">
                <div class="col-md-6">
                  <ul class="mb-0">
                    <li><strong>Efectivo:</strong> ${{ formatMoney(totalContado) }}</li>
                    <li><strong>T. Débito:</strong> ${{ formatMoney(mediosPago.tarjeta_debito) }}</li>
                    <li><strong>T. Crédito:</strong> ${{ formatMoney(mediosPago.tarjeta_credito) }}</li>
                  </ul>
                </div>
                <div class="col-md-6">
                  <ul class="mb-0">
                    <li><strong>Transferencia:</strong> ${{ formatMoney(mediosPago.transferencia) }}</li>
                    <li><strong>Cheque:</strong> ${{ formatMoney(mediosPago.cheque) }}</li>
                    <li><strong>Otros:</strong> ${{ formatMoney(mediosPago.vale_vista + mediosPago.otro) }}</li>
                  </ul>
                </div>
              </div>
              <hr>
              <div class="text-center">
                <strong>TOTAL GENERAL: ${{ formatMoney(totalGeneralContado) }}</strong>
              </div>
            </div>
            <p class="text-muted small">
              <i class="fas fa-user-secret me-1"></i>
              La comparación con las ventas reales del sistema se mostrará después de confirmar... 🕵️‍♂️
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" @click="confirmarGuardado">Confirmar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import PaymentMethodsHelper from '../helpers/PaymentMethodsHelper.js'

export default {
  name: 'ArqueoCaja',
  data() {
    return {
      // Denominaciones
      billetes: {
        'bill_20000': 20000,
        'bill_10000': 10000,
        'bill_5000': 5000,
        'bill_2000': 2000,
        'bill_1000': 1000
      },
      monedas: {
        'coin_500': 500,
        'coin_100': 100,
        'coin_50': 50,
        'coin_10': 10
      },
      
      // Conteo actual - EFECTIVO
      conteo: {
        'bill_20000': 0,
        'bill_10000': 0,
        'bill_5000': 0,
        'bill_2000': 0,
        'bill_1000': 0,
        'coin_500': 0,
        'coin_100': 0,
        'coin_50': 0,
        'coin_10': 0
      },
      
      // Métodos de pago disponibles (se carga dinámicamente)
      availablePaymentMethods: [],
      
      // Conteo de OTROS MEDIOS DE PAGO (inicializado dinámicamente)
      mediosPago: {},
      
      // Ventas del sistema por método de pago (dinámico)
      ventasPorMedio: {},
      
      // Datos del día - SE REVELAN DESPUÉS (legacy para compatibilidad)
      totalContado: 0,
      ventasEfectivo: 0,
      ventasTarjetaDebito: 0,
      ventasTarjetaCredito: 0,
      ventasTransferencia: 0,
      ventasCheque: 0,
      ventasValeVista: 0,
      ventasOtro: 0,
      totalVentas: 0,
      diferencia: 0,
      observaciones: '',
      
      // Estado
      loading: false,
      historial: [],
      statusText: 'Caja Abierta',
      statusClass: 'status-abierto',
      arqueoGuardado: false, // Nueva propiedad para controlar visibilidad
      
      // Información del sistema
      fechaActual: '',
      usuarioActual: {
        id: null,
        nombre: 'Cargando...'
      },
      negocioInfo: {
        id: null,
        nombre: 'Cargando...'
      }
    }
  },
  
  computed: {
    diferenciaClass() {
      if (this.diferencia === 0) return 'text-success';
      if (this.diferencia > 0) return 'text-warning';
      return 'text-danger';
    },
    
    // Nuevas computed properties para el resultado del arqueo
    mensajeResultado() {
      if (this.diferencia === 0) {
        return 'Arqueo Exacto ✅';
      } else if (this.diferencia > 0) {
        return 'Hay Sobrante ⚠️';
      } else {
        return 'Hay Faltante ❌';
      }
    },
    
    explicacionResultado() {
      if (this.diferencia === 0) {
        return 'El dinero contado coincide exactamente con las ventas en efectivo.';
      } else if (this.diferencia > 0) {
        return `Hay $${this.formatMoney(Math.abs(this.diferencia))} más de lo esperado según las ventas.`;
      } else {
        return `Faltan $${this.formatMoney(Math.abs(this.diferencia))} según las ventas registradas.`;
      }
    },
    
    iconoResultado() {
      if (this.diferencia === 0) {
        return 'fas fa-check-circle text-success';
      } else if (this.diferencia > 0) {
        return 'fas fa-exclamation-triangle text-warning';
      } else {
        return 'fas fa-times-circle text-danger';
      }
    },
    
    alertaResultadoClass() {
      if (this.diferencia === 0) {
        return 'alert-success';
      } else if (this.diferencia > 0) {
        return 'alert-warning';
      } else {
        return 'alert-danger';
      }
    },
    
    // Nuevas computed properties para totales de medios de pago
    totalOtrosMedios() {
      let total = 0;
      for (const method of this.availablePaymentMethods) {
        if (method.key !== 'efectivo' && this.mediosPago[method.key]) {
          total += (this.mediosPago[method.key] || 0);
        }
      }
      return total;
    },
    
    totalGeneralContado() {
      return this.totalContado + this.totalOtrosMedios;
    },
    
    // Total de ventas por cada medio de pago (solo visible después de guardar)
    totalVentasSistema() {
      let total = 0;
      // Incluir efectivo
      total += this.getPaymentMethodSales('efectivo');
      
      // Incluir todos los otros métodos de pago
      for (const method of this.availablePaymentMethods) {
        if (method.key !== 'efectivo') {
          total += this.getPaymentMethodSales(method.key);
        }
      }
      return total;
    },
    
    // Diferencias por cada medio de pago
    diferenciaTarjetaDebito() {
      return (this.mediosPago.tarjeta_debito || 0) - this.ventasTarjetaDebito;
    },
    
    diferenciaTarjetaCredito() {
      return (this.mediosPago.tarjeta_credito || 0) - this.ventasTarjetaCredito;
    },
    
    diferenciaTransferencia() {
      return (this.mediosPago.transferencia || 0) - this.ventasTransferencia;
    },
    
    diferenciaCheque() {
      return (this.mediosPago.cheque || 0) - this.ventasCheque;
    },
    
    diferenciaValeVista() {
      return (this.mediosPago.vale_vista || 0) - this.ventasValeVista;
    },
    
    diferenciaOtro() {
      return (this.mediosPago.otro || 0) - this.ventasOtro;
    },
    
    diferenciaGeneral() {
      return this.totalGeneralContado - this.totalVentasSistema;
    }
  },
  
  mounted() {
    this.initializePaymentMethods();
    this.inicializarFecha();
    this.cargarInfoUsuario();
    this.cargarInfoNegocio();
    this.cargarHistorial();
    // NO cargamos el resumen del día hasta después de guardar
  },
  
  methods: {
    // Inicializar métodos de pago dinámicamente
    initializePaymentMethods() {
      this.availablePaymentMethods = PaymentMethodsHelper.getActivePaymentMethods();
      this.mediosPago = PaymentMethodsHelper.initializePaymentCounts(this.availablePaymentMethods);
      
      // Asegurar que efectivo esté incluido
      if (!this.mediosPago.hasOwnProperty('efectivo')) {
        this.mediosPago.efectivo = 0;
      }
    },

    // Helper para obtener información de display del método de pago
    getPaymentMethodDisplay(methodKey) {
      return PaymentMethodsHelper.getPaymentMethodDisplay(methodKey);
    },

    // Helper para obtener clase de badge
    getPaymentMethodBadgeClass(methodKey) {
      return PaymentMethodsHelper.getBadgeClass(methodKey);
    },

    // Helper para obtener diferencia por método de pago
    getPaymentMethodDifference(methodKey) {
      const contado = this.mediosPago[methodKey] || 0;
      const ventas = this.ventasPorMedio[methodKey] || 0;
      return contado - ventas;
    },

    // Helper para obtener ventas del sistema por método
    getPaymentMethodSales(methodKey) {
      return this.ventasPorMedio[methodKey] || 0;
    },

    // Ya no carga automáticamente el resumen del día
    async cargarDatosIniciales() {
      // Solo carga historial en el mounted, resumen después de guardar
      await this.cargarHistorial();
      this.calcularTotal();
    },
    
    inicializarFecha() {
      const hoy = new Date();
      this.fechaActual = hoy.toLocaleDateString('es-CL', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    
    async cargarInfoUsuario() {
      try {
        const response = await axios.get('/local/me');
        if (response.data && response.data.success) {
          this.usuarioActual = {
            id: response.data.user.id,
            nombre: response.data.user.name || response.data.user.username || 'Usuario'
          };
        }
      } catch (error) {
        console.error('Error al cargar usuario:', error);
        this.usuarioActual.nombre = 'Usuario no identificado';
      }
    },
    
    async cargarInfoNegocio() {
      try {
        // Intentar obtener desde la API primero
        const response = await axios.get('/local/negocio/info');
        if (response.data.success) {
          this.negocioInfo = response.data.data;
          return;
        }
      } catch (error) {
        console.error('Error al obtener info del negocio desde API:', error);
      }
      
      try {
        // Fallback: Intentar obtener info del negocio desde el store
        if (this.$store.getters['main/getEnvs']) {
          const envs = this.$store.getters['main/getEnvs'];
          this.negocioInfo = {
            id: envs.app_id || null,
            nombre: envs.app_name || envs.nombre_negocio || 'Mi Negocio',
            direccion: envs.direccion || '',
            telefono: envs.telefono || '',
            email: envs.email || ''
          };
        } else {
          // Fallback: obtener desde localStorage o configuración
          const appConfig = JSON.parse(localStorage.getItem('app-config') || '{}');
          this.negocioInfo = {
            id: appConfig.id || null,
            nombre: appConfig.name || appConfig.nombre || 'Mi Negocio',
            direccion: appConfig.direction || '',
            telefono: appConfig.phone || '',
            email: appConfig.email || ''
          };
        }
      } catch (error) {
        console.error('Error al cargar info del negocio:', error);
        this.negocioInfo = {
          id: null,
          nombre: 'Negocio no identificado',
          direccion: '',
          telefono: '',
          email: ''
        };
      }
    },
    
    async cargarResumenDia() {
      try {
        const today = new Date().toISOString().split('T')[0];
        const response = await axios.get(`/local/report/arqueo-resumen?startDate=${today}&endDate=${today}`);
        
        if (response.data.success) {
          // Cargar ventas dinámicamente por método de pago
          this.ventasPorMedio = response.data.mediosPago || {};
          
          // Mantener compatibilidad con código legacy
          this.ventasEfectivo = this.ventasPorMedio.efectivo || 0;
          this.ventasTarjetaDebito = this.ventasPorMedio.debito || 0;
          this.ventasTarjetaCredito = this.ventasPorMedio.credito || 0;
          this.ventasTransferencia = this.ventasPorMedio.transferencia || 0;
          this.ventasCheque = this.ventasPorMedio.cheque || 0;
          this.ventasValeVista = 0; // Legacy
          this.ventasOtro = 0; // Legacy
          this.totalVentas = response.data.totalVentas || 0;
          
          // Calcular diferencias
          this.calcularDiferencia();
        }
      } catch (error) {
        console.error('Error al cargar resumen del día:', error);
        this.showError('Error al cargar datos del día');
      }
    },
    
    async cargarHistorial() {
      try {
        const response = await axios.get('/local/arqueo/historial');
        
        if (response.data.success) {
          this.historial = response.data.data || [];
        }
      } catch (error) {
        console.error('Error al cargar historial:', error);
      }
    },
    
    calcularTotal() {
      let total = 0;
      const todasDenominaciones = { ...this.billetes, ...this.monedas };
      
      for (let denominacion in todasDenominaciones) {
        const cantidad = parseInt(this.conteo[denominacion]) || 0;
        total += cantidad * todasDenominaciones[denominacion];
      }
      
      this.totalContado = total;
      this.calcularDiferencia();
    },
    
    calcularDiferencia() {
      this.diferencia = this.totalContado - this.ventasEfectivo;
    },
    
    calcularTotalGeneral() {
      // Primero calcular el total de efectivo
      this.calcularTotal();
      // Los totales de otros medios se calculan automáticamente con computed properties
    },
    
    async guardarArqueo() {
      if (this.totalContado === 0) {
        this.showWarning('Debe ingresar al menos una denominación');
        return;
      }
      
      // Mostrar modal de confirmación
      const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
      modal.show();
    },
    
    async confirmarGuardado() {
      this.loading = true;
      
      try {
        // PRIMERO: Cargar las ventas del día para calcular la diferencia
        await this.cargarResumenDia();
        
        const detalleConteo = this.getDetalleConteo();
        const data = {
          total_contado: this.totalContado,
          detalle_conteo: JSON.stringify(detalleConteo),
          observaciones: this.observaciones,
          fecha_arqueo: new Date().toISOString().split('T')[0],
          usuario_id: this.usuarioActual.id,
          usuario_nombre: this.usuarioActual.nombre,
          negocio_id: this.negocioInfo.id,
          negocio_nombre: this.negocioInfo.nombre
        };
        
        const response = await axios.post('/local/arqueo/guardar', data);
        
        if (response.data.success) {
          // DESPUÉS de guardar exitosamente, mostrar los resultados
          this.arqueoGuardado = true;
          this.showSuccess('Arqueo guardado correctamente');
          await this.cargarHistorial();
          
          // Mostrar mensaje de resultado con un delay para que se vea el cambio
          setTimeout(() => {
            if (this.diferencia === 0) {
              this.showSuccess('¡Perfecto! El arqueo coincide exactamente con las ventas.');
            } else if (this.diferencia > 0) {
              this.showWarning(`Hay un sobrante de $${this.formatMoney(Math.abs(this.diferencia))}`);
            } else {
              this.showError(`Hay un faltante de $${this.formatMoney(Math.abs(this.diferencia))}`);
            }
          }, 1000);
          
        } else {
          this.showError(response.data.message || 'Error al guardar el arqueo');
        }
      } catch (error) {
        console.error('Error al guardar arqueo:', error);
        this.showError('Error al guardar el arqueo. Intente nuevamente.');
      } finally {
        this.loading = false;
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
        modal.hide();
      }
    },
    
    getDetalleConteo() {
      const detalle = {};
      const todasDenominaciones = { ...this.billetes, ...this.monedas };
      
      for (let denominacion in todasDenominaciones) {
        const cantidad = parseInt(this.conteo[denominacion]) || 0;
        if (cantidad > 0) {
          detalle[denominacion] = {
            cantidad: cantidad,
            valor: todasDenominaciones[denominacion],
            subtotal: cantidad * todasDenominaciones[denominacion]
          };
        }
      }
      
      return detalle;
    },
    
    limpiarFormulario() {
      // Limpiar conteo de efectivo
      for (let denominacion in this.conteo) {
        this.conteo[denominacion] = 0;
      }
      
      // Limpiar otros medios de pago
      this.mediosPago.tarjeta_debito = 0;
      this.mediosPago.tarjeta_credito = 0;
      this.mediosPago.transferencia = 0;
      this.mediosPago.cheque = 0;
      this.mediosPago.vale_vista = 0;
      this.mediosPago.otro = 0;
      
      // Limpiar observaciones
      this.observaciones = '';
      
      // Resetear estado para un nuevo arqueo
      this.arqueoGuardado = false;
      
      // Limpiar datos de ventas (se cargarán después de guardar)
      this.ventasEfectivo = 0;
      this.ventasTarjetaDebito = 0;
      this.ventasTarjetaCredito = 0;
      this.ventasTransferencia = 0;
      this.ventasCheque = 0;
      this.ventasValeVista = 0;
      this.ventasOtro = 0;
      this.totalVentas = 0;
      this.diferencia = 0;
      
      // Recalcular totales
      this.calcularTotalGeneral();
    },
    
    imprimirArqueo() {
      if (this.totalContado === 0) {
        this.showWarning('No hay datos para imprimir');
        return;
      }
      
      const printContent = this.generatePrintContent();
      const printWindow = window.open('', '_blank');
      printWindow.document.write(printContent);
      printWindow.document.close();
      printWindow.print();
    },
    
    generatePrintContent() {
      const fecha = new Date().toLocaleDateString('es-CL');
      const hora = new Date().toLocaleTimeString('es-CL');
      const detalle = this.getDetalleConteo();
      
      let detalleHTML = '';
      for (let denominacion in detalle) {
        const item = detalle[denominacion];
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
            .info-section { margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px; }
            .total { font-size: 18px; font-weight: bold; }
          </style>
        </head>
        <body>
          <div class="header">
            <h1>ARQUEO DE CAJA</h1>
            <p>Fecha: ${fecha} - Hora: ${hora}</p>
          </div>
          
          <div class="info-section">
            <h3>Información del Arqueo</h3>
            <p><strong>Negocio:</strong> ${this.negocioInfo.nombre}</p>
            <p><strong>Usuario:</strong> ${this.usuarioActual.nombre}</p>
            <p><strong>Fecha:</strong> ${this.fechaActual}</p>
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
            <p>Total Contado: $${this.formatMoney(this.totalContado)}</p>
            <p>Ventas en Efectivo: $${this.formatMoney(this.ventasEfectivo)}</p>
            <p>Diferencia: $${this.formatMoney(this.diferencia)}</p>
          </div>
          
          ${this.observaciones ? `<div><h3>Observaciones</h3><p>${this.observaciones}</p></div>` : ''}
          
          <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #666;">
            <p>Sistema de Arqueo de Caja - ${new Date().toLocaleString('es-CL')}</p>
          </div>
        </body>
        </html>
      `;
    },
    
    verReportes() {
      // Aquí podrías implementar la navegación a reportes
      this.showInfo('Función de reportes en desarrollo');
    },
    
    // Utilidades
    formatMoney(amount) {
      return parseInt(amount || 0).toLocaleString('es-CL');
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('es-CL');
    },
    
    formatTime(dateString) {
      return new Date(dateString).toLocaleTimeString('es-CL', {
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    getEstadoBadgeClass(diferencia) {
      if (diferencia === 0) return 'bg-success';
      if (diferencia > 0) return 'bg-warning';
      return 'bg-danger';
    },
    
    getEstadoText(diferencia) {
      if (diferencia === 0) return 'Exacto';
      if (diferencia > 0) return 'Sobrante';
      return 'Faltante';
    },
    
    // Notificaciones
    showSuccess(message) {
      this.$toast.success(message);
    },
    
    showError(message) {
      this.$toast.error(message);
    },
    
    showWarning(message) {
      this.$toast.warning(message);
    },
    
    showInfo(message) {
      this.$toast.info(message);
    }
  }
}
</script>

<style scoped>
.arqueo-caja-container {
  padding: 20px;
  background: linear-gradient(145deg, #f8fafc 0%, #e2e8f0 100%);
  min-height: 100vh;
}

.page-header {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  padding: 25px;
  border-radius: 16px;
  margin-bottom: 25px;
  box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
}

.page-title {
  margin: 0;
  font-size: 2rem;
  font-weight: 700;
}

.page-subtitle {
  margin: 5px 0 0 0;
  opacity: 0.8;
  font-size: 1.1rem;
}

.header-actions {
  display: flex;
  align-items: center;
}

.arqueo-info {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 15px;
  backdrop-filter: blur(10px);
}

.info-item {
  color: white;
  font-size: 0.95rem;
  margin-bottom: 5px;
}

.info-item i {
  color: rgba(255, 255, 255, 0.8);
}

.status-badge {
  display: inline-block;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
}

.status-abierto {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  border: 2px solid #10b981;
}

.modern-card {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  border: 1px solid #e2e8f0;
  margin-bottom: 25px;
  backdrop-filter: blur(10px);
}

.card-title {
  color: #1e293b;
  font-weight: 600;
  margin-bottom: 20px;
}

.denomination-section {
  margin-bottom: 30px;
}

.section-title {
  color: #64748b;
  font-weight: 600;
  margin-bottom: 15px;
  font-size: 1.2rem;
}

.denomination-card {
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
  background: #ffffff;
}

.denomination-card:hover {
  border-color: #3b82f6;
  box-shadow: 0 5px 15px rgba(59, 130, 246, 0.1);
  transform: translateY(-2px);
}

.denomination-value {
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 10px;
}

.denomination-input {
  font-size: 1.2rem;
  font-weight: 600;
  text-align: center;
  border: none;
  background: #f8fafc;
  border-radius: 8px;
  padding: 10px;
}

.denomination-input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  background: #ffffff;
}

.total-section {
  background: linear-gradient(135deg, #10b981, #059669);
  border-radius: 12px;
  padding: 25px;
  margin-top: 25px;
}

.summary-item {
  padding: 10px 0;
  border-bottom: 1px solid #f1f5f9;
}

.summary-item:last-child {
  border-bottom: none;
}

.historial-container {
  max-height: 400px;
  overflow-y: auto;
}

.historial-item {
  padding: 15px 0;
  border-bottom: 1px solid #f1f5f9;
}

.historial-item:last-child {
  border-bottom: none;
}

.btn {
  border-radius: 10px;
  font-weight: 600;
  padding: 12px 25px;
  transition: all 0.3s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  border: none;
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  transform: none;
}

/* Estilos para tarjetas de medios de pago */
.payment-method-card {
  background: linear-gradient(135deg, #f8fafc, #e2e8f0);
  border: 2px solid #e2e8f0;
  border-radius: 15px;
  padding: 20px;
  text-align: center;
  transition: all 0.3s ease;
  height: 100%;
}

.payment-method-card:hover {
  transform: translateY(-3px);
  border-color: #3b82f6;
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
}

.payment-method-name {
  color: #1e293b;
  font-weight: 600;
  margin-bottom: 10px;
  font-size: 0.9rem;
}

.payment-input {
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  padding: 12px;
  font-size: 1rem;
  font-weight: 600;
  text-align: center;
  transition: all 0.3s ease;
}

.payment-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
}

/* Estilos para badges de resultados */
.badge-lg {
  font-size: 1rem;
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: 600;
}

/* Animaciones para revelar resultados */
.reveal-animation {
  animation: slideInUp 0.5s ease-out;
}

@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Estilos para cards de resultados por medio de pago */
.card.border-primary .card-header { background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important; }
.card.border-warning .card-header { background: linear-gradient(135deg, #f59e0b, #d97706) !important; }
.card.border-success .card-header { background: linear-gradient(135deg, #10b981, #059669) !important; }
.card.border-info .card-header { background: linear-gradient(135deg, #06b6d4, #0891b2) !important; }
.card.border-dark .card-header { background: linear-gradient(135deg, #374151, #1f2937) !important; }

@media (max-width: 768px) {
  .arqueo-caja-container {
    padding: 15px;
  }
  
  .page-header {
    padding: 20px;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .header-actions {
    flex-direction: column;
    gap: 10px;
    align-items: stretch;
  }
  
  .denomination-card {
    padding: 15px;
  }
}
</style>
