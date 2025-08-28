export default {
  // Control de turnos
  turnoActivo: false,
  montoInicial: 0,
  horaInicioTurno: null,
  turnoId: null,
  
  // Arqueo actual
  arqueoActual: null,
  ultimoArqueo: null,
  
  // Historial
  historialArqueos: [],
  
  // Resumen del día
  resumenDia: {
    total_ventas: 0,
    ventas_por_medio: {},
    total_transacciones: 0,
    fecha: null
  },
  
  // Información del negocio
  negocioInfo: {
    id: null,
    nombre: '',
    direccion: '',
    telefono: '',
    email: ''
  },
  
  // Ventas por método de pago
  ventasPorMedio: {},
  
  // Estado de carga
  loading: false,
  error: null
}
