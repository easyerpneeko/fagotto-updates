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

      <!-- Estado del Arqueo -->
      <div class="arqueo-estado mt-3" v-if="turnoActivo">
        <div class="row">
          <div class="col-md-6">
            <div class="estado-item inicial">
              <i class="fas fa-play-circle me-2"></i>
              <strong>Arqueo Inicial:</strong> 
              <span class="badge bg-success ms-2">✓ ${{ formatMoney(montoInicialTurno) }}</span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="estado-item final">
              <i class="fas fa-stop-circle me-2"></i>
              <strong>Arqueo Final:</strong> 
              <span class="badge bg-warning text-dark ms-2">⏳ Pendiente</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Estado del Arqueo COMPLETADO (se muestra después de guardar) -->
      <div class="arqueo-estado mt-3" v-if="arqueoGuardado && !turnoActivo">
        <div class="row">
          <div class="col-md-6">
            <div class="estado-item inicial">
              <i class="fas fa-play-circle me-2"></i>
              <strong>Arqueo Inicial:</strong> 
              <span class="badge bg-success ms-2">✓ ${{ formatMoney(montoInicialTurno) }}</span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="estado-item final">
              <i class="fas fa-check-circle me-2"></i>
              <strong>Arqueo Final:</strong> 
              <span class="badge bg-success ms-2">✓ ${{ formatMoney(montoFinalTurno) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Botón de Iniciar Turno -->
    <div class="row mb-3" v-if="!turnoActivo">
      <div class="col-12">
        <div class="alert alert-warning text-center">
          <h5><i class="fas fa-exclamation-triangle me-2"></i>Turno no iniciado</h5>
          <p class="mb-3">Debes iniciar un turno antes de hacer el arqueo de caja</p>
          <button 
            class="btn btn-success btn-lg"
            @click="iniciarTurno"
            :disabled="cargandoTurno"
          >
            <i class="fas fa-play me-2"></i>
            {{ cargandoTurno ? 'Iniciando Turno...' : 'Iniciar Turno' }}
          </button>
        </div>
      </div>
    </div>

    <div class="row" v-if="turnoActivo">
      <!-- Panel de Conteo (solo visible después de iniciar turno) -->
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
                        @focus="selectInputContent"
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
                        @focus="selectInputContent"
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
                  class="col-lg-3 col-md-4 col-sm-6" 
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
                        @focus="selectInputContent"
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
            <!-- Resumen Detallado por Método de Pago -->
            <div class="total-section">
              <h5 class="text-white mb-3">
                <i class="fas fa-calculator me-2"></i>Resumen Detallado
              </h5>
              
              <!-- EFECTIVO -->
              <div class="row align-items-center mb-2">
                <div class="col-8">
                  <span class="text-white">💵 Efectivo:</span>
                </div>
                <div class="col-4 text-end">
                  <strong class="text-white">${{ formatMoney(totalContado) }}</strong>
                </div>
              </div>

              <!-- TODOS LOS OTROS MÉTODOS DE PAGO -->
              <div 
                v-for="method in availablePaymentMethods" 
                :key="method.key"
                v-if="method.key !== 'efectivo'"
                class="row align-items-center mb-2"
              >
                <div class="col-8">
                  <span class="text-white">{{ method.emoji }} {{ method.name }}:</span>
                </div>
                <div class="col-4 text-end">
                  <strong class="text-white">${{ formatMoney(mediosPago[method.key] || 0) }}</strong>
                </div>
              </div>

              <!-- TOTAL GENERAL -->
              <hr class="text-white">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-0 text-white">🏆 TOTAL GENERAL:</h3>
                </div>
                <div class="col-4 text-end">
                  <h2 class="mb-0 text-white">${{ formatMoney(totalGeneralContado) }}</h2>
                </div>
              </div>
            </div>

            <!-- Observaciones -->
            <div class="mt-4">
              <label for="observaciones" class="form-label">Observaciones</label>
              <textarea 
                class="form-control" 
                v-model="observaciones" 
                @focus="selectInputContent"
                rows="3" 
                placeholder="Ingrese cualquier observación sobre el arqueo..."
              ></textarea>
            </div>

            <!-- Botones -->
            <div class="mt-4 text-center">
              <button 
                class="btn btn-danger btn-lg me-3" 
                @click="guardarArqueo"
                :disabled="loading || totalContado === 0"
              >
                <i class="fas fa-stop me-2"></i>
                {{ loading ? 'Cerrando Turno...' : 'Cerrar Turno' }}
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
                    <span><i class="fas fa-hand-paper me-1"></i>Contaste:</span>
                    <strong class="text-info">${{ formatMoney(mediosPago[method.key] || 0) }}</strong>
                  </div>
                </div>
                <div class="summary-item">
                  <div class="d-flex justify-content-between">
                    <span><i class="fas fa-chart-line me-1"></i>Ventas del sistema:</span>
                    <strong class="text-success">${{ formatMoney(getPaymentMethodSales(method.key)) }}</strong>
                  </div>
                </div>
                <div class="summary-item border-top pt-2">
                  <div class="d-flex justify-content-between">
                    <span><strong><i class="fas fa-balance-scale me-1"></i>Diferencia:</strong></span>
                    <strong :class="getPaymentMethodDifference(method.key) >= 0 ? 'text-success' : 'text-danger'">
                      ${{ formatMoney(getPaymentMethodDifference(method.key)) }}
                    </strong>
                  </div>
                  <small v-if="getPaymentMethodDifference(method.key) === 0" class="text-success">✅ Exacto</small>
                  <small v-else-if="getPaymentMethodDifference(method.key) > 0" class="text-warning">⬆️ Sobrante</small>
                  <small v-else class="text-danger">⬇️ Faltante</small>
                </div>
              </div>
            </div>

            <!-- RESUMEN GENERAL DETALLADO -->
            <div class="card border-dark">
              <div class="card-header bg-dark text-white">
                <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>RESUMEN GENERAL - DETALLADO</h6>
              </div>
              <div class="card-body">
                <!-- EFECTIVO -->
                <div class="summary-item">
                  <div class="d-flex justify-content-between">
                    <span><strong>💵 Efectivo:</strong></span>
                    <div class="text-end">
                      <div>Dijiste: <strong class="text-info">${{ formatMoney(totalContado) }}</strong></div>
                      <div>Sistema: <strong class="text-success">${{ formatMoney(getPaymentMethodSales('efectivo')) }}</strong></div>
                      <div>Diferencia: <strong :class="diferencia >= 0 ? 'text-success' : 'text-danger'">${{ formatMoney(diferencia) }}</strong></div>
                    </div>
                  </div>
                </div>

                <!-- TODOS LOS OTROS MÉTODOS -->
                <div 
                  v-for="method in availablePaymentMethods" 
                  :key="method.key"
                  v-if="method.key !== 'efectivo' && (mediosPago[method.key] > 0 || getPaymentMethodSales(method.key) > 0)"
                  class="summary-item border-top pt-2 mt-2"
                >
                  <div class="d-flex justify-content-between">
                    <span><strong>{{ method.emoji }} {{ method.name }}:</strong></span>
                    <div class="text-end">
                      <div>Dijiste: <strong class="text-info">${{ formatMoney(mediosPago[method.key] || 0) }}</strong></div>
                      <div>Sistema: <strong class="text-success">${{ formatMoney(getPaymentMethodSales(method.key)) }}</strong></div>
                      <div>Diferencia: <strong :class="getPaymentMethodDifference(method.key) >= 0 ? 'text-success' : 'text-danger'">${{ formatMoney(getPaymentMethodDifference(method.key)) }}</strong></div>
                    </div>
                  </div>
                </div>

                <!-- TOTALES FINALES -->
                <hr class="mt-3">
                <div class="summary-item">
                  <div class="d-flex justify-content-between">
                    <span><strong>🏆 TOTAL GENERAL:</strong></span>
                    <div class="text-end">
                      <div>Dijiste: <strong class="text-info">${{ formatMoney(totalGeneralContado) }}</strong></div>
                      <div>Sistema: <strong class="text-success">${{ formatMoney(totalVentasSistema) }}</strong></div>
                    </div>
                  </div>
                </div>
                <div class="summary-item border-top pt-2">
                  <div class="d-flex justify-content-between">
                    <span><strong>DIFERENCIA TOTAL:</strong></span>
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

        <!-- Historial de Arqueos -->
        <div class="card modern-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="card-title mb-0">
                <i class="fas fa-history me-2"></i>Historial de Arqueos
              </h5>
              <div class="text-muted small">
                <i class="fas fa-shield-alt me-1"></i>Registro protegido de auditoría
              </div>
            </div>
            
            <div class="historial-container">
              <div v-if="historial.length === 0" class="text-center text-muted py-4">
                <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">No hay arqueos registrados</p>
                <small>Los arqueos aparecerán aquí después de guardarlos</small>
              </div>
              <div v-else>
                <div v-for="(arqueo, index) in historial" :key="arqueo.id" class="historial-item">
                  <div class="historial-header d-flex justify-content-between align-items-start">
                    <div class="historial-info">
                      <div class="historial-fecha">
                        <i class="fas fa-calendar-day me-1"></i>
                        <strong>{{ formatDate(arqueo.fecha) }}</strong>
                        <span class="text-muted ms-2">{{ formatTime(arqueo.fecha) }}</span>
                      </div>
                      <div class="historial-usuario text-muted">
                        <i class="fas fa-user me-1"></i>
                        {{ arqueo.usuario_nombre }}
                      </div>
                    </div>
                    <div class="historial-actions">
                      <button 
                        class="btn btn-outline-primary btn-sm" 
                        @click="verDetalleArqueo(arqueo)"
                        title="Ver detalle completo"
                      >
                        <i class="fas fa-eye"></i> Ver Detalle
                      </button>
                    </div>
                  </div>
                  
                  <div class="historial-resumen mt-2">
                    <div class="row text-center">
                      <div class="col-6">
                        <small class="text-muted d-block">Arqueo Inicial</small>
                        <strong class="text-success">${{ formatMoney(arqueo.monto_inicial) }}</strong>
                      </div>
                      <div class="col-6">
                        <small class="text-muted d-block">Arqueo Final</small>
                        <strong class="text-info">${{ formatMoney(arqueo.total_general_contado) }}</strong>
                      </div>
                    </div>
                  </div>
                  
                  <div class="historial-estado mt-2 text-center">
                    <span class="badge bg-light text-dark">
                      <i class="fas fa-clock me-1"></i>
                      {{ formatDuration(arqueo.fecha_inicio, arqueo.fecha_termino) }}
                    </span>
                  </div>
                  
                  <div v-if="arqueo.observaciones" class="historial-observaciones mt-2">
                    <small class="text-muted">
                      <i class="fas fa-sticky-note me-1"></i>
                      {{ arqueo.observaciones }}
                    </small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para Monto Inicial -->
    <div v-if="mostrandoModalMontoInicial" class="modal-simple-overlay" @click="enfocarInputInicial">
      <div class="modal-simple-content" @click.stop>
        <h4 class="mb-3">💰 Monto Inicial del Turno</h4>
        <p>Ingrese el monto de efectivo con el que inicia el turno:</p>
        <input 
          v-model="montoInicialInput" 
          type="number" 
          class="form-control mb-3" 
          placeholder="0" 
          min="0" 
          step="0.01"
          autofocus
          @keyup.enter="confirmarMontoInicial"
          @focus="$event.target.select()"
          ref="montoInicialInputRef"
        >
        <div class="text-end">
          <button class="btn btn-secondary me-2" @click="cancelarMontoInicial">
            <i class="fas fa-times me-1"></i>Cancelar
          </button>
          <button class="btn btn-primary" @click="confirmarMontoInicial">
            <i class="fas fa-play me-1"></i>Iniciar Turno
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import PaymentMethodsHelper from '../helpers/PaymentMethodsHelper.js'
import Loader from '@/helpers/Loader'
import moment from 'moment'
import axios from 'axios'

export default {
  name: 'ArqueoCaja',
  data() {
    return {
      app: null,
      
      // Control básico de turnos
      turnoActivo: false,
      cargandoTurno: false,
      
      // Montos del turno
      montoInicialTurno: 0,
      montoFinalTurno: 0,
      
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
      horaInicio: null, // Hora de inicio del turno
      turnoId: null, // ID del turno guardado
      
      // Información del sistema
      fechaActual: '',
      usuarioActual: {
        id: null,
        nombre: 'Cargando...'
      },
      negocioInfo: {
        id: null,
        nombre: 'Cargando...'
      },
      
      // Control de turno
      horaInicio: null,
      turnoId: null,
      
      // Modales simples
      mostrandoModalMontoInicial: false,
      montoInicialInput: 0,
      resolverMontoInicial: null
    }
  },
  
  computed: {
    me: { 
      get() { return this.$store.getters['main/user']; } 
    },
    
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
      const totalEsperado = this.montoInicialTurno + this.getPaymentMethodSales('efectivo');
      if (this.diferencia === 0) {
        return `El dinero contado coincide exactamente con lo esperado. Monto inicial ($${this.formatMoney(this.montoInicialTurno)}) + Ventas ($${this.formatMoney(this.getPaymentMethodSales('efectivo'))}) = $${this.formatMoney(totalEsperado)}.`;
      } else if (this.diferencia > 0) {
        return `Hay $${this.formatMoney(Math.abs(this.diferencia))} más de lo esperado. Contaste $${this.formatMoney(this.totalContado)} pero deberías tener $${this.formatMoney(totalEsperado)}.`;
      } else {
        return `Faltan $${this.formatMoney(Math.abs(this.diferencia))}. Contaste $${this.formatMoney(this.totalContado)} pero deberías tener $${this.formatMoney(totalEsperado)}.`;
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
      return this.montoInicialTurno + this.totalContado + this.totalOtrosMedios;
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
  
  async beforeCreate() {
    var request = await this.$store.dispatch('main/refreshData', '?slim');
    this.app = request.data;
  },
  
  mounted() {
    console.log('🚀 Iniciando Arqueo de Caja...');
    this.horaInicio = new Date().toISOString(); // Marcar inicio del turno
    this.initializePaymentMethods();
    this.inicializarFecha();
    this.cargarInfoUsuario();
    this.cargarInfoNegocio();
    this.cargarHistorial();
    // Verificar si hay un turno activo antes de cargar otros datos
    this.verificarEstadoTurno();
    // NO cargamos el resumen del día hasta después de guardar
  },

  watch: {
    // Observar cambios en el modal de monto inicial para asegurar focus
    mostrandoModalMontoInicial(newVal) {
      if (newVal) {
        // Cuando se muestra el modal, asegurar que el input tenga focus
        this.$nextTick(() => {
          this.enfocarInputInicial();
        });
      }
    }
  },
  
  methods: {
    // Verificar si hay un turno activo al cargar la página
    async verificarEstadoTurno() {
      try {
        // Primero verificar localStorage como backup rápido
        const turnoLocal = localStorage.getItem('turnoActivo');
        const fechaLocal = localStorage.getItem('fechaTurno');
        const montoInicialLocal = localStorage.getItem('montoInicialTurno');
        const fechaHoy = new Date().toISOString().split('T')[0];
        
        // Limpiar turnos de días anteriores automáticamente
        if (fechaLocal && fechaLocal !== fechaHoy) {
          console.log('🧹 Limpiando turno de día anterior:', fechaLocal);
          localStorage.removeItem('turnoActivo');
          localStorage.removeItem('fechaTurno');
          localStorage.removeItem('horaInicioTurno');
          localStorage.removeItem('montoInicialTurno');
          this.turnoActivo = false;
          this.montoInicialTurno = 0;
          return;
        }
        
        // Si hay un turno local del día de hoy, usarlo temporalmente
        if (turnoLocal === 'true' && fechaLocal === fechaHoy) {
          this.turnoActivo = true;
          this.montoInicialTurno = parseFloat(montoInicialLocal) || 0;
          console.log('✅ Turno activo encontrado en localStorage para hoy');
          console.log('💰 Monto inicial recuperado:', this.montoInicialTurno);
        }
        
        // TODO: Aquí podríamos verificar con el backend también
        // const response = await axios.get('/api/turnos/estado');
        // if (response.data.turno_activo) {
        //   this.turnoActivo = true;
        // }
        
      } catch (error) {
        console.error('Error verificando estado del turno:', error);
        // En caso de error, asumir que no hay turno activo
        this.turnoActivo = false;
        this.montoInicialTurno = 0;
      }
    },

    // Método mejorado para iniciar turno
    async iniciarTurno() {
      try {
        // Primero solicitar el monto inicial
        const montoInicial = await this.solicitarMontoInicial();
        if (montoInicial === null) {
          // Usuario canceló
          return;
        }
        
        this.cargandoTurno = true;
        
        // Guardar monto inicial
        this.montoInicialTurno = montoInicial;
        
        // Guardar estado en localStorage como backup
        const fechaHoy = new Date().toISOString().split('T')[0];
        localStorage.setItem('turnoActivo', 'true');
        localStorage.setItem('fechaTurno', fechaHoy);
        localStorage.setItem('horaInicioTurno', new Date().toISOString());
        localStorage.setItem('montoInicialTurno', montoInicial.toString());
        
        // Por ahora, solo simulamos el inicio del turno
        // Después conectaremos con el backend
        setTimeout(() => {
          this.turnoActivo = true;
          this.cargandoTurno = false;
          this.$awn.success(`Turno iniciado con $${this.formatMoney(montoInicial)}`, { labels: { success: 'TURNO ACTIVO' } });
          
          // Notificar al layout que el turno cambió
          this.notificarCambioTurno();
          
          // Cargar datos normalmente después de iniciar turno
          this.cargarResumenDia();
        }, 1000);
        
      } catch (error) {
        console.error('Error iniciando turno:', error);
        this.$awn.alert('Error al iniciar turno');
        this.cargandoTurno = false;
      }
    },

    // Función para solicitar el monto inicial - VERSIÓN VUE
    async solicitarMontoInicial() {
      return new Promise((resolve) => {
        this.montoInicialInput = 0;
        this.resolverMontoInicial = resolve;
        this.mostrandoModalMontoInicial = true;
        
        // Focus al input con múltiples intentos para asegurar que funcione
        this.$nextTick(() => {
          this.enfocarInputInicial();
        });
      });
    },

    // Método dedicado para enfocar el input inicial con reintentos
    enfocarInputInicial() {
      const intentarFocus = () => {
        const input = this.$refs.montoInicialInputRef;
        if (input) {
          input.focus();
          input.select(); // Selecciona todo el texto para facilitar escritura
          return true;
        }
        return false;
      };

      // Primer intento inmediato
      if (!intentarFocus()) {
        // Si falla, reintenta después de 50ms
        setTimeout(() => {
          if (!intentarFocus()) {
            // Si aún falla, reintenta después de 100ms más
            setTimeout(() => {
              intentarFocus();
            }, 100);
          }
        }, 50);
      }
    },

    // Método para navegar entre inputs con Enter - MEJORADO
    focusNextInput(event) {
      try {
        const currentInput = event.target;
        
        // Esperar a que el DOM esté listo
        this.$nextTick(() => {
          const allInputs = Array.from(document.querySelectorAll('input[type="number"], textarea'));
          const currentIndex = allInputs.indexOf(currentInput);
          
          if (currentIndex >= 0 && currentIndex < allInputs.length - 1) {
            // Enfocar el siguiente input
            const nextInput = allInputs[currentIndex + 1];
            if (nextInput) {
              nextInput.focus();
              nextInput.select();
            }
          }
        });
      } catch (error) {
        console.log('Error navegando inputs:', error);
        // Silencioso - no romper la funcionalidad
      }
    },

    // Método seguro para seleccionar contenido de input
    selectInputContent(event) {
      try {
        // Usar setTimeout para asegurar que el input esté listo
        setTimeout(() => {
          if (event.target && typeof event.target.select === 'function') {
            event.target.select();
          }
        }, 10);
      } catch (error) {
        // Silencioso - no romper la funcionalidad
      }
    },

    // Confirmar monto inicial
    confirmarMontoInicial() {
      const monto = parseFloat(this.montoInicialInput) || 0;
      this.mostrandoModalMontoInicial = false;
      if (this.resolverMontoInicial) {
        this.resolverMontoInicial(monto);
        this.resolverMontoInicial = null;
      }
    },

    // Cancelar monto inicial
    cancelarMontoInicial() {
      this.mostrandoModalMontoInicial = false;
      if (this.resolverMontoInicial) {
        this.resolverMontoInicial(null);
        this.resolverMontoInicial = null;
      }
    },

    // Helper para cerrar modales manualmente
    cerrarModal(modalId) {
      const overlay = document.querySelector('.modal-overlay');
      if (overlay) {
        overlay.remove();
      }
      
      // Remover cualquier backdrop que pueda quedar
      const backdrops = document.querySelectorAll('.modal-backdrop');
      backdrops.forEach(backdrop => backdrop.remove());
      
      // Limpiar funciones globales
      delete window.confirmarMontoInicial;
      delete window.cancelarMontoInicial;
      delete window.confirmarMontoFinal;
      delete window.cancelarMontoFinal;
    },
    initializePaymentMethods() {
      this.availablePaymentMethods = PaymentMethodsHelper.getActivePaymentMethods();
      console.log('🔥 Métodos de pago disponibles:', this.availablePaymentMethods.length, this.availablePaymentMethods);
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
        // Obtener datos del usuario desde el store (igual que pedidos.vue)
        this.usuarioActual = {
          id: this.me.id,
          nombre: this.me.fullname || this.me.username || 'Usuario'
        };
      } catch (error) {
        console.error('Error al cargar info de usuario:', error);
        this.$awn.alert('Error al cargar información del usuario');
      }
    },
    
    async cargarInfoNegocio() {
      try {
        // Obtener datos del negocio desde el store (igual que pedidos.vue)
        var request = await this.$store.dispatch('main/refreshData', '?slim');
        console.log('Datos del negocio recibidos:', request.data);
        
        this.negocioInfo = {
          id: request.data.Id,
          nombre: request.data.name_public || request.data.Name || request.data.name || 'Negocio Local',
          direccion: request.data.Address || 'No especificada',
          telefono: request.data.Phone || '',
          email: request.data.Email || ''
        };
        
        console.log('negocioInfo configurado:', this.negocioInfo);
      } catch (error) {
        console.error('Error al cargar info de negocio:', error);
        this.$awn.alert('Error al cargar información del negocio');
      }
    },
    
    async cargarResumenDia() {
      try {
        console.log('🔄 Cargando resumen del día...');
        // TODO: Implementar store action para arqueo
        // const fecha = new Date().toISOString().split('T')[0];
        // const params = `?startDate=${fecha}&endDate=${fecha}`;
        // const request = await this.$store.dispatch('arqueo/obtenerResumenDia', params);
        
        // Por ahora usar datos vacíos para evitar crashes
        this.initializeEmptyResumen();
        console.log('✅ Resumen del día inicializado con datos vacíos');
        
      } catch (error) {
        console.error('Error al cargar resumen del día:', error);
        // FALLBACK: Datos demo para desarrollo
        this.initializeEmptyResumen();
      }
    },

    initializeEmptyResumen() {
      this.ventasPorMedio = {};
      this.ventasEfectivo = 0;
      this.ventasTarjetaDebito = 0;
      this.ventasTarjetaCredito = 0;
      this.ventasTransferencia = 0;
      this.ventasCheque = 0;
      this.ventasValeVista = 0;
      this.ventasOtro = 0;
      this.totalVentas = 0;
      this.calcularDiferencia();
    },
    
    async cargarHistorial() {
      try {
        console.log('🔄 Cargando historial de arqueos...');
        // TODO: Implementar store action para historial
        // const params = '?page=1&limit=10';
        // const request = await this.$store.dispatch('arqueo/obtenerArqueos', params);
        
        // Por ahora usar historial local
        this.cargarHistorialLocal();
        console.log('✅ Historial cargado desde localStorage');
        
      } catch (error) {
        console.error('Error al cargar historial:', error);
        this.historial = [];
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
      // CORREGIDO: Ahora considera el monto inicial
      // El total que DEBERÍAS tener = Monto inicial + Ventas en efectivo
      const totalEsperado = this.montoInicialTurno + this.ventasEfectivo;
      this.diferencia = this.totalContado - totalEsperado;
    },
    
    calcularTotalGeneral() {
      // Primero calcular el total de efectivo
      this.calcularTotal();
      // Los totales de otros medios se calculan automáticamente con computed properties
    },
    
    async guardarArqueo() {
      if (this.totalContado === 0) {
        this.$awn.alert('Debe ingresar al menos una denominación');
        return;
      }
      
      // Mostrar modal de confirmación personalizado
      this.mostrarModalConfirmacion();
    },

    mostrarModalConfirmacion() {
      // Usar confirm nativo - funciona en Electron
      
      // Construir detalle de otros medios de pago
      let detalleOtrosMedios = '';
      for (const method of this.availablePaymentMethods) {
        if (method.key !== 'efectivo' && this.mediosPago[method.key] && this.mediosPago[method.key] > 0) {
          detalleOtrosMedios += `${method.emoji} ${method.name}: $${this.formatMoney(this.mediosPago[method.key])}\n`;
        }
      }
      
      const mensaje = `¿Está seguro de guardar el arqueo?\n\n` +
        `🏁 Caja inicial: $${this.formatMoney(this.montoInicialTurno)}\n` +
        `💵 Efectivo contado: $${this.formatMoney(this.totalContado)}\n` +
        detalleOtrosMedios +
        `🏆 TOTAL: $${this.formatMoney(this.totalGeneralContado)}\n\n` +
        `La comparación con las ventas se mostrará después...`;
      
      if (window.confirm(mensaje)) {
        this.confirmarGuardado();
      }
    },
    
    async confirmarGuardado() {
      try {
        // Ya tenemos el monto final calculado del conteo de billetes y monedas
        this.montoFinalTurno = this.totalContado;

        this.loading = true;
        
        // PRIMERO: Cargar las ventas del día para calcular la diferencia
        await this.cargarResumenDia();
        
        // Preparar datos del arqueo siguiendo el patrón de pedidos.vue
        const detalleConteo = this.getDetalleConteo();
        
        console.log('Debug info antes de enviar:', {
          negocioInfo: this.negocioInfo,
          usuarioActual: this.usuarioActual
        });
        
        const data = {
          app_id: 58, // ID fijo del negocio que estamos viendo en los logs
          usuario_id: this.usuarioActual.id,
          usuario_nombre: this.usuarioActual.nombre,
          app_nombre: 'Por determinar', // Lo obtendremos desde la BD
          fecha_inicio: new Date().toISOString(),
          fecha_termino: new Date().toISOString(),
          total_sistema: this.montoInicialTurno + this.totalVentasSistema, // ⚠️ SUMA: Monto inicial + ventas sistema
          total_contado: this.totalContado,
          diferencia: this.diferencia,
          estado: this.diferencia === 0 ? 'perfecto' : (this.diferencia > 0 ? 'sobrante' : 'faltante'),
          observaciones: this.observaciones,
          detalle_efectivo: JSON.stringify(detalleConteo),
          detalle_medios_pago: JSON.stringify(this.mediosPago),
          numero_transacciones: (this.resumenDia && this.resumenDia.total_transacciones) || 0
        };
        
        // Crear FormData siguiendo el patrón de pedidos.vue
        var formData = new FormData();
        for (let key in data) {
          if (data[key] !== null && data[key] !== undefined) {
            formData.append(key, data[key]);
          }
        }
        
        // Usar store dispatch siguiendo el patrón de pedidos.vue
        // TODO: Implementar store action para guardar arqueo
        // let request = await this.$store.dispatch('arqueo/guardarArqueo', formData);
        
        // Por ahora simular guardado exitoso
        console.log('💾 Simulando guardado de arqueo:', data);
        let request = { 
          success: true, 
          data: { id: Date.now(), message: 'Arqueo guardado correctamente (simulado)' } 
        };
        
        if (request.success) {
          this.arqueoGuardado = true;
          this.$awn.success('Arqueo guardado correctamente', { labels: { success: 'CORRECTO' } });
          
          // Guardar en historial local
          this.guardarEnHistorialLocal({
            id: Date.now(),
            fecha: new Date().toISOString(),
            fecha_inicio: this.horaInicio || new Date().toISOString(),
            fecha_termino: new Date().toISOString(),
            usuario_nombre: this.usuarioActual.nombre,
            monto_inicial: this.montoInicialTurno,
            monto_final: this.montoFinalTurno,
            total_contado: this.totalContado,
            total_otros_medios: this.totalOtrosMedios,
            total_general_contado: this.totalGeneralContado,
            total_sistema: this.totalVentasSistema,
            diferencia: this.diferencia,
            diferencia_general: this.diferenciaGeneral,
            estado: this.diferencia === 0 ? 'perfecto' : (this.diferencia > 0 ? 'sobrante' : 'faltante'),
            observaciones: this.observaciones,
            detalle_efectivo: this.getDetalleConteo(),
            detalle_medios_pago: { ...this.mediosPago }
          });
          
          // Cerrar turno después de guardar
          this.turnoActivo = false;
          
          // Limpiar localStorage del turno
          localStorage.removeItem('turnoActivo');
          localStorage.removeItem('fechaTurno');
          localStorage.removeItem('horaInicioTurno');
          localStorage.removeItem('montoInicialTurno');
          
          // Notificar al layout que el turno cambió
          this.notificarCambioTurno();
          
          // Recargar historial
          await this.cargarHistorial();
          
          // Mostrar mensaje de resultado con un delay para que se vea el cambio
          setTimeout(() => {
            if (this.diferencia === 0) {
              this.$awn.success('¡Perfecto! El arqueo coincide exactamente con las ventas.');
            } else if (this.diferencia > 0) {
              this.$awn.warning(`Hay un sobrante de $${this.formatMoney(Math.abs(this.diferencia))}`);
            } else {
              this.$awn.alert(`Hay un faltante de $${this.formatMoney(Math.abs(this.diferencia))}`);
            }
            
            // Mensaje final de turno cerrado
            this.$awn.info('Turno cerrado correctamente. Puedes iniciar un nuevo turno.');
          }, 2000);
          
        } else {
          console.log(request.data);
          this.$awn.alert(request.data.message || 'Error al guardar el arqueo');
        }
      } catch (error) {
        console.error('Error al guardar arqueo:', error);
        this.$awn.alert('Error al guardar el arqueo. Intente nuevamente.');
      } finally {
        this.loading = false;
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
        this.$awn.alert('No hay datos para imprimir');
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

    // Guardar arqueo en historial local
    guardarEnHistorialLocal(arqueoData) {
      try {
        // Obtener historial existente
        const historialExistente = JSON.parse(localStorage.getItem('historialArqueos') || '[]');
        
        // Agregar nuevo arqueo al inicio
        historialExistente.unshift(arqueoData);
        
        // Mantener solo los últimos 50 arqueos
        if (historialExistente.length > 50) {
          historialExistente.splice(50);
        }
        
        // Guardar de vuelta en localStorage
        localStorage.setItem('historialArqueos', JSON.stringify(historialExistente));
        
        // Actualizar historial en el componente
        this.cargarHistorialLocal();
        
        console.log('📁 Arqueo guardado en historial local:', arqueoData);
        
      } catch (error) {
        console.error('Error guardando en historial local:', error);
      }
    },

    // Cargar historial desde localStorage
    cargarHistorialLocal() {
      try {
        const historialLocal = JSON.parse(localStorage.getItem('historialArqueos') || '[]');
        this.historial = historialLocal;
        console.log('📖 Historial cargado:', this.historial.length, 'registros');
      } catch (error) {
        console.error('Error cargando historial local:', error);
        this.historial = [];
      }
    },

    // Limpiar historial completo
    limpiarHistorial() {
      if (confirm('¿Está seguro de eliminar todo el historial de arqueos?\n\nEsta acción no se puede deshacer.')) {
        localStorage.removeItem('historialArqueos');
        this.historial = [];
        this.$awn.success('Historial eliminado correctamente');
      }
    },

    // Eliminar un arqueo específico del historial
    eliminarArqueoHistorial(index) {
      if (confirm('¿Está seguro de eliminar este registro del historial?')) {
        this.historial.splice(index, 1);
        localStorage.setItem('historialArqueos', JSON.stringify(this.historial));
        this.$awn.success('Registro eliminado del historial');
      }
    },

    // Ver detalle de un arqueo del historial
    verDetalleArqueo(arqueo) {
      const detalle = `
🗓️ ARQUEO DEL ${this.formatDate(arqueo.fecha)} - ${this.formatTime(arqueo.fecha)}

👤 Usuario: ${arqueo.usuario_nombre}

⏰ Horarios:
• Inicio: ${this.formatDateTime(arqueo.fecha_inicio)}
• Cierre: ${this.formatDateTime(arqueo.fecha_termino)}

💰 Montos:
• Inicial: $${this.formatMoney(arqueo.monto_inicial)}
• Final: $${this.formatMoney(arqueo.monto_final)}

💵 Conteo:
• Efectivo: $${this.formatMoney(arqueo.total_contado)}
• Otros medios: $${this.formatMoney(arqueo.total_otros_medios)}
• TOTAL CONTADO: $${this.formatMoney(arqueo.total_general_contado)}

💻 Sistema:
• Total ventas: $${this.formatMoney(arqueo.total_sistema)}

📊 Resultado:
• Diferencia: $${this.formatMoney(arqueo.diferencia_general)}
• Estado: ${arqueo.estado.toUpperCase()}

📝 Observaciones:
${arqueo.observaciones || 'Sin observaciones'}
      `;
      
      alert(detalle);
    },

    // Notificar cambio de turno al layout
    notificarCambioTurno() {
      // Disparar evento personalizado para notificar cambio de turno
      window.dispatchEvent(new CustomEvent('turnoChanged', {
        detail: { 
          turnoActivo: this.turnoActivo,
          timestamp: new Date().toISOString()
        }
      }));
      console.log('🔔 ARQUEO: Notificando cambio de turno:', this.turnoActivo);
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
    
    formatDateTime(dateString) {
      const date = new Date(dateString);
      return `${date.toLocaleDateString('es-CL')} ${date.toLocaleTimeString('es-CL', {
        hour: '2-digit',
        minute: '2-digit'
      })}`;
    },
    
    formatDuration(start, end) {
      const startTime = new Date(start);
      const endTime = new Date(end);
      const diffMs = endTime - startTime;
      const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
      const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
      
      if (diffHours > 0) {
        return `${diffHours}h ${diffMinutes}m`;
      } else {
        return `${diffMinutes}m`;
      }
    },
    
    getDiferenciaClass(diferencia) {
      if (diferencia === 0) return 'text-success';
      if (diferencia > 0) return 'text-warning';
      return 'text-danger';
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
  color: #000000 !important;
  text-shadow: 2px 2px 4px rgba(255,255,255,0.8), -1px -1px 2px rgba(255,255,255,0.6);
}

.page-subtitle {
  margin: 5px 0 0 0;
  opacity: 1;
  font-size: 1.1rem;
  color: #000000 !important;
  text-shadow: 1px 1px 3px rgba(255,255,255,0.8), -1px -1px 2px rgba(255,255,255,0.6);
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
  color: #000000 !important;
  font-size: 0.95rem;
  margin-bottom: 5px;
  text-shadow: 1px 1px 3px rgba(255,255,255,0.8), -1px -1px 2px rgba(255,255,255,0.6);
}

.info-item i {
  color: #333333 !important;
}

.info-item strong {
  color: #000000 !important;
  font-weight: 700;
}

/* Estilos más específicos para asegurar visibilidad */
.arqueo-info .info-item {
  color: #000000 !important;
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.8), -1px -1px 2px rgba(255,255,255,0.6);
}

.arqueo-info .info-item strong {
  color: #000000 !important;
  font-weight: 700;
}

.arqueo-info .info-item i {
  color: #333333 !important;
}

/* Asegurar que TODO el texto dentro de info-item sea negro */
.arqueo-info .info-item * {
  color: #000000 !important;
  text-shadow: 1px 1px 3px rgba(255,255,255,0.8), -1px -1px 2px rgba(255,255,255,0.6);
}

/* Estilos para el cuadro de estado del arqueo */
.arqueo-estado {
  background: rgba(255, 255, 255, 0.25);
  border-radius: 12px;
  padding: 15px;
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.estado-item {
  color: #000000 !important;
  font-size: 1rem;
  padding: 8px 12px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.15);
  text-shadow: 1px 1px 3px rgba(255,255,255,0.8), -1px -1px 2px rgba(255,255,255,0.6);
}

.estado-item.inicial {
  border-left: 4px solid #28a745;
}

.estado-item.final {
  border-left: 4px solid #ffc107;
}

.estado-item i {
  color: #333333 !important;
}

.estado-item strong {
  color: #000000 !important;
  font-weight: 700;
}

.estado-item .badge {
  font-size: 0.8rem;
  padding: 4px 8px;
}

/* Estilos para modales simples - FUNCIONALES */
.modal-simple-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(3px);
}

.modal-simple-content {
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-width: 400px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  animation: modal-simple-appear 0.3s ease-out;
}

@keyframes modal-simple-appear {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-simple-content h4 {
  color: #374151;
  margin-bottom: 15px;
}

.modal-simple-content p {
  color: #6b7280;
  margin-bottom: 15px;
}

.modal-simple-content .form-control {
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  padding: 12px;
  font-size: 1.1rem;
  text-align: center;
}

.modal-simple-content .form-control:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
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
  max-height: 500px;
  overflow-y: auto;
}

.historial-item {
  padding: 20px;
  border: 2px solid #f1f5f9;
  border-radius: 12px;
  margin-bottom: 15px;
  background: #ffffff;
  transition: all 0.3s ease;
}

.historial-item:hover {
  border-color: #3b82f6;
  box-shadow: 0 5px 15px rgba(59, 130, 246, 0.1);
  transform: translateY(-2px);
}

.historial-item:last-child {
  margin-bottom: 0;
}

.historial-header {
  border-bottom: 1px solid #f1f5f9;
  padding-bottom: 10px;
  margin-bottom: 10px;
}

.historial-fecha {
  font-size: 0.95rem;
  color: #374151;
}

.historial-usuario {
  font-size: 0.85rem;
  margin-top: 2px;
}

.historial-actions .btn {
  padding: 4px 8px;
  font-size: 0.8rem;
}

.historial-resumen {
  background: #f8fafc;
  border-radius: 8px;
  padding: 12px;
}

.historial-estado .badge {
  font-size: 0.75rem;
  padding: 4px 8px;
}

.historial-observaciones {
  background: #fffbeb;
  border: 1px solid #fbbf24;
  border-radius: 6px;
  padding: 8px;
  font-style: italic;
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

/* Asegurar texto negro/oscuro en todo el componente */
.arqueo-caja-container * {
  color: #1e293b !important;
}

.arqueo-caja-container .page-header * {
  color: white !important;
}

.arqueo-caja-container .total-section * {
  color: white !important;
}

.arqueo-caja-container .status-badge {
  color: #10b981 !important;
}

.arqueo-caja-container .btn {
  color: inherit !important;
}

.arqueo-caja-container .card-header {
  color: white !important;
}

.arqueo-caja-container input, 
.arqueo-caja-container select, 
.arqueo-caja-container textarea {
  color: #1e293b !important;
}

.arqueo-caja-container .table th,
.arqueo-caja-container .table td {
  color: #1e293b !important;
}

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
