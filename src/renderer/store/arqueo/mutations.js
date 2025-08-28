export function setProperty(state, payload) {
  state[payload.key] = payload.data;
}

export function setLoading(state, loading) {
  state.loading = loading;
}

export function setError(state, error) {
  state.error = error;
}

export function addArqueo(state, arqueo) {
  state.historialArqueos.unshift(arqueo);
  // Mantener solo los últimos 50 arqueos
  if (state.historialArqueos.length > 50) {
    state.historialArqueos.splice(50);
  }
}

export function updateArqueo(state, { index, arqueo }) {
  if (index >= 0 && index < state.historialArqueos.length) {
    state.historialArqueos.splice(index, 1, arqueo);
  }
}

export function removeArqueo(state, index) {
  if (index >= 0 && index < state.historialArqueos.length) {
    state.historialArqueos.splice(index, 1);
  }
}

export function clearHistorial(state) {
  state.historialArqueos = [];
}

export function iniciarTurno(state, { montoInicial, turnoId = null }) {
  state.turnoActivo = true;
  state.montoInicial = montoInicial;
  state.horaInicioTurno = new Date().toISOString();
  state.turnoId = turnoId;
}

export function cerrarTurno(state) {
  state.turnoActivo = false;
  state.turnoId = null;
  state.horaInicioTurno = null;
}

export function setVentasPorMedio(state, ventas) {
  state.ventasPorMedio = { ...ventas };
}
