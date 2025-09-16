import Connection from '../../helpers/Connection.js';
import BaseUrl from '../../helpers/baseUrl.js';

// ==================== GESTIÓN DE TURNOS ====================

// MÉTODO TEMPORAL: Crear tabla TurnosCaja
export async function crearTablaTurnos(context) {
  let url = BaseUrl.getUrl('api/local/turno/crear-tabla');
  const request = await Connection.request('post', url);
  return request;
}

export async function verificarEstadoTurno(context) {
  let url = BaseUrl.getUrl('api/local/turno/estado');
  const request = await Connection.request('get', url);
  if (request.success) {
    // ✅ CORREGIDO: Usar la clave correcta del backend
    context.commit('setProperty', { key: 'turnoActivo', data: request.data.turno_abierto || false });
    context.commit('setProperty', { key: 'montoInicial', data: request.data.monto_inicial || 0 });
    
    // Guardar también la información completa del turno si existe
    if (request.data.turno) {
      context.commit('setProperty', { key: 'turnoId', data: request.data.turno.id });
      context.commit('setProperty', { key: 'horaInicioTurno', data: request.data.turno.fecha_inicio });
    }
  }
  return request;
}

export async function iniciarTurno(context, data) {
  let url = BaseUrl.getUrl('api/local/turno/iniciar');
  const request = await Connection.request('post', url, data);
  if (request.success) {
    // ✅ ACTUALIZAR: Estado completo del turno
    context.commit('setProperty', { key: 'turnoActivo', data: true });
    context.commit('setProperty', { key: 'turnoId', data: request.data.turno.id });
    context.commit('setProperty', { key: 'horaInicioTurno', data: request.data.turno.fecha_inicio });
    context.commit('setProperty', { key: 'montoInicial', data: data.monto_inicial || 0 });
  }
  return request;
}

export async function cerrarTurnoConArqueo(context, data) {
  let url = BaseUrl.getUrl('api/local/turno/cerrar');
  const request = await Connection.request('post', url, data);
  if (request.success) {
    context.commit('setProperty', { key: 'turnoActivo', data: false });
    context.commit('setProperty', { key: 'ultimoArqueo', data: request.data });
  }
  return request;
}

// ==================== GESTIÓN DE ARQUEOS ====================

export async function guardarArqueo(context, data) {
  let url = BaseUrl.getUrl("api/local/arqueo");
  const request = await Connection.request("post", url, data);
  if (request.success) {
    context.commit('addArqueo', request.data);
  }
  return request;
}

export async function obtenerArqueos(context, params = '') {
  let url = BaseUrl.getUrl('api/local/arqueos' + params);
  const request = await Connection.request('get', url);
  if (request.success) {
    context.commit('setProperty', { key: 'historialArqueos', data: request.data });
  }
  return request;
}

export async function obtenerArqueoActual(context, params = '') {
  let url = BaseUrl.getUrl('api/local/arqueo/actual' + params);
  const request = await Connection.request('get', url);
  if (request.success) {
    context.commit('setProperty', { key: 'arqueoActual', data: request.data });
  }
  return request;
}

// ==================== REPORTES Y RESÚMENES ====================

export async function obtenerResumenDia(context, params = '') {
  let url = BaseUrl.getUrl('api/local/report/arqueo-resumen' + params);
  const request = await Connection.request('get', url);
  if (request.success) {
    context.commit('setProperty', { key: 'resumenDia', data: request.data });
  }
  return request;
}

export async function obtenerInfoNegocio(context) {
  let url = BaseUrl.getUrl('api/local/negocio/info');
  const request = await Connection.request('get', url);
  if (request.success) {
    context.commit('setProperty', { key: 'negocioInfo', data: request.data });
  }
  return request;
}

// ==================== MÉTODOS DE PAGO ====================

export async function obtenerVentasPorMedioPago(context, params = '') {
  let url = BaseUrl.getUrl('api/local/report/ventas-medios-pago' + params);
  const request = await Connection.request('get', url);
  if (request.success) {
    context.commit('setProperty', { key: 'ventasPorMedio', data: request.data });
  }
  return request;
}

// ==================== LEGACY (mantener compatibilidad) ====================

export async function cerrarTurno(context, data) {
  // Wrapper para mantener compatibilidad
  return await cerrarTurnoConArqueo(context, data);
}
