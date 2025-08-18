<template>
  <div class="row p-4">
    <!-- Header -->
    <div class="col-12 mb-4">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0">
            <i class="fas fa-file-invoice-dollar me-2"></i>
            Gestión de Facturas
          </h4>
          <small>Listado de todas las facturas emitidas en el sistema</small>
        </div>
      </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="col-12 mb-4">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="fas fa-filter me-2"></i>
            Filtros de Búsqueda
          </h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-2">
              <label class="form-label">Folio:</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="filtros.folio"
                placeholder="Número de folio"
              >
            </div>
            <div class="col-md-3">
              <label class="form-label">RUT Cliente:</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="filtros.rut"
                placeholder="12345678-9"
              >
            </div>
            <div class="col-md-3">
              <label class="form-label">Razón Social:</label>
              <input 
                type="text" 
                class="form-control" 
                v-model="filtros.razonSocial"
                placeholder="Nombre del cliente"
              >
            </div>
            <div class="col-md-2">
              <label class="form-label">Fecha Desde:</label>
              <input 
                type="date" 
                class="form-control" 
                v-model="filtros.fechaDesde"
              >
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <div class="btn-group w-100">
                <button 
                  class="btn btn-primary" 
                  @click="buscarFacturas"
                  :disabled="loading"
                >
                  <i class="fas fa-search me-1"></i>
                  Buscar
                </button>
                <button 
                  class="btn btn-success" 
                  @click="cargarTodasLasFacturas"
                  :disabled="loading"
                >
                  <i class="fas fa-list me-1"></i>
                  Todas
                </button>
              </div>
            </div>
          </div>
          <!-- Segunda fila: Selector de Franquiciados -->
          <div class="row mt-3">
            <div class="col-md-4">
              <label class="form-label">Seleccionar Franquiciado:</label>
              <select 
                class="form-control"
                v-model="filtros.franquiciadoSeleccionado"
                @change="aplicarFiltroFranquiciado"
              >
                <option value="">Todos los franquiciados</option>
                <option 
                  v-for="franquiciado in franquiciadosUnicos" 
                  :key="franquiciado.rut"
                  :value="franquiciado.rut"
                >
                  {{ franquiciado.nombre }} ({{ franquiciado.rut }})
                </option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Fecha Hasta:</label>
              <input 
                type="date" 
                class="form-control" 
                v-model="filtros.fechaHasta"
              >
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button 
                class="btn btn-warning w-100" 
                @click="limpiarFiltros"
              >
                <i class="fas fa-broom me-1"></i>
                Limpiar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Estadísticas -->
    <div class="col-12 mb-4" v-if="facturas.length > 0">
      <div class="row">
        <div class="col-md-3">
          <div class="card bg-primary text-white">
            <div class="card-body text-center">
              <h3>{{ facturas.length }}</h3>
              <p class="mb-0">Total Facturas</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-success text-white">
            <div class="card-body text-center">
              <h3>${{ formatNumber(totalMontoFacturas) }}</h3>
              <p class="mb-0">Monto Total</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-info text-white">
            <div class="card-body text-center">
              <h3>{{ clientesUnicos }}</h3>
              <p class="mb-0">Clientes Únicos</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-warning text-white">
            <div class="card-body text-center">
              <h3>${{ formatNumber(promedioFactura) }}</h3>
              <p class="mb-0">Promedio por Factura</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de Facturas -->
    <div class="col-12" v-if="facturas.length > 0">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>
            Facturas Encontradas ({{ facturas.length }})
          </h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead class="table-dark">
                <tr>
                  <th>Folio</th>
                  <th>Fecha</th>
                  <th>Cliente</th>
                  <th>RUT</th>
                  <th>Total</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="factura in facturas" :key="factura.id">
                  <td>
                    <strong class="text-primary">{{ factura.sell_folio || factura.id }}</strong>
                  </td>
                  <td>{{ formatDate(factura.created_at) }}</td>
                  <td>{{ (factura.client && factura.client.razon_social) || factura.fullname || 'Sin nombre' }}</td>
                  <td>{{ (factura.client && factura.client.rut) || 'Sin RUT' }}</td>
                  <td>
                    <span class="badge bg-success fs-6">
                      ${{ formatNumber(factura.total) }}
                    </span>
                  </td>
                  <td>
                    <span 
                      :class="getEstadoBadge(factura)"
                    >
                      {{ getEstadoTexto(factura) }}
                    </span>
                  </td>
                  <td>
                    <div class="btn-group" role="group">
                      <!-- Ver Detalles -->
                      <button 
                        class="btn btn-outline-info btn-sm"
                        @click="verDetalles(factura)"
                        title="Ver detalles de la factura"
                      >
                        <i class="fas fa-eye"></i>
                      </button>
                      
                      <!-- Ver PDF -->
                      <button 
                        class="btn btn-outline-primary btn-sm"
                        @click="verPDF(factura)"
                        title="Ver PDF de la factura"
                      >
                        <i class="fas fa-file-pdf"></i>
                      </button>
                      
                      <!-- Generar Nota de Crédito -->
                      <button 
                        class="btn btn-outline-danger btn-sm"
                        @click="abrirModalNotaCredito(factura)"
                        :disabled="factura.estado !== 'completed'"
                        title="Generar Nota de Crédito"
                      >
                        <i class="fas fa-ban"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Estado sin facturas -->
    <div class="col-12" v-if="facturas.length === 0 && !loading">
      <div class="card">
        <div class="card-body text-center py-5">
          <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No se encontraron facturas</h5>
          <p class="text-muted">No hay facturas que coincidan con los criterios de búsqueda.</p>
          <button class="btn btn-primary" @click="cargarTodasLasFacturas">
            <i class="fas fa-refresh me-1"></i>
            Cargar todas las facturas
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div class="col-12" v-if="loading">
      <div class="card">
        <div class="card-body text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
          <p class="mt-3 text-muted">Cargando facturas...</p>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import Connection from '@/helpers/Connection.js';
import BaseUrl from '@/helpers/baseUrl.js';
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
import Loader from '@/helpers/Loader';

export default {
  name: 'Franquiciados',
  data() {
    return {
      filtros: {
        folio: '',
        rut: '',
        razonSocial: '',
        fechaDesde: '',
        fechaHasta: '',
        franquiciadoSeleccionado: ''
      },
      facturas: [],
      todasLasFacturas: [], // Array para mantener todas las facturas sin filtrar
      loading: false
    };
  },
  computed: {
    totalMontoFacturas() {
      return this.facturas.reduce((sum, factura) => sum + parseFloat(factura.total || 0), 0);
    },
    clientesUnicos() {
      const rutUnicos = new Set(this.facturas.map(f => f.rut_cliente).filter(r => r));
      return rutUnicos.size;
    },
    promedioFactura() {
      return this.facturas.length > 0 ? this.totalMontoFacturas / this.facturas.length : 0;
    },
    franquiciadosUnicos() {
      const clientesMap = new Map();
      
      this.facturas.forEach(factura => {
        const rut = factura.rut_cliente;
        const nombre = (factura.client && factura.client.name) || factura.razon_social || 'Sin nombre';
        
        if (rut && !clientesMap.has(rut)) {
          clientesMap.set(rut, {
            rut: rut,
            nombre: nombre
          });
        }
      });
      
      return Array.from(clientesMap.values()).sort((a, b) => a.nombre.localeCompare(b.nombre));
    }
  },
  mounted() {
    console.log('📋 Módulo Franquiciados cargado');
    console.log('Store disponible:', !!this.$store);
    console.log('Sells module:', !!this.$store.state.sells);
    
    // Verificar autenticación
    const user = this.$store.state.main.user;
    console.log('Usuario autenticado:', user);
    
    if (!user || !user.id) {
      console.warn('⚠️ Usuario no autenticado, redirigiendo al login');
      this.$router.push('/login');
      return;
    }
    
    this.cargarTodasLasFacturas();
  },
  methods: {
    async cargarTodasLasFacturas() {
      this.loading = true;
      console.log('🔄 Iniciando carga de facturas...');
      try {
        // Volver al método que funcionaba: usar el store con más registros
        // Intentar cargar páginas múltiples para obtener más datos
        await this.cargarMultiplesPaginas();
        console.log('✅ Facturas cargadas:', this.facturas.length);
      } catch (error) {
        console.error('❌ Error al cargar facturas:', error);
        this.$awn.alert('Error al cargar las facturas');
      } finally {
        this.loading = false;
      }
    },

    async cargarMultiplesPaginas() {
      try {
        console.log('🔄 Iniciando cargarMultiplesPaginas...');
        
        // Primero intentar con el store
        try {
          const params = '?page=1&perpage=50';
          console.log('📤 Solicitando vía store:', params);
          const response = await this.$store.dispatch('sells/getSells', params);
          console.log('📥 Respuesta del store:', response);
          
          if (response && response.success && response.data && response.data.items) {
            console.log('✅ Datos del store encontrados:', response.data.items.length, 'facturas');
            this.facturas = response.data.items;
            this.todasLasFacturas = [...response.data.items];
            this.$awn.success(`${this.facturas.length} facturas cargadas (store)`);
            return;
          }
        } catch (storeError) {
          console.error('❌ Error con el store:', storeError);
        }
        
        // Si falla el store, intentar con Connection directo
        console.log('🔄 Intentando con Connection directo...');
        const url = BaseUrl.getUrl('api/local/sells?page=1&perpage=50');
        console.log('📤 URL directa:', url);
        
        const directResponse = await Connection.request('get', url);
        console.log('📥 Respuesta directa:', directResponse);
        
        if (directResponse && directResponse.success && directResponse.data && directResponse.data.items) {
          console.log('✅ Datos directos encontrados:', directResponse.data.items.length, 'facturas');
          this.facturas = directResponse.data.items;
          this.todasLasFacturas = [...directResponse.data.items];
          this.$awn.success(`${this.facturas.length} facturas cargadas (directo)`);
        } else {
          console.error('❌ No se encontraron datos válidos');
          this.$awn.alert('No se encontraron facturas');
        }
        
      } catch (error) {
        console.error('❌ Error en cargarMultiplesPaginas:', error);
        this.$awn.alert('Error al cargar las facturas: ' + error.message);
      }
    },

    async cargarTodasLasPaginas() {
      try {
        let todasLasFacturas = [];
        let paginaActual = 1;
        let totalPaginas = 1;

        do {
          const params = `?page=${paginaActual}`;
          const response = await this.$store.dispatch('sells/getSells', params);
          
          if (response.success && response.data.items) {
            todasLasFacturas = [...todasLasFacturas, ...response.data.items];
            totalPaginas = response.data.pages || 1;
            paginaActual++;
            
            // Mostrar progreso
            this.$awn.info(`Cargando página ${paginaActual-1} de ${totalPaginas}...`);
          } else {
            break;
          }
        } while (paginaActual <= totalPaginas);

        this.facturas = todasLasFacturas;
        this.todasLasFacturas = [...todasLasFacturas]; // Copia para mantener datos originales
        this.$awn.success(`${this.facturas.length} facturas cargadas en total`);
      } catch (error) {
        console.error('Error al cargar todas las páginas:', error);
        this.$awn.alert('Error al cargar todas las páginas');
      }
    },

    async buscarFacturas() {
      // Simplificar: solo cargar todas las facturas
      await this.cargarTodasLasFacturas();
    },

    verDetalles(factura) {
      // Mostrar detalles en consola por ahora
      console.log('Detalles de factura:', factura);
      this.$awn.info(`Factura ${factura.folio} - Total: $${this.formatNumber(factura.total)}`);
    },

    async verPDF(factura) {
      try {
        const url = BaseUrl.getUrl(`api/local/sell/${factura.id}/pdf`);
        // Abrir el PDF en una nueva ventana
        window.open(url, '_blank');
        this.$awn.info(`Abriendo PDF de factura ${factura.sell_folio || factura.id}`);
      } catch (error) {
        console.error('Error al abrir PDF:', error);
        this.$awn.alert('Error al abrir el PDF');
      }
    },

    abrirModalNotaCredito(factura) {
      this.$awn.info('Función Nota de Crédito en desarrollo');
    },

    getEstadoBadge(estado) {
      switch (estado) {
        case 'completed': return 'badge bg-success';
        case 'pending': return 'badge bg-warning';
        case 'cancelled': return 'badge bg-danger';
        default: return 'badge bg-secondary';
      }
    },

    getEstadoTexto(factura) {
      if (factura.trash === 1) return 'ELIMINADA';
      if (factura.cancel === 1) return 'CANCELADA';
      return 'ACTIVA';
    },

    getEstadoBadge(factura) {
      if (factura.trash === 1) return 'badge bg-danger';
      if (factura.cancel === 1) return 'badge bg-warning';
      return 'badge bg-success';
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('es-CL');
    },

    formatNumber(number) {
      return FormatNumber.format(number);
    },

    aplicarFiltroFranquiciado() {
      if (this.filtros.franquiciadoSeleccionado) {
        // Filtrar facturas por el RUT seleccionado
        this.facturas = this.todasLasFacturas.filter(factura => 
          factura.rut_cliente === this.filtros.franquiciadoSeleccionado
        );
        this.$awn.success(`Filtrado por franquiciado: ${this.filtros.franquiciadoSeleccionado}`);
      } else {
        // Si no hay selección, mostrar todas las facturas
        this.facturas = [...this.todasLasFacturas];
        this.$awn.info('Mostrando todas las facturas');
      }
    },

    limpiarFiltros() {
      this.filtros = {
        folio: '',
        rut: '',
        razonSocial: '',
        fechaDesde: '',
        fechaHasta: '',
        franquiciadoSeleccionado: ''
      };
      this.facturas = [...this.todasLasFacturas];
      this.$awn.info('Filtros limpiados');
    }
  }
};
</script>

<style scoped>
.card {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  border: 1px solid rgba(0, 0, 0, 0.125);
}

.table-hover tbody tr:hover {
  background-color: rgba(0, 0, 0, 0.075);
}

.fs-6 {
  font-size: 1rem !important;
}
</style>
