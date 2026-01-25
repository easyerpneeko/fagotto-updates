<template>
  <div class="stock-excel-container">
    <div class="header-section">
      <div class="title-section">
        <i class="fas fa-table"></i>
        <h1>Stock (Excel)</h1>
        <span class="subtitle">Gestión de productos - pedidofinal_precios</span>
      </div>
      <div class="actions-section">
        <button class="btn-action btn-refresh" @click="cargarDatos" :disabled="loading">
          <i class="fas fa-sync-alt" :class="{ spinning: loading }"></i>
          Actualizar
        </button>
        <button class="btn-action btn-save" @click="guardarCambios" :disabled="!hayCambios || saving">
          <i class="fas fa-save"></i>
          Guardar Cambios ({{ cambiosPendientes.length }})
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-overlay">
      <div class="spinner"></div>
      <p>Cargando datos...</p>
    </div>

    <!-- Tabla tipo Excel -->
    <div v-else class="table-container">
      <div class="table-wrapper">
        <table class="excel-table">
          <thead>
            <tr>
              <th class="col-id">#</th>
              <th class="col-nombre">Producto</th>
              <th class="col-tipo">Tipo</th>
              <th class="col-stock">Stock</th>
              <th class="col-precio">Precio Unitario</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(producto, index) in productos" :key="producto.id" :class="{ 'row-modified': productoModificado(producto.id) }">
              <!-- ID -->
              <td class="col-id">{{ producto.id }}</td>
              
              <!-- Producto -->
              <td class="col-nombre">{{ producto.producto }}</td>
              
              <!-- Tipo de medida -->
              <td class="col-tipo">
                <select 
                  v-model="producto.tipo_medida" 
                  @change="onTipoChange(producto)"
                  class="cell-select"
                  disabled
                >
                  <option value="kilos">Kilos</option>
                  <option value="litros">Litros</option>
                  <option value="unidades">Unidades</option>
                </select>
              </td>
              
              <!-- Stock -->
              <td class="col-stock editable">
                <input 
                  type="text" 
                  v-model="producto.stock" 
                  @blur="validarStock(producto)"
                  @keypress="validarTecla($event, producto)"
                  class="cell-input text-right"
                  :placeholder="producto.tipo_medida === 'kilos' ? 'Ej: 3.45' : 'Ej: 5'"
                  inputmode="decimal"
                />
              </td>
              
              <!-- Precio Unitario -->
              <td class="col-precio text-right">$ {{ formatearPrecio(producto.precio_por_unidad) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Footer con estadísticas -->
    <div class="footer-stats">
      <div class="stat-item">
        <i class="fas fa-box"></i>
        <span>Total Productos: <strong>{{ productos.length }}</strong></span>
      </div>
      <div class="stat-item">
        <i class="fas fa-edit"></i>
        <span>Cambios Pendientes: <strong>{{ cambiosPendientes.length }}</strong></span>
      </div>
      <div class="stat-item">
        <i class="fas fa-clock"></i>
        <span>Última actualización: <strong>{{ ultimaActualizacion }}</strong></span>
      </div>
    </div>
  </div>
</template>

<script>
import Connection from '@/helpers/Connection';
import BaseUrl from '@/helpers/baseUrl';

export default {
  name: 'StockExcel',
  data() {
    return {
      productos: [],
      productosOriginales: [],
      cambiosPendientes: [],
      loading: false,
      saving: false,
      ultimaActualizacion: '-',
      appInfo: null
    };
  },
  computed: {
    hayCambios() {
      return this.cambiosPendientes.length > 0;
    }
  },
  async mounted() {
    await this.cargarAppInfo();
    this.cargarDatos();
  },
  methods: {
    async cargarAppInfo() {
      try {
        const request = await this.$store.dispatch('main/refreshData', '?slim');
        this.appInfo = request.data;
        console.log('✅ App info cargada:', this.appInfo);
      } catch (error) {
        console.error('❌ Error cargando app info:', error);
      }
    },
    
    async cargarDatos() {
      this.loading = true;
      try {
        // 1. Cargar productos base (sin stock)
        let url = BaseUrl.getUrl('api/local/pedidofinal/stock');
        const request = await Connection.request('get', url);
        
        console.log('🔍 Request completo:', request);
        console.log('🔍 request.ok:', request.ok);
        console.log('🔍 request.data:', request.data);
        console.log('🔍 Es array?', Array.isArray(request.data));
        
        // Detectar formato de respuesta correctamente
        const isSuccess = request.ok === true || 
                         request.success === true || 
                         (request.data && request.data.success === true);
        
        if (isSuccess) {
          let data = null;
          
          if (Array.isArray(request.data)) {
            data = request.data;
            console.log('✅ Caso 1: Array directo', data.length);
          } 
          else if (request.data && request.data.success && Array.isArray(request.data.data)) {
            data = request.data.data;
            console.log('✅ Caso 2: Object con success', data.length);
          }
          else if (request.data && Array.isArray(request.data.data)) {
            data = request.data.data;
            console.log('✅ Caso 3: Object simple', data.length);
          }
          
          if (data && data.length > 0) {
            // 2. Obtener el último stock reportado por este negocio
            const app = this.appInfo || {};
            const appId = app.app_id || app.appId || app.Id || app.id || null;
            const idNegocio = app.Id || app.id || null;
            
            // Construir URL con parámetros de filtro
            let urlStock = BaseUrl.getUrl('api/local/pedidofinal/stock-negocio/resumen');
            if (idNegocio) {
              urlStock += `?id_negocio=${idNegocio}`;
              if (appId) {
                urlStock += `&app_id=${appId}`;
              }
            } else if (appId) {
              urlStock += `?app_id=${appId}`;
            }
            
            console.log('📊 Cargando stock de negocio:', { idNegocio, appId, urlStock });
            const stockRequest = await Connection.request('get', urlStock);
            
            let stockPorNegocio = {};
            const isStockSuccess = stockRequest.ok === true || 
                                  stockRequest.success === true || 
                                  (stockRequest.data && stockRequest.data.success === true);
            
            if (isStockSuccess) {
              const stockData = Array.isArray(stockRequest.data) ? stockRequest.data : 
                               (stockRequest.data && Array.isArray(stockRequest.data.data) ? stockRequest.data.data : []);
              
              // Filtrar solo el stock de este negocio
              stockData.forEach(item => {
                if ((item.app_id && item.app_id === appId) || (item.id_negocio && item.id_negocio === idNegocio)) {
                  stockPorNegocio[item.id_producto] = item.cantidad_reportada;
                }
              });
            }
            
            // 3. Asignar productos con el stock del negocio (o 0 si no hay)
            this.productos = data.map(p => ({
              ...p,
              stock: stockPorNegocio[p.id] || 0,
              tipo_medida: this.detectarTipoMedida(p.unidad_medida) // Auto-detectar tipo inicial
            })).sort((a, b) => a.id - b.id); // Ordenar por ID del 1 al final
            
            this.productosOriginales = JSON.parse(JSON.stringify(this.productos));
            this.cambiosPendientes = [];
            this.actualizarHora();
            console.log('🎉 Productos asignados:', this.productos.length);
            this.$awn.success(`${data.length} productos cargados correctamente`);
          } else {
            console.error('❌ Data no válida:', data);
            throw new Error('No se encontraron productos');
          }
        } else {
          throw new Error('Request no exitoso');
        }
      } catch (error) {
        console.error('❌ Error cargando datos:', error);
        const errorMsg = error.message || 'Error desconocido';
        this.$awn.alert('Error al cargar los datos: ' + errorMsg);
      } finally {
        this.loading = false;
      }
    },
    
    marcarModificado(id, campo, valor) {
      const index = this.cambiosPendientes.findIndex(c => c.id === id);
      
      if (index >= 0) {
        // Ya existe un cambio para este producto
        this.cambiosPendientes[index].cambios[campo] = valor;
      } else {
        // Nuevo cambio
        this.cambiosPendientes.push({
          id: id,
          cambios: {
            [campo]: valor
          }
        });
      }
    },
    
    productoModificado(id) {
      return this.cambiosPendientes.some(c => c.id === id);
    },
    
    async guardarCambios() {
      if (!this.hayCambios) return;
      
      this.saving = true;
      let exitosos = 0;
      let errores = 0;
      
      try {
        // Obtener información del negocio actual desde appInfo
        const app = this.appInfo || {};
        
        console.log('🔍 DEBUG APP COMPLETO:', JSON.stringify(app, null, 2));
        console.log('🔍 KEYS DE APP:', Object.keys(app));
        
        const idNegocio = app.Id || app.id || null;
        const nombreNegocio = app.Name || app.name || 'Sin nombre';
        const appId = app.app_id || app.appId || app.Id || app.id || null;
        const usuario = localStorage.getItem('username') || 'Sistema';
        
        console.log('🔍 DEBUG APP INFO:', {
          idNegocio: idNegocio,
          nombreNegocio: nombreNegocio,
          appId: appId,
          usuario: usuario
        });
        
        for (const cambio of this.cambiosPendientes) {
          try {
            // Solo procesamos cambios de stock
            if (cambio.cambios.stock !== undefined) {
              const producto = this.productos.find(p => p.id === cambio.id);
              
              if (producto) {
                // Preparar datos - solo incluir campos que no sean null
                const payload = {
                  id_producto: parseInt(cambio.id),
                  producto_nombre: producto.producto || 'Sin nombre',
                  cantidad_reportada: parseFloat(cambio.cambios.stock),
                  unidad_medida: producto.unidad_medida || 'kg',
                  observacion: 'Actualización de stock desde Stock (Excel)'
                };
                
                // Solo agregar campos opcionales si tienen valor
                if (idNegocio !== null) {
                  payload.id_negocio = parseInt(idNegocio);
                }
                if (appId !== null) {
                  payload.app_id = String(appId);
                }
                if (nombreNegocio && nombreNegocio !== 'Sin nombre') {
                  payload.nombre_negocio = nombreNegocio;
                }
                if (usuario) {
                  payload.usuario = usuario;
                }
                
                console.log('🚀 Enviando datos:', payload);
                
                // Registrar en historial por negocio
                let url = BaseUrl.getUrl('api/local/pedidofinal/stock-negocio');
                const request = await Connection.request('post', url, payload);
                
                const isSaveSuccess = request.ok === true || 
                                     request.success === true || 
                                     (request.data && request.data.success === true);
                
                if (isSaveSuccess) {
                  exitosos++;
                  console.log(`✅ Stock guardado - ID: ${cambio.id}, Producto: ${producto.producto}`);
                } else {
                  errores++;
                  console.error(`❌ Error guardando - ID: ${cambio.id}, Producto: ${producto.producto}`, request);
                  
                  // Mostrar el mensaje de error específico
                  if (request.data && request.data.message) {
                    this.$awn.alert(`Error en ${producto.producto}: ${request.data.message}`);
                  }
                  if (request.data && request.data.errors) {
                    console.error('Errores de validación:', request.data.errors);
                  }
                }
              }
            }
          } catch (error) {
            console.error(`❌ Error guardando stock del producto ${cambio.id}:`, error);
            errores++;
          }
        }
        
        if (exitosos > 0) {
          this.$awn.success(`${exitosos} stock(s) actualizado(s) correctamente`);
        }
        
        if (errores > 0) {
          this.$awn.warning(`${errores} stock(s) con error al registrar`);
        }
        
        // Recargar datos
        await this.cargarDatos();
        
      } catch (error) {
        console.error('Error guardando cambios:', error);
        this.$awn.alert('Error al guardar los cambios');
      } finally {
        this.saving = false;
      }
    },
    
    actualizarHora() {
      const now = new Date();
      this.ultimaActualizacion = now.toLocaleString('es-CL');
    },
    
    formatearPrecio(precio) {
      return parseFloat(precio).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    },
    
    permiteDecimales(unidadMedida) {
      if (!unidadMedida) return false;
      const unidad = unidadMedida.toLowerCase().trim();
      // Permite decimales si contiene: kg, kilo, kilos, lt, litro, litros
      return unidad.includes('kg') || 
             unidad.includes('kilo') || 
             unidad.includes('lt') || 
             unidad.includes('litro') ||
             unidad.includes('bolsa') && (unidad.includes('k') || unidad.includes('2'));
    },

    detectarTipoMedida(unidadMedida) {
      // Auto-detectar basado en el nombre de la unidad
      if (!unidadMedida) return 'unidades';
      
      const unidad = String(unidadMedida).toLowerCase().trim();
      
      // Detectar LITROS
      if (unidad.includes('litro') || unidad.includes('lt') || unidad.includes('ml')) {
        return 'litros';
      }
      
      // Detectar KILOS
      if (unidad.includes('kg') || unidad.includes('kilo') || unidad.includes('gr') || unidad.includes('bolsa') && unidad.includes('k')) {
        return 'kilos';
      }
      
      // Por defecto: UNIDADES
      return 'unidades';
    },

    onTipoChange(producto) {
      // Cuando cambia el tipo, validar nuevamente el stock
      this.validarStock(producto);
    },
    
    validarTecla(event, producto) {
      const key = event.key;
      const value = producto.stock ? producto.stock.toString() : '';
      
      // Permitir: números, backspace, delete, tab, escape, enter
      if (['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight'].includes(key)) {
        return true;
      }
      
      // Permitir punto decimal SOLO si es tipo kilos
      if (key === '.' || key === ',') {
        // Bloquear si NO es kilos
        if (producto.tipo_medida !== 'kilos') {
          event.preventDefault();
          return false;
        }
        // Bloquear si ya existe un punto o coma
        if (value.includes('.') || value.includes(',')) {
          event.preventDefault();
          return false;
        }
        // Permitir el punto/coma
        return true;
      }
      
      // Solo permitir números
      if (!/[0-9]/.test(key)) {
        event.preventDefault();
        return false;
      }
    },
    
    validarStock(producto) {
      let valor = producto.stock ? producto.stock.toString() : '0';
      
      // Reemplazar coma por punto
      valor = valor.replace(',', '.');
      
      // Remover caracteres no válidos
      valor = valor.replace(/[^0-9.]/g, '');
      
      // Evitar múltiples puntos
      const partes = valor.split('.');
      if (partes.length > 2) {
        valor = partes[0] + '.' + partes.slice(1).join('');
      }
      
      // Convertir a número
      let numero = parseFloat(valor) || 0;
      
      // Solo KILOS permite decimales, litros y unidades son enteros
      if (producto.tipo_medida === 'kilos') {
        // Limitar a 2 decimales para kilos
        numero = Math.round(numero * 100) / 100;
      } else {
        // Redondear a entero para litros y unidades
        numero = Math.round(numero);
      }
      
      // Asegurar que no sea negativo
      if (numero < 0) numero = 0;
      
      // Actualizar el valor
      producto.stock = numero;
      
      // Marcar como modificado
      this.marcarModificado(producto.id, 'stock', numero);
    }
  }
};
</script>

<style scoped>
.stock-excel-container {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #f5f7fa;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
}

/* Header */
.header-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.title-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.title-section i {
  font-size: 2rem;
}

.title-section h1 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 700;
}

.subtitle {
  font-size: 0.9rem;
  opacity: 0.9;
  margin-left: 1rem;
}

.actions-section {
  display: flex;
  gap: 1rem;
}

.btn-action {
  padding: 0.7rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}

.btn-refresh {
  background: white;
  color: #667eea;
}

.btn-refresh:hover {
  background: #f0f0f0;
  transform: translateY(-2px);
}

.btn-save {
  background: #38ef7d;
  color: white;
}

.btn-save:hover:not(:disabled) {
  background: #2ed86f;
  transform: translateY(-2px);
}

.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Loading */
.loading-overlay {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* Tabla */
.table-container {
  flex: 1;
  overflow: hidden;
  padding: 1.5rem;
}

.table-wrapper {
  height: 100%;
  overflow: auto;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.excel-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.9rem;
}

.excel-table thead {
  position: sticky;
  top: 0;
  z-index: 10;
  background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
}

.excel-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 700;
  color: #667eea;
  border-bottom: 2px solid #667eea;
  white-space: nowrap;
}

.excel-table tbody tr {
  transition: all 0.2s ease;
}

.excel-table tbody tr:hover {
  background: #f8f9ff;
}

.excel-table tbody tr.row-modified {
  background: #fff9e6;
  border-left: 4px solid #ffc107;
}

.excel-table td {
  padding: 0.5rem 1rem;
  border-bottom: 1px solid #e0e0e0;
}

.col-id {
  width: 50px;
  text-align: center;
  font-weight: 600;
  color: #666;
}

.col-nombre {
  width: 280px;
  max-width: 280px;
}

.col-tipo {
  width: 140px;
}

.col-stock {
  width: 100px;
}

.col-precio {
  width: 130px;
}

/* Inputs editables */
.cell-input,
.cell-select {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  background: white;
}

.cell-input:focus,
.cell-select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
}

.text-right {
  text-align: right;
}

/* Footer */
.footer-stats {
  background: white;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-around;
  align-items: center;
  border-top: 1px solid #e0e0e0;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #666;
}

.stat-item i {
  color: #667eea;
  font-size: 1.2rem;
}

.stat-item strong {
  color: #333;
  font-weight: 700;
}
</style>
