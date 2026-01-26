export function categories(state) {
  // Ocultar solo "Emergencia" del catálogo (los productos Días Locos se acceden por botón especial)
  return (state.categories || []).filter(cat => cat.name !== 'Emergencia');
}
