export function setProperty (state, params) {
  const key = params.key;
  const data = params.data;
  state[key] =  data;
}

// Des-selecciona la mesa actual
export function clearBoard (state) {
  console.log('Clear Board!')
  state.board = null;
}

// Des-selecciona el waiter actual (en el caso de ser un SOLO waiter)
export function clearOnlyWaiter (state) {
  console.log('Clear Only Waiter!')
  state.onlyWaiter = null;
}

// Activar/desactivar modo modal fijo
export function setKeepModalOpen (state, value) {
  state.keepModalOpen = value;
}
