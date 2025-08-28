export default {
  turnoActivo: (state) => state.turnoActivo,
  montoInicial: (state) => state.montoInicial,
  horaInicioTurno: (state) => state.horaInicioTurno,
  turnoId: (state) => state.turnoId,
  
  arqueoActual: (state) => state.arqueoActual,
  ultimoArqueo: (state) => state.ultimoArqueo,
  
  historialArqueos: (state) => state.historialArqueos,
  cantidadArqueos: (state) => state.historialArqueos.length,
  
  resumenDia: (state) => state.resumenDia,
  negocioInfo: (state) => state.negocioInfo,
  ventasPorMedio: (state) => state.ventasPorMedio,
  
  loading: (state) => state.loading,
  error: (state) => state.error,
  
  // Getters calculados
  totalVentasDelDia: (state) => {
    return Object.values(state.ventasPorMedio).reduce((total, monto) => total + (monto || 0), 0);
  },
  
  ventasPorMetodoPago: (state) => (metodoPago) => {
    return state.ventasPorMedio[metodoPago] || 0;
  },
  
  estadoTurnoCompleto: (state) => {
    return {
      activo: state.turnoActivo,
      montoInicial: state.montoInicial,
      horaInicio: state.horaInicioTurno,
      id: state.turnoId
    };
  },
  
  ultimosArqueos: (state) => (limite = 10) => {
    return state.historialArqueos.slice(0, limite);
  }
};
