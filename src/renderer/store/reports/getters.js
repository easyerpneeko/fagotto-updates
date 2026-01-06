export function getterCounters(state) {
  return state.counters;
}
export function getterProductEstadisticas(state) {
  return state.products;
}
export function getterWaiters(state) {
  return state.waiters;
}
export function getterWorkshifts(state) {
  return state.workshifts;
}
export function getterExpenses(state) {
  return state.expenses;
}

export function getterOneWaiter(state) {
  return state.oneWaiter;
}

export function getterDailySales(state) {
  return state.dailySales || 0;
}