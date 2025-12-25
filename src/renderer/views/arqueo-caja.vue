<template>
  <div class="arqueo-container">
    <!-- Elementos decorativos de fondo -->
    <div class="bg-decoration">
      <div class="floating-circle circle-1"></div>
      <div class="floating-circle circle-2"></div>
      <div class="floating-circle circle-3"></div>
      <div class="floating-shape shape-1"></div>
      <div class="floating-shape shape-2"></div>
    </div>
    
    <!-- Header Moderno -->
    <div class="modern-header">
      <div class="header-content">
        <div class="header-title-section">
          <div class="title-icon">
            <i class="fas fa-cash-register"></i>
          </div>
          <div class="title-text">
            <h1>Arqueo de Cajaaaaaaaaaaaaaaa</h1>
          </div>
        </div>
        <div class="header-status">
          <div class="status-indicator" :class="turnoActivo ? 'active' : 'inactive'">
            <i class="fas fa-circle"></i>
            <span>{{ turnoActivo ? 'Turno Activo' : 'Sin Turno' }}</span>
          </div>
        </div>
      </div>
      
      <!-- Info Cards -->
      <div class="info-cards">
        <div class="info-card">
          <i class="fas fa-store"></i>
          <div class="card-content">
            <span class="card-label">Negocio</span>
            <span class="card-value">{{ negocioInfo.nombre }}</span>
          </div>
        </div>
        <div class="info-card">
          <i class="fas fa-calendar-day"></i>
          <div class="card-content">
            <span class="card-label">Fecha</span>
            <span class="card-value">{{ fechaActual }}</span>
          </div>
        </div>
        <div class="info-card">
          <i class="fas fa-user"></i>
          <div class="card-content">
            <span class="card-label">Usuario</span>
            <span class="card-value">{{ usuarioActual.nombre }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Sin Turno - Inicio -->
    <div v-if="!turnoActivo" class="no-turno-section">
      <div class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-play-circle"></i>
        </div>
        <h3>¿Listo para iniciar el día?</h3>
        <p>Inicia un turno para comenzar el control de caja</p>
        <button 
          class="btn-primary-large"
          @click="iniciarTurno"
          :disabled="cargandoTurno"
        >
          <i class="fas fa-rocket me-2"></i>
          {{ cargandoTurno ? 'Iniciando...' : 'Iniciar Turno' }}
        </button>
      </div>
    </div>

    <!-- Con Turno Activo -->
    <div v-if="turnoActivo" class="turno-activo">
      
      <!-- Resumen del Turno -->
      <div class="turno-summary">
        <div class="summary-card initial">
          <div class="card-icon">
            <i class="fas fa-piggy-bank"></i>
          </div>
          <div class="card-info">
            <span class="card-title">Monto Inicial</span>
            <span class="card-amount">${{ formatMoney(montoInicialTurno) }}</span>
          </div>
        </div>
        
        <div class="summary-card current">
          <div class="card-icon">
            <i class="fas fa-calculator"></i>
          </div>
          <div class="card-info">
            <span class="card-title">Efectivo Contado</span>
            <span class="card-amount">${{ formatMoney(totalContado) }}</span>
            <small style="color: #64748b; font-size: 10px; display: block; margin-top: 4px;">
              Solo billetes + monedas (sin monto inicial)
            </small>
          </div>
        </div>
        
        <div class="summary-card total">
          <div class="card-icon">
            <i class="fas fa-trophy"></i>
          </div>
          <div class="card-info">
            <span class="card-title">Total General</span>
            <span class="card-amount total-highlight">${{ formatMoney(totalGeneralContado) }}</span>
            <small style="color: #64748b; font-size: 10px; display: block; margin-top: 4px;">
              Efectivo + todos los medios de pago
            </small>
          </div>
        </div>

        <!-- Tarjeta de Gastos del Día -->
        <div class="summary-card expense-card" @click="mostrarModalGastos" style="cursor: pointer;">
          <div class="card-icon expense-icon">
            <i class="fas fa-minus-circle"></i>
          </div>
          <div class="card-info">
            <span class="card-title">Gastos del Día</span>
            <span class="card-amount expense-amount">-${{ formatMoney(totalGastosDia) }}</span>
            <small style="color: #ef4444; font-size: 10px; display: block; margin-top: 4px;">
              {{ gastosDia.length }} gasto{{ gastosDia.length !== 1 ? 's' : '' }} registrado{{ gastosDia.length !== 1 ? 's' : '' }}
              <i class="fas fa-eye ms-1"></i> Click para ver detalle
            </small>
          </div>
        </div>

        <!-- Tarjeta de Resumen Final (después de gastos) -->
        <div class="summary-card final-card">
          <div class="card-icon final-icon">
            <i class="fas fa-calculator"></i>
          </div>
          <div class="card-info">
            <span class="card-title">Resumen Final</span>
            <span class="card-amount final-highlight">${{ formatMoney(resumenFinalCaja) }}</span>
            <small style="color: #10b981; font-size: 10px; display: block; margin-top: 4px;">
              Monto Inicial + Total - Gastos
            </small>
          </div>
        </div>
      </div>

      <!-- Contenido Principal -->
      <div class="main-content">
        
        <!-- Panel de Conteo -->
        <div class="conteo-panel">
          <div class="panel-header">
            <h3><i class="fas fa-money-bill-wave me-2"></i>Conteo de Efectivo</h3>
          </div>
          
          <!-- Billetes -->
          <div class="denomination-group">
            <h4 class="group-title">💵 Billetes</h4>
            <div class="denomination-grid">
              <div 
                v-for="(valor, denominacion) in billetes" 
                :key="denominacion"
                class="denomination-item"
              >
                <div class="denom-value">${{ formatMoney(valor) }}</div>
                <input 
                  type="number" 
                  class="denom-input" 
                  v-model.number="conteo[denominacion]"
                  @input="calcularTotal"
                  @focus="selectInputContent"
                  min="0"
                  placeholder="0"
                >
                <div class="denom-label">cantidad</div>
              </div>
            </div>
          </div>

          <!-- Monedas -->
          <div class="denomination-group">
            <h4 class="group-title">🪙 Monedas</h4>
            <div class="denomination-grid small">
              <div 
                v-for="(valor, denominacion) in monedas" 
                :key="denominacion"
                class="denomination-item"
              >
                <div class="denom-value">${{ formatMoney(valor) }}</div>
                <input 
                  type="number" 
                  class="denom-input" 
                  v-model.number="conteo[denominacion]"
                  @input="calcularTotal"
                  @focus="selectInputContent"
                  min="0"
                  placeholder="0"
                >
                <div class="denom-label">cantidad</div>
              </div>
            </div>
          </div>

          <!-- Suma Total Métodos de Pago Diversos -->
          <div class="medios-group">
            <h4 class="group-title">💳 Suma Total Métodos de Pago Diversos</h4>
            <p class="group-subtitle">Registra lo que tienes de cada medio (sin ver las ventas)</p>
            <div class="medios-grid">
              <div 
                v-for="method in availablePaymentMethods" 
                :key="method.key"
                v-if="method.key !== 'efectivo'"
                class="medio-item"
              >
                <div class="medio-icon" :class="`icon-${method.color}`">
                  <i :class="method.icon"></i>
                </div>
                <div class="medio-info">
                  <span class="medio-name">{{ method.emoji }} {{ method.name }}</span>
                  <input 
                    type="number" 
                    class="medio-input" 
                    v-model.number="mediosPago[method.key]"
                    @input="calcularTotalGeneral"
                    @focus="selectInputContent"
                    min="0"
                    step="0.01"
                    placeholder="0.00"
                  >
                </div>
              </div>
            </div>
          </div>

          <!-- Observaciones -->
          <div class="observaciones-section">
            <label class="obs-label">
              <i class="fas fa-sticky-note me-2"></i>Observaciones
            </label>
            <textarea 
              class="obs-textarea" 
              v-model="observaciones" 
              @focus="selectInputContent"
              rows="3" 
              placeholder="Agrega cualquier observación sobre el arqueo..."
            ></textarea>
          </div>

          <!-- Botones de Acción -->
          <div class="action-buttons">
            <button 
              class="btn-danger-large" 
              @click="mostrarModalConfirmacion"
              :disabled="loading || totalContado === 0"
            >
              <i class="fas fa-stop me-2"></i>
              {{ loading ? 'Cerrando Turno...' : 'Cerrar Turno' }}
            </button>
            <button class="btn-outline" @click="limpiarFormulario">
              <i class="fas fa-broom me-2"></i>Limpiar
            </button>
          </div>
        </div>

        <!-- Panel de Resultados (Solo después de guardar) -->
        <div v-if="arqueoGuardado" class="resultados-panel">
          <div class="panel-header">
            <h3><i class="fas fa-chart-line me-2"></i>Resultado del Arqueo</h3>
          </div>
          
          <!-- Comparación Efectivo -->
          <div class="comparison-card efectivo">
            <div class="comparison-header">
              <i class="fas fa-money-bill-wave"></i>
              <span>Efectivo</span>
            </div>
            <div class="comparison-data">
              <div class="data-row">
                <span>Contaste (billetes + monedas):</span>
                <span class="amount">${{ formatMoney(totalContado) }}</span>
              </div>
              <div class="data-row">
                <span>Ventas en efectivo del sistema:</span>
                <span class="amount">${{ formatMoney(getPaymentMethodSales('efectivo')) }}</span>
              </div>
              <div class="data-row difference">
                <span>Diferencia (contado - ventas):</span>
                <span class="amount" :class="diferencia >= 0 ? 'positive' : 'negative'">
                  ${{ formatMoney(diferencia) }}
                </span>
              </div>
              <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                <small style="color: #64748b; font-size: 11px; font-style: italic;">
                  💡 El monto inicial (${{ formatMoney(montoInicialTurno) }}) NO se cuenta aquí porque ya estaba en caja
                </small>
              </div>
            </div>
          </div>

          <!-- Otros Medios -->
          <div 
            v-for="method in availablePaymentMethods" 
            :key="method.key"
            v-if="method.key !== 'efectivo' && (mediosPago[method.key] > 0 || getPaymentMethodSales(method.key) > 0)"
            class="comparison-card medio"
          >
            <div class="comparison-header">
              <i :class="method.icon"></i>
              <span>{{ method.name }}</span>
            </div>
            <div class="comparison-data">
              <div class="data-row">
                <span>Contaste:</span>
                <span class="amount">${{ formatMoney(mediosPago[method.key] || 0) }}</span>
              </div>
              <div class="data-row">
                <span>Sistema:</span>
                <span class="amount">${{ formatMoney(getPaymentMethodSales(method.key)) }}</span>
              </div>
              <div class="data-row difference">
                <span>Diferencia:</span>
                <span class="amount" :class="getPaymentMethodDifference(method.key) >= 0 ? 'positive' : 'negative'">
                  ${{ formatMoney(getPaymentMethodDifference(method.key)) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Resultado Final -->
          <div class="resultado-final" :class="getEstadoClass()">
            <div class="resultado-icon">
              <i :class="getResultadoIcon()"></i>
            </div>
            <div class="resultado-info">
              <h4>{{ getMensajeResultado() }}</h4>
              <p>Diferencia total: ${{ formatMoney(diferenciaGeneral) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Monto Inicial -->
    <div v-if="mostrandoModalMontoInicial" class="modal-overlay" @click="enfocarInputInicial">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3><i class="fas fa-piggy-bank me-2"></i>Monto Inicial</h3>
        </div>
        <div class="modal-body">
          <p>¿Con cuánto dinero inicias el turno?</p>
          <input 
            v-model="montoInicialInput" 
            type="number" 
            class="modal-input" 
            placeholder="0" 
            min="0" 
            step="0.01"
            autofocus
            @keyup.enter="confirmarMontoInicial"
            @focus="$event.target.select()"
            ref="montoInicialInputRef"
          >
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="cancelarMontoInicial">
            <i class="fas fa-times me-1"></i>Cancelar
          </button>
          <button class="btn-primary" @click="confirmarMontoInicial">
            <i class="fas fa-play me-1"></i>Iniciar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmación Elegante para Cierre de Turno -->
    <div v-if="showConfirmModal" class="modal-overlay audit-modal" @click="cerrarModalConfirmacion">
      <div class="modal-container elegant-modal" @click.stop>
        <div class="modal-header elegant-header">
          <div class="modal-icon audit-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h2 class="modal-title">Confirmación de Cierre de Turno</h2>
          <div class="security-badge">
            <i class="fas fa-lock"></i>
            <span>Sistema Seguro</span>
          </div>
        </div>
        
        <div class="modal-body elegant-body">
          <div class="confirmation-message">
            <div class="greeting-section">
              <p class="main-message">
                Estimado/a <strong>{{ getNombreCajero() }}</strong>, está a punto de finalizar su turno de trabajo.
              </p>
            </div>
            
            <div class="summary-box">
              <div class="summary-header">
                <i class="fas fa-clipboard-check"></i>
                <span>Resumen del Turno</span>
              </div>
              <div class="summary-content">
                <div class="info-item">
                  <i class="fas fa-clock"></i>
                  <span>Iniciado:</span>
                  <span class="value">{{ getFechaInicioTurno() }}</span>
                </div>
                <div class="info-item">
                  <i class="fas fa-calculator"></i>
                  <span>Total Contado:</span>
                  <span class="value highlight">{{ formatMoney(totalGeneralContado) }}</span>
                </div>
                <div class="info-item">
                  <i class="fas fa-coins"></i>
                  <span>Monto Inicial:</span>
                  <span class="value">{{ formatMoney(montoInicialTurno) }}</span>
                </div>
              </div>
            </div>
            
            <div class="audit-notice">
              <div class="audit-header">
                <div class="audit-icon-circle">
                  <i class="fas fa-eye"></i>
                </div>
                <h4>Sistema de Auditoría Interna</h4>
              </div>
              <div class="audit-content">
                <p class="audit-main">
                  Al confirmar, toda la información registrada será almacenada de forma segura 
                  en nuestro sistema de control interno.
                </p>
                <div class="audit-details">
                  <div class="audit-point">
                    <i class="fas fa-check-circle"></i>
                    <span>Los registros de arqueo son monitoreados periódicamente</span>
                  </div>
                  <div class="audit-point">
                    <i class="fas fa-shield-check"></i>
                    <span>Garantizamos transparencia en el manejo de efectivo</span>
                  </div>
                  <div class="audit-point">
                    <i class="fas fa-chart-line"></i>
                    <span>Análisis estadístico para mejora continua</span>
                  </div>
                </div>
                <p class="audit-footnote">
                  <small>
                    <i class="fas fa-info-circle"></i>
                    Este procedimiento forma parte de nuestras políticas de control interno 
                    y cumplimiento normativo para proteger tanto al empleado como a la empresa.
                  </small>
                </p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer elegant-footer">
          <button @click="cerrarModalConfirmacion" class="btn-cancel elegant-cancel">
            <i class="fas fa-arrow-left"></i>
            Revisar Nuevamente
          </button>
          <button @click="confirmarCierreConAuditoria" class="btn-confirm elegant-confirm">
            <i class="fas fa-check-double"></i>
            <span>Confirmar y Cerrar Turno</span>
            <small>Con registro de auditoría</small>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Listado de Gastos -->
    <div v-if="mostrandoModalGastos" class="modal-overlay" @click="cerrarModalGastos">
      <div class="modal-container gastos-modal" @click.stop>
        <div class="modal-header">
          <div class="modal-icon expense-modal-icon">
            <i class="fas fa-wallet"></i>
          </div>
          <h2 class="modal-title">Gastos del Día</h2>
          <button @click="cerrarModalGastos" class="btn-close-modal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body gastos-body">
          <div v-if="gastosDia.length === 0" class="empty-state">
            <i class="fas fa-check-circle"></i>
            <p>No hay gastos registrados para hoy</p>
            <small>El resumen final no incluye deducciones</small>
          </div>
          
          <div v-else class="gastos-list">
            <div class="gastos-header">
              <span class="header-item">Concepto</span>
              <span class="header-item">Monto</span>
            </div>
            <div 
              v-for="(gasto, index) in gastosDia" 
              :key="gasto.id || index"
              class="gasto-item"
            >
              <div class="gasto-concepto">
                <i class="fas fa-receipt"></i>
                <span>{{ gasto.name || 'Sin descripción' }}</span>
              </div>
              <div class="gasto-monto">
                <span class="monto-value">-${{ formatMoney(gasto.balance) }}</span>
              </div>
            </div>
            
            <div class="gastos-total">
              <span class="total-label">Total Gastos:</span>
              <span class="total-value">-${{ formatMoney(totalGastosDia) }}</span>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button @click="cerrarModalGastos" class="btn-primary">
            <i class="fas fa-check me-1"></i>Entendido
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
      
      // Conteo de MÉTODOS DE PAGO DIVERSOS (inicializado dinámicamente)
      mediosPago: {},
      
      // Ventas del sistema por método de pago (dinámico)
      ventasPorMedio: {},
      
      // Gastos del día
      gastosDia: [],
      mostrandoModalGastos: false,
      
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
      resolverMontoInicial: null,
      
      // Modal de confirmación de cierre con auditoría
      showConfirmModal: false
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
        return 'Arqueo Registrado ⚠️';
      } else {
        return 'Arqueo Registrado ❌';
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
    
    // ✅ CORREGIDO: Total general SIN incluir monto inicial (eso ya está en caja)
    totalGeneralContado() {
      return this.totalContado + this.totalOtrosMedios; // Solo lo que contaste, sin monto inicial
    },
    
    // ✅ CORREGIDO: Total de ventas del sistema (solo ventas, sin monto inicial)
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

    // Total de gastos del día
    totalGastosDia() {
      return this.gastosDia.reduce((sum, gasto) => sum + parseFloat(gasto.balance || 0), 0);
    },

    // Resumen Final de Caja: Monto Inicial + Total Contado - Gastos
    resumenFinalCaja() {
      return this.montoInicialTurno + this.totalGeneralContado - this.totalGastosDia;
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
    
    // ✅ CORREGIDO: Diferencia general - todo lo que hay en caja vs lo que debería haber
    diferenciaGeneral() {
      // Lo que hay en caja: Monto Inicial + Total Contado - Gastos
      const totalEnCaja = this.resumenFinalCaja;
      
      // Lo que debería haber: Monto Inicial + Ventas del Sistema - Gastos
      const totalEsperado = this.montoInicialTurno + this.totalVentasSistema - this.totalGastosDia;
      
      return totalEnCaja - totalEsperado;
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
    this.cargarGastosDia(); // Cargar gastos del día
    this.cargarInfoUsuario();
    this.cargarInfoNegocio();
    this.cargarHistorial(); // ✅ Solo de BD, no localStorage
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
        console.log('🔍 VERIFICAR TURNO: Iniciando verificación...');
        
        // Primero verificar con el backend (fuente de verdad)
        try {
          const request = await this.$store.dispatch('arqueo/verificarEstadoTurno');
          console.log('📡 VERIFICAR TURNO: Respuesta del backend:', request);
          
          if (request.success && request.data) {
            // Usar datos del backend como fuente de verdad
            this.turnoActivo = request.data.turno_abierto || false;
            
            if (this.turnoActivo && request.data.turno) {
              this.montoInicialTurno = parseFloat(request.data.turno.monto_inicial) || 0;
              // 🔧 ARREGLO: Recuperar también el ID del turno activo
              this.turnoId = request.data.turno.id;
              
              console.log('✅ VERIFICAR TURNO: Turno activo encontrado en backend');
              console.log('📊 Turno activo:', this.turnoActivo);
              console.log('🆔 Turno ID:', this.turnoId);
              console.log('💰 Monto inicial:', this.montoInicialTurno);
              
              // Sincronizar localStorage con datos del backend
              const fechaHoy = new Date().toISOString().split('T')[0];
              localStorage.setItem('turnoActivo', this.turnoActivo.toString());
              localStorage.setItem('fechaTurno', fechaHoy);
              localStorage.setItem('montoInicialTurno', this.montoInicialTurno.toString());
              localStorage.setItem('turnoId', this.turnoId.toString());
              if (request.data.turno.fecha_inicio) {
                localStorage.setItem('horaInicioTurno', request.data.turno.fecha_inicio);
              }
              
              // Emitir evento para notificar a otros componentes
              window.dispatchEvent(new CustomEvent('turnoChanged', { 
                detail: { activo: true, turnoId: this.turnoId } 
              }));
              
            } else {
              console.log('⚠️ VERIFICAR TURNO: Backend confirma que no hay turno activo');
              this.turnoActivo = false;
              this.montoInicialTurno = 0;
              
              // ✅ MEJORADO: Solo limpiar localStorage si el backend explícitamente dice que no hay turno
              // Y solo si el request fue exitoso (no por error de conexión)
              localStorage.removeItem('turnoActivo');
              localStorage.removeItem('fechaTurno');
              localStorage.removeItem('horaInicioTurno');
              localStorage.removeItem('montoInicialTurno');
              localStorage.removeItem('turnoId');
              
              // Emitir evento para notificar a otros componentes
              window.dispatchEvent(new CustomEvent('turnoChanged', { 
                detail: { activo: false } 
              }));
            }
            return;
          } else {
            console.warn('⚠️ VERIFICAR TURNO: Respuesta del backend no exitosa:', request);
          }
        } catch (apiError) {
          console.warn('⚠️ VERIFICAR TURNO: Error de comunicación con API, manteniendo estado local:', apiError);
          // NO limpiar localStorage en caso de error de comunicación
        }
        
        // Fallback: verificar localStorage como backup (solo si backend no respondió correctamente)
        const turnoLocal = localStorage.getItem('turnoActivo');
        const fechaLocal = localStorage.getItem('fechaTurno');
        const montoInicialLocal = localStorage.getItem('montoInicialTurno');
        const turnoIdLocal = localStorage.getItem('turnoId');
        const fechaHoy = new Date().toISOString().split('T')[0];
        
        console.log('📱 VERIFICAR TURNO: Usando localStorage como fallback');
        console.log('📱 TurnoLocal:', turnoLocal, 'FechaLocal:', fechaLocal, 'FechaHoy:', fechaHoy);
        
        // Limpiar turnos de días anteriores automáticamente
        if (fechaLocal && fechaLocal !== fechaHoy) {
          console.log('🧹 VERIFICAR TURNO: Limpiando turno de día anterior:', fechaLocal);
          localStorage.removeItem('turnoActivo');
          localStorage.removeItem('fechaTurno');
          localStorage.removeItem('horaInicioTurno');
          localStorage.removeItem('montoInicialTurno');
          localStorage.removeItem('turnoId');
          this.turnoActivo = false;
          this.montoInicialTurno = 0;
          return;
        }
        
        // Si hay un turno local del día de hoy, usarlo temporalmente
        if (turnoLocal === 'true' && fechaLocal === fechaHoy) {
          this.turnoActivo = true;
          this.montoInicialTurno = parseFloat(montoInicialLocal) || 0;
          this.turnoId = parseInt(turnoIdLocal) || null;
          console.log('✅ VERIFICAR TURNO: Usando datos del localStorage (modo offline)');
          console.log('🆔 Turno ID desde localStorage:', this.turnoId);
          console.log('💰 Monto inicial desde localStorage:', this.montoInicialTurno);
        } else {
          this.turnoActivo = false;
          this.montoInicialTurno = 0;
        }
        
      } catch (error) {
        console.error('❌ VERIFICAR TURNO: Error inesperado:', error);
        // En caso de error grave, mantener estado actual (no limpiar localStorage)
      }
    },

    // Método mejorado para iniciar turno
    async iniciarTurno() {
      try {
        // Primero solicitar el monto inicial
        const montoInicial = await this.solicitarMontoInicial();
        console.log('🚀 INICIAR TURNO - Monto recibido:', montoInicial);
        
        if (montoInicial === null) {
          // Usuario canceló
          console.log('❌ INICIAR TURNO - Usuario canceló');
          return;
        }
        
        this.cargandoTurno = true;
        
        // Preparar datos para el backend
        const turnoData = {
          usuario_id: this.usuarioActual.id, // 🔧 ARREGLO: Usar el usuario logueado actual
          usuario_nombre: this.usuarioActual.nombre, // 🔧 ARREGLO: Usar el nombre del usuario actual
          monto_inicial: montoInicial,
          fecha_inicio: new Date().toISOString(),
          notas_iniciales: `Turno iniciado con monto inicial de $${this.formatMoney(montoInicial)}`
        };
        
        console.log('📤 INICIAR TURNO - Datos a enviar:', turnoData);
        
        // 🔧 TEMPORAL: Enviar como FormData para asegurar que llegue al backend
        const formData = new FormData();
        formData.append('usuario_id', turnoData.usuario_id);
        formData.append('usuario_nombre', turnoData.usuario_nombre);
        formData.append('monto_inicial', turnoData.monto_inicial);
        formData.append('fecha_inicio', turnoData.fecha_inicio);
        formData.append('notas_iniciales', turnoData.notas_iniciales);
        
        console.log('📤 FORMDATA - Enviando como FormData para compatibilidad');
        for (let pair of formData.entries()) {
          console.log('📋 FormData:', pair[0], '=', pair[1]);
        }
        
        try {
          // Llamar a la API para crear el turno en la base de datos
          console.log('🔄 ENVIANDO REQUEST AL BACKEND...');
          const request = await this.$store.dispatch('arqueo/iniciarTurno', formData);
          
          console.log('📨 RESPUESTA COMPLETA DEL BACKEND:', request);
          console.log('✅ Success:', request.success);
          console.log('📄 Data:', request.data);
          console.log('⚠️ Message:', request.message);
          
          if (request.success) {
            // ✅ Turno creado exitosamente en la base de datos
            this.turnoActivo = true;
            this.montoInicialTurno = montoInicial;
            
            // 🔧 ARREGLO: Guardar el ID del turno devuelto por el backend
            if (request.data && request.data.turno && request.data.turno.id) {
              this.turnoId = request.data.turno.id;
              console.log('🆔 TURNO ID guardado:', this.turnoId);
            }
            
            console.log('✅ INICIAR TURNO - Turno guardado exitosamente');
            console.log('💰 INICIAR TURNO - montoInicialTurno asignado:', this.montoInicialTurno);
            
            // Guardar estado en localStorage como backup
            const fechaHoy = new Date().toISOString().split('T')[0];
            localStorage.setItem('turnoActivo', 'true');
            localStorage.setItem('fechaTurno', fechaHoy);
            localStorage.setItem('horaInicioTurno', new Date().toISOString());
            localStorage.setItem('montoInicialTurno', montoInicial.toString());
            localStorage.setItem('turnoId', this.turnoId.toString()); // 🔧 ARREGLO: Guardar también el ID
            
            this.cargandoTurno = false;
            this.$awn.success(`Turno iniciado con $${this.formatMoney(montoInicial)}`, { labels: { success: 'TURNO ACTIVO' } });
            
            // Notificar al layout que el turno cambió
            this.notificarCambioTurno();
            
            // Cargar datos normalmente después de iniciar turno
            this.cargarResumenDia();
            
            console.log('✅ Turno guardado en base de datos con ID:', (request.data.turno && request.data.turno.id) || 'N/A');
            
          } else {
            console.error('❌ BACKEND RETORNÓ SUCCESS = FALSE');
            console.error('📋 Respuesta completa:', request);
            console.error('💬 Mensaje del backend:', request.message);
            console.error('📄 Data del backend:', request.data);
            
            // 🔍 NUEVO: Log del error completo sin truncar
            if (request.data && request.data.message) {
              console.error('🔍 ERROR COMPLETO SIN TRUNCAR:', request.data.message);
            }
            if (request.data && request.data.debug) {
              console.error('🐛 DEBUG INFO:', request.data.debug);
            }
            
            throw new Error(request.message || 'Error desconocido al crear turno');
          }
          
        } catch (apiError) {
          console.error('❌ Error al crear turno en base de datos:', apiError);
          console.error('🔍 Tipo de error:', typeof apiError);
          console.error('📋 Error completo:', {
            message: apiError.message,
            stack: apiError.stack,
            name: apiError.name,
            cause: apiError.cause
          });
          
          // Mostrar error específico al usuario
          if (apiError.message && apiError.message.includes('Ya tienes un turno abierto')) {
            this.$awn.warning('Ya tienes un turno abierto. Debes cerrarlo antes de iniciar uno nuevo.');
          } else {
            this.$awn.alert(`Error al iniciar turno: ${apiError.message || 'Error de conexión'}`);
          }
          
          this.cargandoTurno = false;
          return;
        }
        
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
      console.log('🎯 CONFIRMAR MONTO INICIAL:');
      console.log('📝 Input recibido:', this.montoInicialInput);
      console.log('💰 Monto parseado:', monto);
      
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

    // ✅ NUEVO: Método para determinar estado del arqueo correctamente CONSIDERANDO TODO
    getEstadoArqueo() {
      // Usar la diferencia general que ya considera monto inicial, ventas y gastos
      const diferenciaTotal = this.diferenciaGeneral;
      
      if (Math.abs(diferenciaTotal) <= 1) { // Tolerancia de $1 para redondeos
        return 'perfecto';
      } else if (diferenciaTotal > 0) {
        return 'sobrante';
      } else {
        return 'faltante';
      }
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

    // Mostrar modal con listado de gastos
    mostrarModalGastos() {
      this.mostrandoModalGastos = true;
    },

    cerrarModalGastos() {
      this.mostrandoModalGastos = false;
    },

    // Cargar gastos del día desde el backend
    async cargarGastosDia() {
      try {
        console.log('💸 Cargando gastos del día...');
        
        // Construir parámetros de fecha para hoy
        const fechaHoy = new Date().toISOString().split('T')[0];
        var params = `?params=true&startDate=${fechaHoy}&endDate=${fechaHoy}`;
        
        // Llamar a la acción del store para obtener gastos
        var request = await this.$store.dispatch("expenses/getExpenses", params);
        
        if (request && request.success && request.data && request.data.items) {
          this.gastosDia = request.data.items;
          console.log('✅ Gastos cargados:', this.gastosDia.length, 'gastos por un total de $' + this.totalGastosDia);
        } else {
          this.gastosDia = [];
          console.log('ℹ️ No hay gastos registrados para hoy');
        }
      } catch (error) {
        console.error('❌ Error cargando gastos:', error);
        this.gastosDia = [];
      }
    },
    
    async cargarHistorial() {
      try {
        console.log('🔄 Cargando historial de arqueos desde base de datos...');
        
        // ✅ USAR STORE REAL para obtener historial de la base de datos
        const params = '?page=1&limit=10'; // Últimos 10 arqueos solamente
        const request = await this.$store.dispatch('arqueo/obtenerArqueos', params);
        
        if (request.success && request.data) {
          // Mejorar manejo de diferentes estructuras de respuesta
          let arqueos = [];
          if (Array.isArray(request.data)) {
            arqueos = request.data;
          } else if (request.data.items && Array.isArray(request.data.items)) {
            arqueos = request.data.items;
          } else if (request.data.data && Array.isArray(request.data.data)) {
            arqueos = request.data.data;
          }
          
          this.historial = arqueos;
          console.log('📖 Historial cargado desde BD:', this.historial.length, 'registros');
        } else {
          console.log('⚠️ No se pudieron cargar arqueos desde BD');
          this.historial = [];
        }
        
      } catch (error) {
        console.error('❌ Error al cargar historial desde BD:', error);
        this.historial = [];
      }
    },
    
    // Cargar gastos del día desde el backend
    async cargarGastosDia() {
      try {
        console.log('💸 Cargando gastos del día...');
        
        // Construir parámetros de fecha para hoy
        const fechaHoy = new Date().toISOString().split('T')[0];
        var params = `?params=true&startDate=${fechaHoy}&endDate=${fechaHoy}`;
        
        // Llamar a la acción del store para obtener gastos
        var request = await this.$store.dispatch("expenses/getExpenses", params);
        
        if (request && request.success && request.data && request.data.items) {
          this.gastosDia = request.data.items;
          console.log('✅ Gastos cargados:', this.gastosDia.length, 'gastos por un total de $' + this.totalGastosDia);
        } else {
          this.gastosDia = [];
          console.log('ℹ️ No hay gastos registrados para hoy');
        }
      } catch (error) {
        console.error('❌ Error cargando gastos:', error);
        this.gastosDia = [];
      }
    },

    async cargarVentasDelSistema() {
      try {
        console.log('📊 Cargando ventas del sistema para comparación...');
        
        // ✅ DATOS DE PRUEBA REALISTAS para testing local
        // En el futuro, hacer llamada real a la API
        const fechaHoy = new Date().toISOString().split('T')[0];
        console.log('📅 Consultando ventas para fecha:', fechaHoy);
        
        // ✅ DATOS REALISTAS basados en el monto que registraste ($38,000 efectivo)
        this.ventasPorMedio = {
          'efectivo': 35000, // Cerca de lo que contaste pero no exacto para mostrar diferencia
          'tarjeta_debito': 5000,
          'tarjeta_credito': 3000,
          'transferencia': 2000,
          'uber_eats': 1500,
          'junaeb': 500,
          // Otros métodos en 0 para simplicidad
          'cheque': 0,
          'banco': 0,
          'amipass': 0,
          'multicaja': 0,
          'edenred': 0,
          'sodexo': 0,
          'rappi': 0,
          'pedidos_ya': 0,
          'pluxee': 0,
          'banco_chile_20': 0,
          'fluxi': 0
        };
        
        // Asignar valores legacy para compatibilidad
        this.ventasEfectivo = this.ventasPorMedio['efectivo'] || 0;
        this.ventasTarjetaDebito = this.ventasPorMedio['tarjeta_debito'] || 0;
        this.ventasTarjetaCredito = this.ventasPorMedio['tarjeta_credito'] || 0;
        this.ventasTransferencia = this.ventasPorMedio['transferencia'] || 0;
        this.ventasCheque = this.ventasPorMedio['cheque'] || 0;
        this.ventasValeVista = this.ventasPorMedio['vale_vista'] || 0;
        this.ventasOtro = this.ventasPorMedio['otro'] || 0;
        
        console.log('💰 Ventas del sistema cargadas (DATOS DE PRUEBA):', this.ventasPorMedio);
        console.log('📋 Total ventas en efectivo:', this.ventasEfectivo);
        console.log('🏆 Total ventas sistema:', this.totalVentasSistema);
        
        // Recalcular diferencias con los datos reales
        this.calcularDiferencia();
        
      } catch (error) {
        console.error('❌ Error cargando ventas del sistema:', error);
        // Mantener datos vacíos en caso de error
        this.initializeEmptyResumen();
      }
    },
    
    calcularTotal() {
      let total = 0;
      const todasDenominaciones = { ...this.billetes, ...this.monedas };
      
      console.log('🧮 Calculando total del conteo...');
      console.log('📋 Estado actual del conteo:', this.conteo);
      
      for (let denominacion in todasDenominaciones) {
        const cantidad = parseInt(this.conteo[denominacion]) || 0;
        const valorDenominacion = todasDenominaciones[denominacion];
        const subtotal = cantidad * valorDenominacion;
        
        if (cantidad > 0) {
          console.log(`💰 ${denominacion}: ${cantidad} x ${valorDenominacion} = ${subtotal}`);
        }
        
        total += subtotal;
      }
      
      console.log('💵 Total contado calculado:', total);
      this.totalContado = total;
      this.calcularDiferencia();
    },
    
    calcularDiferencia() {
      // ✅ CORREGIDO: La diferencia de efectivo es simple
      // Lo que contaste en efectivo VS lo que vendiste en efectivo
      this.diferencia = this.totalContado - this.getPaymentMethodSales('efectivo');
      
      console.log('🧮 CÁLCULO DE DIFERENCIA:');
      console.log('💵 Efectivo contado:', this.totalContado);
      console.log('💰 Ventas en efectivo:', this.getPaymentMethodSales('efectivo'));
      console.log('📊 Diferencia:', this.diferencia);
    },
    
    calcularTotalGeneral() {
      // Primero calcular el total de efectivo
      this.calcularTotal();
      // Los totales de métodos de pago diversos se calculan automáticamente con computed properties
    },
    
    async guardarArqueo() {
      if (this.totalContado === 0) {
        this.$awn.alert('Debe ingresar al menos una denominación');
        return;
      }
      
      // Mostrar modal de confirmación elegante
      this.mostrarModalConfirmacion();
    },

    mostrarModalConfirmacion() {
      // Mostrar modal elegante en lugar del confirm nativo
      this.showConfirmModal = true;
    },
    
    cerrarModalConfirmacion() {
      this.showConfirmModal = false;
    },
    
    async confirmarCierreConAuditoria() {
      // Cerrar modal primero
      this.showConfirmModal = false;
      
      // Proceder con el guardado original
      this.confirmarGuardado();
    },
    
    getNombreCajero() {
      return (this.me && this.me.fullname) || 
             (this.usuarioActual && this.usuarioActual.nombre) || 
             'Cajero';
    },
    
    getFechaInicioTurno() {
      return this.horaInicio ? 
        moment(this.horaInicio).format('DD/MM/YYYY HH:mm') : 
        moment().format('DD/MM/YYYY HH:mm');
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
        
        // ✅ DEBUGGING DETALLADO ANTES DE CREAR EL OBJETO DATA
        console.log('🔍 VALORES PRE-ENVÍO:');
        console.log('  🆔 turnoId:', this.turnoId, '(tipo:', typeof this.turnoId, ')');
        console.log('  💰 montoInicialTurno:', this.montoInicialTurno, '(tipo:', typeof this.montoInicialTurno, ')');
        console.log('  💵 totalContado:', this.totalContado, '(tipo:', typeof this.totalContado, ')');
        console.log('  💳 totalOtrosMedios:', this.totalOtrosMedios, '(tipo:', typeof this.totalOtrosMedios, ')');
        console.log('  🏆 totalGeneralContado:', this.totalGeneralContado, '(tipo:', typeof this.totalGeneralContado, ')');
        console.log('  📊 mediosPago objeto:', this.mediosPago);
        
        // 🔍 DEBUG: Verificar el app.id que se está usando
        console.log('🏢 DEBUG this.app:', this.app);
        console.log('🏢 DEBUG this.app.id:', this.app ? this.app.id : 'NO EXISTE');
        console.log('🏢 DEBUG app_id final:', (this.app && this.app.id) ? this.app.id : 58);
        
        const data = {
          app_id: (this.app && this.app.id) ? this.app.id : 58, // Usar el ID del negocio actual desde this.app
          turno_id: this.turnoId, // 🔧 ARREGLO: Agregar ID del turno que se quiere cerrar
          usuario_id: this.usuarioActual.id,
          usuario_nombre: this.usuarioActual.nombre,
          app_nombre: this.negocioInfo.nombre || 'Por determinar',
          fecha_inicio: new Date().toISOString(),
          fecha_termino: new Date().toISOString(),
          
          // ✅ CORRECCIÓN PRINCIPAL: Separar correctamente los campos
          monto_inicial: this.montoInicialTurno, // Lo que tenías al inicio
          monto_final: this.totalContado, // Solo efectivo contado (billetes + monedas)
          total_contado: this.totalContado, // ✅ CORREGIDO: Solo efectivo, NO total general
          total_otros_medios: this.totalOtrosMedios, // Suma total de métodos de pago diversos
          total_general: this.totalContado + this.totalOtrosMedios, // ✅ NUEVO: Total SIN monto inicial
          
          // ✅ SISTEMA: Para comparar
          total_sistema: this.totalVentasSistema, // Solo ventas del sistema (sin monto inicial)
          
          // ✅ DIFERENCIAS CORRECTAS:
          diferencia: this.totalContado - this.getPaymentMethodSales('efectivo'), // Solo efectivo vs ventas efectivo
          diferencia_general: (this.totalContado + this.totalOtrosMedios) - this.totalVentasSistema, // Total arqueo vs total ventas
          
          // ✅ ESTADO BASADO EN DIFERENCIA GENERAL
          estado: this.getEstadoArqueo(),
          observaciones: this.observaciones,
          detalle_efectivo: JSON.stringify(this.getDetalleConteo()),
          detalle_medios_pago: JSON.stringify(this.mediosPago),
          numero_transacciones: (this.resumenDia && this.resumenDia.total_transacciones) || 0
        };
        
        console.log('🚀 DATOS ENVIADOS AL BACKEND (CORREGIDOS):');
        console.log('💰 Monto inicial del turno:', data.monto_inicial);
        console.log('💵 Efectivo contado (monto_final):', data.monto_final);
        console.log('💳 Métodos de pago diversos:', data.total_otros_medios);
        console.log('📊 Total contado (SOLO EFECTIVO):', data.total_contado);
        console.log('🏆 Total general (efectivo + otros):', data.total_general);
        console.log('💻 Ventas del sistema:', data.total_sistema);
        console.log('📊 Diferencia efectivo:', data.diferencia);
        console.log('📊 Diferencia general:', data.diferencia_general);
        console.log('✅ Estado calculado:', data.estado);
        console.log('📋 Objeto completo data:', data);
        
        // ✅ DEBUGGING DETALLADO DE CAMPOS MONETARIOS
        console.log('💰 DEBUGGING CAMPOS MONETARIOS:');
        console.log('  - monto_inicial (tipo/valor):', typeof data.monto_inicial, '/', data.monto_inicial);
        console.log('  - monto_final (tipo/valor):', typeof data.monto_final, '/', data.monto_final);
        console.log('  - total_contado (tipo/valor):', typeof data.total_contado, '/', data.total_contado);
        console.log('  - total_otros_medios (tipo/valor):', typeof data.total_otros_medios, '/', data.total_otros_medios);
        console.log('  - total_general_contado (tipo/valor):', typeof data.total_general_contado, '/', data.total_general_contado);
        console.log('  - diferencia_general (tipo/valor):', typeof data.diferencia_general, '/', data.diferencia_general);
        
        // 🔧 ARREGLO: Convertir a FormData como hacemos al iniciar turno
        console.log('📤 FORMDATA - Convirtiendo datos a FormData para compatibilidad backend...');
        const formData = new FormData();
        
        // Agregar todos los campos al FormData
        for (const [key, value] of Object.entries(data)) {
          formData.append(key, value);
        }
        
        // 🔍 DEBUG: Mostrar lo que se está enviando como FormData
        console.log('📋 FormData enviado:');
        for (let pair of formData.entries()) {
          console.log(`  📄 ${pair[0]} = ${pair[1]}`);
        }
        
        try {
          // ✅ USAR STORE REAL enviando FormData en lugar de objeto JS
          console.log('💾 Guardando arqueo en base de datos con FormData...');
          let request = await this.$store.dispatch('arqueo/cerrarTurnoConArqueo', formData);
          
          if (request.success) {
            this.arqueoGuardado = true;
            this.$awn.success('Arqueo guardado correctamente en base de datos', { labels: { success: 'CORRECTO' } });
            
            // Obtener ID del arqueo guardado con mejor manejo
            let arqueoId = 'Sin ID';
            if (request.data) {
              if (request.data.arqueo && request.data.arqueo.id) {
                arqueoId = request.data.arqueo.id;
              } else if (request.data.id) {
                arqueoId = request.data.id;
              } else if (request.data.turno && request.data.turno.id) {
                arqueoId = `Turno-${request.data.turno.id}`;
              }
            }
            
            console.log('✅ Arqueo guardado en la base de datos con ID:', arqueoId);
            console.log('📊 Respuesta completa del servidor:', request);
            
            // Cerrar turno después de guardar
            this.turnoActivo = false;
            
            // Limpiar localStorage del turno
            localStorage.removeItem('turnoActivo');
            localStorage.removeItem('fechaTurno');
            localStorage.removeItem('horaInicioTurno');
            localStorage.removeItem('montoInicialTurno');
            
            // Notificar al layout que el turno cambió
            this.notificarCambioTurno();
            
            // ✅ CARGAR DATOS DE VENTAS DESPUÉS DE GUARDAR para mostrar comparación
            console.log('🔄 Cargando ventas del sistema para comparación...');
            await this.cargarVentasDelSistema();
            
            // Recargar historial desde la base de datos
            await this.cargarHistorial();
            
            // ⚡ MODO SILENCIOSO - Sin mensajes molestos de diferencias
            // Solo mensaje de confirmación exitosa
            setTimeout(() => {
              this.$awn.success('Turno cerrado correctamente.');
            }, 1500);
            
          } else {
            console.log(request.data);
            this.$awn.alert(request.data.message || 'Error al guardar el arqueo');
          }
          
        } catch (innerError) {
          console.error('❌ Error guardando arqueo:', innerError);
          console.error('📋 Detalles del error:', {
            message: innerError.message,
            response: innerError.response,
            status: innerError.status,
            data: innerError.data
          });
          this.$awn.alert('Error al guardar arqueo: ' + (innerError.message || 'Error desconocido'));
        }
        
      } catch (outerError) {
        console.error('❌ Error general en confirmarGuardado:', outerError);
        this.$awn.alert('Error al procesar arqueo: ' + outerError.message);
      } finally {
        this.loading = false;
      }
    },
    
    getDetalleConteo() {
      const detalle = {};
      const todasDenominaciones = { ...this.billetes, ...this.monedas };
      
      console.log('📝 Generando detalle del conteo...');
      console.log('🔢 Conteo actual:', this.conteo);
      
      for (let denominacion in todasDenominaciones) {
        const cantidad = parseInt(this.conteo[denominacion]) || 0;
        if (cantidad > 0) {
          detalle[denominacion] = {
            cantidad: cantidad,
            valor: todasDenominaciones[denominacion],
            subtotal: cantidad * todasDenominaciones[denominacion]
          };
          console.log(`✅ ${denominacion}: ${cantidad} x ${todasDenominaciones[denominacion]} = ${detalle[denominacion].subtotal}`);
        }
      }
      
      console.log('📦 Detalle final:', detalle);
      return detalle;
    },
    
    limpiarFormulario() {
      // Limpiar conteo de efectivo
      for (let denominacion in this.conteo) {
        this.conteo[denominacion] = 0;
      }
      
      // Limpiar métodos de pago diversos
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

    // ✅ ELIMINADO: Ya no guardamos en localStorage, solo BD
    // guardarEnHistorialLocal - MÉTODO ELIMINADO

    // ✅ ELIMINADO: Ya no usamos localStorage para historial
    // cargarHistorialLocal - MÉTODO ELIMINADO

    // ✅ ELIMINADO: Ya no manejamos localStorage para historial
    // limpiarHistorial y eliminarArqueoHistorial - MÉTODOS ELIMINADOS

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
• Métodos diversos: $${this.formatMoney(arqueo.total_otros_medios)}
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
      if (diferencia > 0) return 'Registrado';
      return 'Registrado';
    }
  }
}
</script>

<style scoped>
/* Variables CSS */
:root {
  --primary-color: #3b82f6;
  --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --secondary-color: #64748b;
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --danger-color: #ef4444;
  --dark-color: #1e293b;
  --light-color: #f8fafc;
  --border-color: #e2e8f0;
  --shadow-soft: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-medium: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --shadow-large: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Contenedor Principal */
.arqueo-container {
  min-height: 100vh;
  background: 
    radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
    radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.2) 0%, transparent 50%),
    linear-gradient(135deg, #667eea 0%, #764ba2 20%, #6366f1 40%, #8b5cf6 60%, #a855f7 80%, #c084fc 100%);
  padding: 20px;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  position: relative;
  overflow: hidden;
}

/* Elementos decorativos de fondo */
.arqueo-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: 
    radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 2px, transparent 2px),
    radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
  background-size: 80px 80px, 40px 40px;
  animation: float 20s ease-in-out infinite;
  pointer-events: none;
  z-index: 0;
}

.arqueo-container::after {
  content: '';
  position: absolute;
  top: 20%;
  right: 10%;
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
  border-radius: 50%;
  animation: pulse 4s ease-in-out infinite;
  pointer-events: none;
  z-index: 0;
}

/* Círculos decorativos flotantes */
.arqueo-container > * {
  position: relative;
  z-index: 1;
}

/* Elementos decorativos de fondo */
.bg-decoration {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  pointer-events: none;
  z-index: 0;
}

.floating-circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.05);
  animation: float 15s ease-in-out infinite;
}

.circle-1 {
  width: 200px;
  height: 200px;
  top: 10%;
  left: 10%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
  animation-delay: 0s;
}

.circle-2 {
  width: 150px;
  height: 150px;
  top: 60%;
  right: 15%;
  background: radial-gradient(circle, rgba(167, 139, 250, 0.1) 0%, transparent 70%);
  animation-delay: 2s;
}

.circle-3 {
  width: 100px;
  height: 100px;
  top: 80%;
  left: 70%;
  background: radial-gradient(circle, rgba(34, 197, 94, 0.1) 0%, transparent 70%);
  animation-delay: 4s;
}

.floating-shape {
  position: absolute;
  background: rgba(255, 255, 255, 0.03);
  animation: float 20s linear infinite;
}

.shape-1 {
  width: 60px;
  height: 60px;
  top: 30%;
  left: 80%;
  border-radius: 20px;
  background: linear-gradient(45deg, rgba(59, 130, 246, 0.1), rgba(147, 51, 234, 0.1));
  animation-delay: 1s;
}

.shape-2 {
  width: 80px;
  height: 80px;
  top: 20%;
  left: 30%;
  border-radius: 15px;
  background: linear-gradient(-45deg, rgba(34, 197, 94, 0.1), rgba(59, 130, 246, 0.1));
  animation-delay: 3s;
}

/* Header Moderno */
.modern-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 30px;
  margin-bottom: 30px;
  box-shadow: var(--shadow-large);
  border: 1px solid rgba(255, 255, 255, 0.2);
  position: relative;
  overflow: hidden;
}

.modern-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--primary-gradient);
  z-index: 0;
}

.modern-header::after {
  content: '';
  position: absolute;
  top: -50px;
  right: -50px;
  width: 100px;
  height: 100px;
  background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
  border-radius: 50%;
  z-index: 0;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header-title-section {
  display: flex;
  align-items: center;
  gap: 16px;
}

.title-icon {
  width: 60px;
  height: 60px;
  background: var(--primary-gradient);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
  box-shadow: var(--shadow-medium);
}

.title-text h1 {
  margin: 0;
  font-size: 28px;
  font-weight: 800;
  color: var(--dark-color) !important;
  /* Removemos el gradiente que causa problemas */
  /* background: var(--primary-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text; */
}

.title-text p {
  margin: 0;
  color: var(--secondary-color) !important;
  font-size: 14px;
  font-weight: 500;
}

.header-status .status-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  border-radius: 50px;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s ease;
}

.status-indicator.active {
  background: rgba(16, 185, 129, 0.1);
  color: var(--success-color);
  border: 2px solid rgba(16, 185, 129, 0.2);
}

.status-indicator.inactive {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-color);
  border: 2px solid rgba(239, 68, 68, 0.2);
}

/* Info Cards */
.info-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.info-card {
  background: rgba(255, 255, 255, 0.6);
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
}

.info-card:hover {
  background: rgba(255, 255, 255, 0.8);
  transform: translateY(-2px);
}

.info-card i {
  width: 48px;
  height: 48px;
  background: var(--primary-gradient);
  color: white !important;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.card-content {
  flex: 1;
}

.card-label {
  display: block;
  font-size: 12px;
  color: #64748b !important;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.card-value {
  display: block;
  font-size: 16px;
  font-weight: 700;
  color: #1e293b !important;
}

/* Sin Turno State */
.no-turno-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 60px 40px;
  text-align: center;
  box-shadow: var(--shadow-large);
  border: 1px solid rgba(255, 255, 255, 0.2);
  position: relative;
  overflow: hidden;
}

.no-turno-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: 
    radial-gradient(circle at 20px 20px, rgba(59, 130, 246, 0.03) 1px, transparent 1px);
  background-size: 40px 40px;
  z-index: 0;
}

.no-turno-section > * {
  position: relative;
  z-index: 1;
}

.empty-state {
  max-width: 400px;
  margin: 0 auto;
}

.empty-icon {
  width: 100px;
  height: 100px;
  background: var(--primary-gradient);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
  color: white;
  font-size: 40px;
  box-shadow: var(--shadow-large);
}

.empty-state h3 {
  font-size: 24px;
  font-weight: 700;
  color: var(--dark-color) !important;
  margin-bottom: 8px;
}

.empty-state p {
  color: var(--secondary-color) !important;
  font-size: 16px;
  margin-bottom: 32px;
}

/* Botones */
.btn-primary-large {
  background: var(--primary-gradient);
  border: none;
  color: white;
  padding: 16px 32px;
  border-radius: 16px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: var(--shadow-medium);
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary-large:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: var(--shadow-large);
}

.btn-primary-large:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary-small {
  background: rgba(100, 116, 139, 0.1);
  border: 1px solid rgba(100, 116, 139, 0.2);
  color: var(--secondary-color);
  padding: 8px 16px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-secondary-small:hover:not(:disabled) {
  background: rgba(100, 116, 139, 0.2);
}

.btn-danger-large {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  border: none;
  color: white;
  padding: 16px 32px;
  border-radius: 16px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: var(--shadow-medium);
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-danger-large:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: var(--shadow-large);
}

.btn-danger-large:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-outline {
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.3);
  color: var(--dark-color);
  padding: 12px 24px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-outline:hover {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.5);
}

/* Turno Activo */
.turno-activo {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

/* Resumen del Turno */
.turno-summary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.summary-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: var(--shadow-medium);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.summary-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-large);
}

.summary-card.initial .card-icon {
  background: linear-gradient(135deg, #10b981, #059669);
}

.summary-card.current .card-icon {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.summary-card.total .card-icon {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.summary-card.expense-card .card-icon.expense-icon {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}

.summary-card.final-card .card-icon.final-icon {
  background: linear-gradient(135deg, #10b981, #059669);
}

.expense-amount {
  color: #ef4444 !important;
  font-weight: 700;
}

.final-highlight {
  color: #10b981 !important;
  font-weight: 700;
}

.card-icon {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
}

.card-info {
  flex: 1;
}

.card-title {
  display: block;
  font-size: 12px;
  color: var(--secondary-color) !important;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}

.card-amount {
  display: block;
  font-size: 20px;
  font-weight: 800;
  color: var(--dark-color) !important;
}

.total-highlight {
  color: var(--primary-color) !important;
  font-size: 24px !important;
  /* Removemos el gradiente que causa problemas */
  /* background: var(--primary-gradient) !important;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text; */
}

/* Contenido Principal */
.main-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 30px;
  max-width: 1300px;
  margin: 0 auto;
}

@media (max-width: 1200px) {
  .main-content {
    max-width: 100%;
  }
}

/* Panel de Conteo */
.conteo-panel {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 30px;
  box-shadow: var(--shadow-large);
  border: 1px solid rgba(255, 255, 255, 0.2);
  position: relative;
  overflow: hidden;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

.conteo-panel::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: 
    linear-gradient(45deg, rgba(59, 130, 246, 0.02) 25%, transparent 25%), 
    linear-gradient(-45deg, rgba(59, 130, 246, 0.02) 25%, transparent 25%), 
    linear-gradient(45deg, transparent 75%, rgba(59, 130, 246, 0.02) 75%), 
    linear-gradient(-45deg, transparent 75%, rgba(59, 130, 246, 0.02) 75%);
  background-size: 60px 60px;
  background-position: 0 0, 0 30px, 30px -30px, -30px 0px;
  z-index: 0;
}

.conteo-panel > * {
  position: relative;
  z-index: 1;
}

.panel-header {
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid rgba(59, 130, 246, 0.1);
  text-align: center;
}

.panel-header h3 {
  font-size: 22px;
  font-weight: 700;
  color: var(--dark-color) !important;
  margin: 0;
}

/* Grupos de Denominación */
.denomination-group {
  margin-bottom: 40px;
}

.group-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--dark-color) !important;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.denomination-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  justify-items: center;
}

.denomination-grid.small {
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

.denomination-item {
  background: linear-gradient(135deg, #f8fafc, #e2e8f0);
  border: 2px solid rgba(59, 130, 246, 0.1);
  border-radius: 16px;
  padding: 20px;
  text-align: center;
  transition: all 0.2s ease;
  width: 140px;
  min-height: 120px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.denomination-item:hover {
  border-color: var(--primary-color);
  box-shadow: var(--shadow-medium);
  transform: translateY(-1px);
}

.denomination-item:hover {
  border-color: var(--primary-color);
  box-shadow: var(--shadow-medium);
  transform: translateY(-2px);
}

.denom-value {
  font-size: 16px;
  font-weight: 800;
  color: var(--primary-color) !important;
  margin-bottom: 8px;
}

.denom-input {
  width: 100%;
  padding: 10px;
  border: 2px solid rgba(226, 232, 240, 0.5);
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  text-align: center;
  background: white;
  transition: all 0.3s ease;
  margin-bottom: 8px;
}

.denom-input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.denom-label {
  font-size: 12px;
  color: var(--secondary-color) !important;
  font-weight: 500;
}

/* Medios de Pago */
.medios-group {
  margin-bottom: 40px;
}

.group-subtitle {
  color: var(--secondary-color) !important;
  font-size: 14px;
  margin-bottom: 20px;
  font-style: italic;
}

.medios-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.medio-item {
  background: linear-gradient(135deg, #ffffff, #f8fafc);
  border: 2px solid rgba(226, 232, 240, 0.5);
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.3s ease;
}

.medio-item:hover {
  border-color: var(--primary-color);
  box-shadow: var(--shadow-medium);
  transform: translateY(-2px);
}

.medio-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: white;
}

.icon-primary { background: var(--primary-gradient); }
.icon-success { background: linear-gradient(135deg, #10b981, #059669); }
.icon-warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
.icon-info { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.icon-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

.medio-info {
  flex: 1;
}

.medio-name {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: var(--dark-color) !important;
  margin-bottom: 8px;
}

.medio-input {
  width: 100%;
  padding: 10px;
  border: 2px solid rgba(226, 232, 240, 0.5);
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  background: white;
  transition: all 0.3s ease;
}

.medio-input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Observaciones */
.observaciones-section {
  margin-bottom: 40px;
}

.obs-label {
  display: block;
  font-size: 16px;
  font-weight: 600;
  color: var(--dark-color) !important;
  margin-bottom: 12px;
}

.obs-textarea {
  width: 100%;
  padding: 16px;
  border: 2px solid rgba(226, 232, 240, 0.5);
  border-radius: 16px;
  font-size: 14px;
  background: white;
  resize: vertical;
  transition: all 0.3s ease;
  font-family: inherit;
}

.obs-textarea:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Botones de Acción */
.action-buttons {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
}

/* Panel de Resultados */
.resultados-panel {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 30px;
  box-shadow: var(--shadow-large);
  border: 1px solid rgba(255, 255, 255, 0.2);
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

/* Comparación Cards */
.comparison-card {
  background: linear-gradient(135deg, #ffffff, #f8fafc);
  border: 2px solid rgba(226, 232, 240, 0.5);
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 16px;
  transition: all 0.3s ease;
}

.comparison-card:hover {
  box-shadow: var(--shadow-medium);
  transform: translateY(-2px);
}

.comparison-card.efectivo {
  border-color: var(--success-color);
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(16, 185, 129, 0.02));
}

.comparison-header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 16px;
  font-weight: 700;
  color: var(--dark-color) !important;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(226, 232, 240, 0.5);
}

.comparison-data {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.data-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  color: var(--dark-color) !important;
}

.data-row.difference {
  padding-top: 8px;
  border-top: 1px solid rgba(226, 232, 240, 0.5);
  font-weight: 600;
}

.amount {
  font-weight: 600;
}

.amount.positive {
  color: var(--success-color) !important;
}

.amount.negative {
  color: var(--danger-color) !important;
}

/* Resultado Final */
.resultado-final {
  background: linear-gradient(135deg, #f8fafc, #e2e8f0);
  border-radius: 20px;
  padding: 24px;
  text-align: center;
  margin-top: 24px;
  border: 2px solid rgba(226, 232, 240, 0.5);
}

.resultado-final.perfecto {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
  border-color: var(--success-color);
}

.resultado-final.sobrante {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));
  border-color: var(--warning-color);
}

.resultado-final.faltante {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
  border-color: var(--danger-color);
}

.resultado-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
  font-size: 24px;
  color: white;
}

.resultado-final.perfecto .resultado-icon {
  background: var(--success-color);
}

.resultado-final.sobrante .resultado-icon {
  background: var(--warning-color);
}

.resultado-final.faltante .resultado-icon {
  background: var(--danger-color);
}

.resultado-info h4 {
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 8px;
  color: var(--dark-color) !important;
}

.resultado-info p {
  font-size: 16px;
  margin: 0;
  opacity: 0.8;
  color: var(--secondary-color) !important;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 24px;
  padding: 32px;
  max-width: 400px;
  width: 100%;
  box-shadow: var(--shadow-large);
  animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-header h3 {
  font-size: 20px;
  font-weight: 700;
  color: var(--dark-color) !important;
  margin: 0 0 20px 0;
}

.modal-body p {
  color: var(--secondary-color) !important;
  margin-bottom: 20px;
}

.modal-input {
  width: 100%;
  padding: 16px;
  border: 2px solid rgba(226, 232, 240, 0.5);
  border-radius: 16px;
  font-size: 16px;
  font-weight: 600;
  text-align: center;
  background: var(--light-color);
  transition: all 0.3s ease;
  margin-bottom: 24px;
}

.modal-input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.modal-footer {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.btn-secondary, .btn-primary {
  padding: 12px 20px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-secondary {
  background: rgba(100, 116, 139, 0.1);
  color: var(--secondary-color) !important;
  border: 1px solid rgba(100, 116, 139, 0.2);
}

.btn-secondary:hover {
  background: rgba(100, 116, 139, 0.2);
}

.btn-primary {
  background: var(--primary-gradient);
  color: white;
  box-shadow: var(--shadow-medium);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-large);
}

/* Utilidades */
.me-1 { margin-right: 4px; }
.me-2 { margin-right: 8px; }
.mt-4 { margin-top: 24px; }

/* Forzar visibilidad de todos los textos */
.arqueo-container * {
  color: inherit !important;
}

.arqueo-container .card-label,
.arqueo-container .card-value,
.arqueo-container .card-title,
.arqueo-container .card-amount,
.arqueo-container h1,
.arqueo-container h2,
.arqueo-container h3,
.arqueo-container h4,
.arqueo-container h5,
.arqueo-container h6,
.arqueo-container p,
.arqueo-container span,
.arqueo-container div {
  color: var(--dark-color) !important;
}

.arqueo-container .card-label {
  color: var(--secondary-color) !important;
}

.arqueo-container .title-text h1 {
  color: var(--dark-color) !important;
}

.arqueo-container .title-text p {
  color: var(--secondary-color) !important;
}

.arqueo-container .status-indicator span {
  color: inherit !important;
}

/* Reglas súper específicas para info-cards */
.modern-header .info-cards .info-card .card-content .card-label {
  color: #64748b !important;
  font-size: 12px !important;
  font-weight: 500 !important;
  text-transform: uppercase !important;
}

.modern-header .info-cards .info-card .card-content .card-value {
  color: #1e293b !important;
  font-size: 16px !important;
  font-weight: 700 !important;
}

/* Reglas para summary cards también */
.turno-summary .summary-card .card-info .card-title {
  color: #64748b !important;
  font-size: 12px !important;
  font-weight: 600 !important;
  text-transform: uppercase !important;
}

.turno-summary .summary-card .card-info .card-amount {
  color: #1e293b !important;
  font-size: 20px !important;
  font-weight: 800 !important;
}

/* SOLUCIÓN DEFINITIVA - Forzar todos los colores */
.info-cards .info-card .card-label,
.info-cards .info-card span.card-label {
  color: #64748b !important;
  opacity: 1 !important;
  visibility: visible !important;
}

.info-cards .info-card .card-value,
.info-cards .info-card span.card-value {
  color: #1e293b !important;
  opacity: 1 !important;
  visibility: visible !important;
}

/* También para las summary cards */
.turno-summary .summary-card .card-title,
.turno-summary .summary-card span.card-title {
  color: #64748b !important;
  opacity: 1 !important;
  visibility: visible !important;
}

.turno-summary .summary-card .card-amount,
.turno-summary .summary-card span.card-amount {
  color: #1e293b !important;
  opacity: 1 !important;
  visibility: visible !important;
}

/* Responsive */
@media (max-width: 768px) {
  .arqueo-container {
    padding: 16px;
  }
  
  .modern-header {
    padding: 20px;
  }
  
  .header-content {
    flex-direction: column;
    gap: 20px;
    text-align: center;
  }
  
  .info-cards {
    grid-template-columns: 1fr;
  }
  
  .turno-summary {
    grid-template-columns: 1fr;
  }
  
  .denomination-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  
  .denomination-grid.small {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .denomination-item {
    width: 100%;
    min-height: 100px;
  }
  
  .medios-grid {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .btn-primary-large,
  .btn-danger-large {
    width: 100%;
    justify-content: center;
  }
  
  .conteo-panel,
  .resultados-panel {
    max-width: 100%;
    padding: 20px;
  }
}

@media (max-width: 1000px) {
  .denomination-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .denomination-grid.small {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Animaciones */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes float {
  0%, 100% {
    transform: translate(0px, 0px) rotate(0deg);
  }
  33% {
    transform: translate(30px, -30px) rotate(120deg);
  }
  66% {
    transform: translate(-20px, 20px) rotate(240deg);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 0.3;
    transform: scale(1);
  }
  50% {
    opacity: 0.1;
    transform: scale(1.1);
  }
}

@keyframes shimmer {
  /* Animación deshabilitada para evitar movimientos locos */
}

@keyframes bounce {
  /* Animación deshabilitada para evitar movimientos locos */
}

.turno-activo {
  animation: fadeIn 0.5s ease;
}

.summary-card {
  animation: slideUp 0.5s ease;
}

.summary-card:nth-child(2) {
  animation-delay: 0.1s;
}

.summary-card:nth-child(3) {
  animation-delay: 0.2s;
}

/* Efectos de hover mejorados */
.info-card:hover {
  background: rgba(255, 255, 255, 0.8);
  transform: translateY(-2px);
  /* Removemos la animación bounce que también causaba movimiento */
}

/* Continuación de estilos - sin cerrar/abrir style tag */
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

/* ======================================
   MODAL DE AUDITORÍA ELEGANTE - STYLES
   ====================================== */

/* Overlay del Modal */
.audit-modal {
  backdrop-filter: blur(12px);
  background: rgba(0, 0, 0, 0.7);
  z-index: 10000;
}

/* Container del Modal Elegante */
.elegant-modal {
  background: linear-gradient(145deg, #ffffff, #f8fafc);
  border-radius: 24px;
  box-shadow: 
    0 25px 80px rgba(0, 0, 0, 0.3),
    0 0 60px rgba(102, 126, 234, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.9);
  max-width: 580px;
  width: 90%;
  max-height: 90vh; /* Aumenté a 90vh */
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.2);
  position: relative;
  animation: modalAppear 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  display: flex;
  flex-direction: column; /* Flexbox para mejor control */
}

@keyframes modalAppear {
  0% {
    transform: scale(0.8) translateY(-50px);
    opacity: 0;
  }
  100% {
    transform: scale(1) translateY(0);
    opacity: 1;
  }
}

/* Header del Modal Elegante */
.elegant-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 1.5rem 2rem 1.25rem; /* Reduje padding */
  color: white;
  position: relative;
  overflow: hidden;
  flex-shrink: 0; /* No se encoge */
}

.elegant-header::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -20%;
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
  border-radius: 50%;
  animation: headerGlow 3s ease-in-out infinite;
}

@keyframes headerGlow {
  0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.3; }
  50% { transform: scale(1.1) rotate(180deg); opacity: 0.1; }
}

.audit-icon {
  background: rgba(255, 255, 255, 0.15);
  border-radius: 50%;
  width: 60px; /* Reduje de 70px */
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 0.75rem; /* Reduje margin */
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.2);
  position: relative;
  z-index: 2;
}

.audit-icon i {
  font-size: 1.75rem; /* Reduje de 2rem */
  color: #ffd700;
  text-shadow: 0 2px 8px rgba(0,0,0,0.3);
  animation: iconPulse 2s infinite;
}

@keyframes iconPulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.modal-title {
  margin: 0;
  font-size: 1.35rem; /* Reduje de 1.5rem */
  font-weight: 700;
  text-align: center;
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
  position: relative;
  z-index: 2;
}

.security-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 0.75rem; /* Reduje margin */
  padding: 0.4rem 0.75rem; /* Reduje padding */
  background: rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  font-size: 0.8rem; /* Reduje font */
  font-weight: 600;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  position: relative;
  z-index: 2;
}

.security-badge i {
  color: #ffd700;
}

/* Body del Modal */
.elegant-body {
  padding: 1.5rem 2rem; /* Reduje padding */
  background: white;
  position: relative;
  flex: 1; /* Toma el espacio disponible */
  overflow-y: auto; /* SCROLL AQUÍ */
  overflow-x: hidden;
  /* Estilizar scrollbar */
  scrollbar-width: thin;
  scrollbar-color: #667eea #f1f5f9;
}

/* Webkit scrollbar para Chrome/Safari */
.elegant-body::-webkit-scrollbar {
  width: 6px;
}

.elegant-body::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.elegant-body::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 3px;
}

.elegant-body::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #5a67d8, #553c9a);
}

.confirmation-message {
  text-align: center;
}

.greeting-section {
  margin-bottom: 1.25rem; /* Reduje margin */
}

.main-message {
  font-size: 1.1rem;
  color: #2d3748;
  line-height: 1.6;
  margin: 0;
}

.main-message strong {
  color: #667eea;
  font-weight: 700;
}

/* Summary Box */
.summary-box {
  background: linear-gradient(135deg, #f7fafc, #edf2f7);
  border-radius: 16px;
  padding: 1.25rem; /* Reduje padding */
  margin: 1.25rem 0; /* Reduje margin */
  border: 1px solid #e2e8f0;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.summary-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem; /* Reduje margin */
  font-weight: 600;
  color: #4a5568;
  font-size: 0.95rem; /* Reduje font */
}

.summary-header i {
  color: #667eea;
  font-size: 1.1rem;
}

.summary-content {
  display: grid;
  gap: 0.75rem;
}

.info-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid rgba(226, 232, 240, 0.5);
}

.info-item:last-child {
  border-bottom: none;
}

.info-item i {
  color: #667eea;
  width: 20px;
  margin-right: 0.5rem;
}

.info-item .value {
  font-weight: 600;
  color: #2d3748;
}

.info-item .value.highlight {
  color: #667eea;
  font-size: 1.1rem;
  background: linear-gradient(90deg, #667eea, #764ba2);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Audit Notice */
.audit-notice {
  background: linear-gradient(135deg, #f0f4ff, #e6f3ff);
  border-radius: 16px;
  padding: 1.25rem; /* Reduje padding */
  margin-top: 1.25rem; /* Reduje margin */
  border: 1px solid rgba(102, 126, 234, 0.1);
  position: relative;
  overflow: hidden;
}

.audit-notice::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #667eea, #764ba2);
}

.audit-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.75rem; /* Reduje margin */
}

.audit-icon-circle {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 50%;
  width: 36px; /* Reduje de 40px */
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.audit-icon-circle i {
  color: white;
  font-size: 1.1rem; /* Reduje font */
}

.audit-header h4 {
  margin: 0;
  color: #2d3748;
  font-size: 1.05rem; /* Reduje font */
  font-weight: 700;
}

.audit-content {
  text-align: left;
}

.audit-main {
  color: #4a5568;
  line-height: 1.6;
  margin: 0 0 0.75rem 0; /* Reduje margin */
  font-size: 0.9rem; /* Reduje font */
}

.audit-details {
  display: grid;
  gap: 0.5rem; /* Reduje gap */
  margin: 0.75rem 0; /* Reduje margin */
}

.audit-point {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.4rem 0; /* Reduje padding */
  font-size: 0.85rem; /* Reduje font */
  color: #4a5568;
}

.audit-point i {
  color: #48bb78;
  font-size: 1rem;
  min-width: 16px;
}

.audit-footnote {
  background: rgba(102, 126, 234, 0.05);
  border-left: 3px solid #667eea;
  padding: 0.75rem; /* Reduje padding */
  border-radius: 0 8px 8px 0;
  margin-top: 0.75rem; /* Reduje margin */
}

.audit-footnote small {
  color: #718096;
  line-height: 1.5;
  font-size: 0.85rem;
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
}

.audit-footnote i {
  color: #667eea;
  margin-top: 2px;
  min-width: 14px;
}

/* Footer del Modal */
.elegant-footer {
  padding: 1.25rem 2rem; /* Reduje padding */
  background: linear-gradient(135deg, #f8fafc, #ffffff);
  border-top: 1px solid #e2e8f0;
  display: flex;
  gap: 1rem;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0; /* No se encoge */
}

.elegant-cancel {
  background: #718096;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
}

.elegant-cancel:hover {
  background: #4a5568;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(113, 128, 150, 0.3);
}

.elegant-confirm {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  min-width: 200px;
  position: relative;
  overflow: hidden;
}

.elegant-confirm::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s ease;
}

.elegant-confirm:hover::before {
  left: 100%;
}

.elegant-confirm:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.elegant-confirm span {
  font-size: 0.95rem;
  position: relative;
  z-index: 2;
}

.elegant-confirm small {
  font-size: 0.75rem;
  opacity: 0.9;
  font-weight: 500;
  position: relative;
  z-index: 2;
}

/* Responsive para el modal */
@media (max-width: 640px) {
  .elegant-modal {
    width: 95%;
    max-height: 90vh;
  }
  
  .elegant-header {
    padding: 1.5rem 1.5rem 1rem;
  }
  
  .elegant-body {
    padding: 1.5rem;
  }
  
  .elegant-footer {
    padding: 1rem 1.5rem;
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .elegant-confirm {
    width: 100%;
  }
  
  .elegant-cancel {
    width: 100%;
    justify-content: center;
  }
  
  .modal-title {
    font-size: 1.25rem;
  }
  
  .audit-icon {
    width: 60px;
    height: 60px;
  }
  
  .audit-icon i {
    font-size: 1.5rem;
  }
}

/* Modal de Gastos */
.gastos-modal {
  max-width: 600px;
  width: 90%;
}

.expense-modal-icon {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 28px;
  margin: 0 auto 15px;
}

.btn-close-modal {
  position: absolute;
  top: 15px;
  right: 15px;
  background: rgba(0, 0, 0, 0.1);
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  color: #64748b;
}

.btn-close-modal:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  transform: rotate(90deg);
}

.gastos-body {
  max-height: 400px;
  overflow-y: auto;
}

.gastos-modal .empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #64748b;
}

.gastos-modal .empty-state i {
  font-size: 48px;
  color: #10b981;
  margin-bottom: 15px;
}

.gastos-modal .empty-state p {
  font-size: 16px;
  font-weight: 500;
  margin-bottom: 5px;
}

.gastos-modal .empty-state small {
  font-size: 13px;
  color: #94a3b8;
}

.gastos-list {
  background: #f8fafc;
  border-radius: 12px;
  padding: 16px;
}

.gastos-header {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 20px;
  padding: 12px 16px;
  font-weight: 600;
  font-size: 13px;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #e2e8f0;
  margin-bottom: 8px;
}

.gasto-item {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 20px;
  padding: 14px 16px;
  background: white;
  border-radius: 10px;
  margin-bottom: 8px;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
}

.gasto-item:hover {
  border-color: #ef4444;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.1);
  transform: translateX(2px);
}

.gasto-concepto {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #1e293b;
}

.gasto-concepto i {
  color: #ef4444;
  font-size: 16px;
}

.gasto-concepto span {
  font-weight: 500;
}

.gasto-monto {
  display: flex;
  align-items: center;
}

.monto-value {
  font-size: 16px;
  font-weight: 700;
  color: #ef4444;
}

.gastos-total {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 20px;
  padding: 16px;
  margin-top: 12px;
  background: linear-gradient(135deg, #fef2f2, #fee2e2);
  border-radius: 10px;
  border: 2px solid #ef4444;
}

.total-label {
  font-weight: 700;
  font-size: 16px;
  color: #1e293b;
}

.total-value {
  font-weight: 800;
  font-size: 18px;
  color: #ef4444;
}

/* Hover en tarjeta de gastos */
.expense-card:hover {
  border-color: #ef4444;
  box-shadow: 0 8px 24px rgba(239, 68, 68, 0.15);
}
</style>
