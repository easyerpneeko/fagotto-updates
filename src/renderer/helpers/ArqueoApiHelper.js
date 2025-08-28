// ArqueoApiHelper.js - Helper para manejar conexiones API del arqueo con fallback local

export default class ArqueoApiHelper {
  
  // Verificar si la API está disponible
  static async checkApiAvailability(store) {
    try {
      const response = await store.dispatch('main/refreshData', '?slim');
      return response && response.success !== false;
    } catch (error) {
      console.warn('🔍 API no disponible:', error.message);
      return false;
    }
  }

  // Obtener estado del turno con fallback
  static async getEstadoTurno(store) {
    try {
      // Intentar con API primero
      const request = await store.dispatch('arqueo/verificarEstadoTurno');
      if (request.success) {
        return {
          success: true,
          data: request.data,
          source: 'api'
        };
      }
    } catch (error) {
      console.warn('⚠️ API no disponible para estado turno:', error.message);
    }

    // Fallback a localStorage
    return {
      success: true,
      data: ArqueoApiHelper.getEstadoTurnoLocal(),
      source: 'local'
    };
  }

  // Iniciar turno con fallback
  static async iniciarTurno(store, data) {
    try {
      // Intentar con API primero
      const request = await store.dispatch('arqueo/iniciarTurno', data);
      if (request.success) {
        // Sincronizar con localStorage como backup
        ArqueoApiHelper.sincronizarTurnoLocal(data, true);
        return {
          success: true,
          data: request.data,
          source: 'api'
        };
      }
    } catch (error) {
      console.warn('⚠️ API no disponible para iniciar turno:', error.message);
    }

    // Fallback a localStorage
    ArqueoApiHelper.sincronizarTurnoLocal(data, true);
    return {
      success: true,
      data: { turno_id: Date.now(), message: 'Turno iniciado localmente' },
      source: 'local'
    };
  }

  // Cerrar turno con arqueo
  static async cerrarTurno(store, data) {
    try {
      // Intentar con API primero
      const request = await store.dispatch('arqueo/cerrarTurnoConArqueo', data);
      if (request.success) {
        // Limpiar localStorage al cerrar exitosamente
        ArqueoApiHelper.limpiarTurnoLocal();
        return {
          success: true,
          data: request.data,
          source: 'api'
        };
      }
    } catch (error) {
      console.warn('⚠️ API no disponible para cerrar turno:', error.message);
    }

    // Fallback: Simular cierre exitoso y marcar para sincronización posterior
    ArqueoApiHelper.guardarArqueoParaSincronizar(data);
    ArqueoApiHelper.limpiarTurnoLocal();
    
    return {
      success: true,
      data: { id: Date.now(), message: 'Arqueo guardado localmente' },
      source: 'local'
    };
  }

  // Obtener historial con fallback
  static async getHistorial(store, params = '') {
    try {
      // Intentar con API primero
      const request = await store.dispatch('arqueo/obtenerArqueos', params);
      if (request.success) {
        return {
          success: true,
          data: request.data.arqueos || request.data || [],
          source: 'api'
        };
      }
    } catch (error) {
      console.warn('⚠️ API no disponible para historial:', error.message);
    }

    // Fallback a localStorage
    return {
      success: true,
      data: ArqueoApiHelper.getHistorialLocal(),
      source: 'local'
    };
  }

  // Obtener resumen del día con fallback
  static async getResumenDia(store, params = '') {
    try {
      // Intentar con API primero
      const request = await store.dispatch('arqueo/obtenerResumenDia', params);
      if (request.success) {
        return {
          success: true,
          data: request.data,
          source: 'api'
        };
      }
    } catch (error) {
      console.warn('⚠️ API no disponible para resumen:', error.message);
    }

    // Fallback: datos vacíos
    return {
      success: true,
      data: {
        total_ventas: 0,
        ventas_por_medio: {},
        total_transacciones: 0
      },
      source: 'local'
    };
  }

  // ==================== MÉTODOS LOCALES ====================

  static getEstadoTurnoLocal() {
    const turnoLocal = localStorage.getItem('turnoActivo');
    const fechaLocal = localStorage.getItem('fechaTurno');
    const montoInicialLocal = localStorage.getItem('montoInicialTurno');
    const fechaHoy = new Date().toISOString().split('T')[0];
    
    // Limpiar turnos de días anteriores
    if (fechaLocal && fechaLocal !== fechaHoy) {
      ArqueoApiHelper.limpiarTurnoLocal();
      return {
        turno_activo: false,
        monto_inicial: 0,
        turno_id: null
      };
    }
    
    return {
      turno_activo: turnoLocal === 'true',
      monto_inicial: parseFloat(montoInicialLocal) || 0,
      turno_id: localStorage.getItem('turnoId') || null
    };
  }

  static sincronizarTurnoLocal(data, activo) {
    const fechaHoy = new Date().toISOString().split('T')[0];
    
    if (activo) {
      localStorage.setItem('turnoActivo', 'true');
      localStorage.setItem('fechaTurno', fechaHoy);
      localStorage.setItem('horaInicioTurno', new Date().toISOString());
      localStorage.setItem('montoInicialTurno', data.monto_inicial.toString());
      if (data.turno_id) {
        localStorage.setItem('turnoId', data.turno_id.toString());
      }
    } else {
      ArqueoApiHelper.limpiarTurnoLocal();
    }
  }

  static limpiarTurnoLocal() {
    localStorage.removeItem('turnoActivo');
    localStorage.removeItem('fechaTurno');
    localStorage.removeItem('horaInicioTurno');
    localStorage.removeItem('montoInicialTurno');
    localStorage.removeItem('turnoId');
  }

  static getHistorialLocal() {
    try {
      return JSON.parse(localStorage.getItem('historialArqueos') || '[]');
    } catch (error) {
      console.error('Error cargando historial local:', error);
      return [];
    }
  }

  static guardarArqueoParaSincronizar(data) {
    try {
      // Guardar arqueo en cola de sincronización
      const pendientes = JSON.parse(localStorage.getItem('arqueosPendientesSincronizar') || '[]');
      pendientes.push({
        ...data,
        timestamp: new Date().toISOString(),
        estado: 'pendiente'
      });
      localStorage.setItem('arqueosPendientesSincronizar', JSON.stringify(pendientes));
      
      // También guardar en historial local
      const historial = ArqueoApiHelper.getHistorialLocal();
      historial.unshift({
        id: Date.now(),
        fecha: new Date().toISOString(),
        ...data,
        sincronizado: false
      });
      
      if (historial.length > 50) {
        historial.splice(50);
      }
      
      localStorage.setItem('historialArqueos', JSON.stringify(historial));
      
    } catch (error) {
      console.error('Error guardando arqueo para sincronizar:', error);
    }
  }

  // Sincronizar arqueos pendientes (para llamar cuando la API esté disponible)
  static async sincronizarArqueosPendientes(store) {
    try {
      const pendientes = JSON.parse(localStorage.getItem('arqueosPendientesSincronizar') || '[]');
      
      if (pendientes.length === 0) {
        return { sincronizados: 0, errores: 0 };
      }

      let sincronizados = 0;
      let errores = 0;
      const noSincronizados = [];

      for (const arqueo of pendientes) {
        try {
          const request = await store.dispatch('arqueo/cerrarTurnoConArqueo', arqueo);
          if (request.success) {
            sincronizados++;
          } else {
            errores++;
            noSincronizados.push(arqueo);
          }
        } catch (error) {
          errores++;
          noSincronizados.push(arqueo);
        }
      }

      // Actualizar lista de pendientes
      localStorage.setItem('arqueosPendientesSincronizar', JSON.stringify(noSincronizados));

      return { sincronizados, errores };

    } catch (error) {
      console.error('Error sincronizando arqueos pendientes:', error);
      return { sincronizados: 0, errores: 1 };
    }
  }
}
