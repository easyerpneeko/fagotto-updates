import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

export default new Router({
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
          path: 'usuarios',
          component: require('@/views/users.vue').default
        },
        {
          path: 'clientes',
          component: require('@/views/clients.vue').default
        },
        {
          path: 'seguimientos',
          component: require('@/views/follows.vue').default
        },
        {
          path: 'productos',
          component: require('@/views/products.vue').default
        },
        {
          path: 'nueva/venta',
          component: require('@/views/newSell.vue').default
        },
        {
          path: 'nueva/venta/rapida',
          component: require('@/views/newSellFast.vue').default
        },
        {
          path: 'ventas',
          component: require('@/views/sells.vue').default
        },
        {
          path: 'devoluciones',
          component: require('@/views/devolutions.vue').default
        },
        {
          path: 'stocks',
          component: require('@/views/stocks.vue').default
        },
        {
          path: 'client-orders/mobile-devices/lista',
          component: require('@/views/orders-clients/mobile-devices.vue').default
        },
        {
          path: 'client-orders/mobile-devices/create',
          component: require('@/views/orders-clients/new-order-mobile.vue').default
        },
        {
          path: 'cafeteria',
          component: require('@/views/cafeteria.vue').default
        },
        {
          path: 'meseros',
          component: require('@/views/waiters.vue').default
        },
        {
          path: 'mesas',
          component: require('@/views/boards.vue').default
        },
        {
          path: 'cafeteria/kitchen-mode/kitchen',
          component: require('@/views/kitchen-mode/kitchen.vue').default
        },
        {
          path: 'reportes',
          component: require('@/views/report.vue').default
        },
        {
          path: 'gastos',
          component: require('@/views/expenses.vue').default
        },
        {
          path: 'folios',
          component: require('@/views/folios.vue').default
        },
        {
          path: 'pedidos',
          component: require('@/views/pedidos.vue').default
        },
        {
          path: 'reposteria',
          component: require('@/views/reposteria.vue').default
        },
        {
          path: 'operaciones',
          component: require('@/views/operations.vue').default
        }
      ]
    },
    {
      path: '*',
      redirect: '/'
    }
  ]
})
