import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

// Helper para verificar si hay turno activo
function verificarTurnoActivo() {
  try {
    const turnoLocal = localStorage.getItem('turnoActivo');
    const fechaLocal = localStorage.getItem('fechaTurno');
    const fechaHoy = new Date().toISOString().split('T')[0];
    
    // Limpiar turnos de días anteriores automáticamente
    if (fechaLocal && fechaLocal !== fechaHoy) {
      console.log('🛡️ ROUTER: Limpiando turno de día anterior:', fechaLocal);
      localStorage.removeItem('turnoActivo');
      localStorage.removeItem('fechaTurno');
      localStorage.removeItem('horaInicioTurno');
      localStorage.removeItem('montoInicialTurno');
      return false;
    }
    
    // Verificar si hay turno activo del día de hoy
    const tieneNavegacionPermitida = turnoLocal === 'true' && fechaLocal === fechaHoy;
    
    if (tieneNavegacionPermitida) {
      console.log('🛡️ ROUTER: ✅ Turno activo, navegación permitida');
    } else {
      console.log('🛡️ ROUTER: ❌ Sin turno activo, redirigiendo al home');
    }
    
    return tieneNavegacionPermitida;
    
  } catch (error) {
    console.error('🛡️ ROUTER: Error verificando turno:', error);
    return false;
  }
}

// Rutas que NO requieren turno activo
const rutasLibres = [
  '/',
  '/login',
  '/inicio',
  '/inicio/', // Home del layout
  '/arqueo-caja'
];

const router = new Router({
  routes: [
    {
      path: '/',
      name: 'landing-page',
      component: require('@/components/LandingPage').default
    },
    {
      path: '/login',
      name: 'login-page',
      component: require('@/components/LandingPage/Login.vue').default
    },
    {
      path: '/inicio',
      name: 'layout',
      component: require('@/Layout/layout.vue').default,
      children: [
        {
          path: '/',
          component: require('@/views/home.vue').default
        },
        {
          path: 'arqueo-caja',
          component: require('@/views/arqueo-caja.vue').default
        },
        {
          path: 'usuarios',
          component: require('@/views/users.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'clientes',
          component: require('@/views/clients.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'seguimientos',
          component: require('@/views/follows.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'productos',
          component: require('@/views/products.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'nueva/venta',
          component: require('@/views/newSell.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'nueva/venta/rapida',
          component: require('@/views/newSellFast.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'ventas',
          component: require('@/views/sells.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'devoluciones',
          component: require('@/views/devolutions.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'stocks',
          component: require('@/views/stocks.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'client-orders/mobile-devices/lista',
          component: require('@/views/orders-clients/mobile-devices.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'client-orders/mobile-devices/create',
          component: require('@/views/orders-clients/new-order-mobile.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'cafeteria',
          component: require('@/views/cafeteria.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'meseros',
          component: require('@/views/waiters.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'mesas',
          component: require('@/views/boards.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'cafeteria/kitchen-mode/kitchen',
          component: require('@/views/kitchen-mode/kitchen.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'reportes',
          component: require('@/views/report.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'gastos',
          component: require('@/views/expenses.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'folios',
          component: require('@/views/folios.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'franquiciados',
          component: require('@/views/franquiciados.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'pedidos',
          component: require('@/views/pedidos.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'uber-eats',
          component: require('@/components/pages/UberEatsDashboard.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'reposteria',
          component: require('@/views/reposteria.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'operaciones',
          component: require('@/views/operations.vue').default,
          meta: { requiresTurno: true }
        },
        {
          path: 'ingredients',
          component: require('@/views/ingredients.vue').default,
          meta: { requiresTurno: true }
        },
      ]
    },
    {
      path: '*',
      redirect: '/'
    }
  ]
})

// Router Guard Global - Verificar turno antes de navegar
router.beforeEach((to, from, next) => {
  console.log('🛡️ ROUTER: Navegando a:', to.path);
  
  // Si la ruta requiere turno activo
  if (to.meta && to.meta.requiresTurno) {
    if (verificarTurnoActivo()) {
      // Tiene turno activo, puede continuar
      next();
    } else {
      // No tiene turno activo, redirigir al home
      console.log('🛡️ ROUTER: Bloqueando navegación, sin turno activo');
      next('/inicio');
    }
  } else {
    // Ruta libre, puede continuar
    next();
  }
});

export default router;