<template>
  <div class="sidebar-mini dark navar-color" :class="(drawer) ? 'sidebar-open' : 'sidebar-collapse'">
    <aside class="main-sidebar bg-drawer navar-color d-flex flex-column align-items-end">
      <div class="sidebar1" style="height: 100%;">
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column pt-4" data-widget="true" role="menu" data-accordion="true">
            <li :class="['nav-item mb-1', { 'pb-5': !showCafeteriaLayoutWrapper }]">
              <a @click="drawer = !drawer" class="navar-text" :class="['nav-link', { 'open-link': drawer }]">
                <i class="nav-icon fas fa-bars"></i>
                <p>Menu</p>
              </a>
            </li>
            <li v-if="showCafeteriaLayoutWrapper" class="nav-item pb-5 mb-1">
              <a @click="drawerPanel = !drawerPanel" class="navar-text"
                :class="['nav-link', { 'open-link': drawer, 'router-link-exact-active router-link-active navar-text active': drawerPanel }]">
                <i class="nav-icon fas fa-cash-register"></i>
                <p>Ordenes activas</p>
              </a>
            </li>
          </ul>
          <ul class="nav nav-pills nav-sidebar flex-column"
            style="flex-wrap: nowrap; max-height: 60vh; overflow-y: auto">
            <li v-for="(option, index) in menu" :key="index" class="nav-item pt-1" v-if="menu && menu.length > 0">
              <router-link tag="a" :to="option.route"
                :class="['text-white nav-link', ($route.path == option.route) ? 'active' : '', { 'open-link': drawer, 'disabled': offOn }]">
                <i :class="['nav-icon fas', option.icon]"></i>
                <p class="navar-text ">{{ option.label }}</p>
              </router-link>
            </li>
          </ul>
        </nav>
      </div>
      <div class="sidebar1 mt-auto">
        <ul class="nav nav-pills nav-sidebar flex-column justify-content-end" style="min-height: 20vh;"
          data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item justify-content-end">
            <div :class="['nav-link text-white pointer', { 'open-link': drawer, 'disabled': offOn }]" @click="refreshApp">
              <i class="nav-icon fas fa-sync-alt"></i>
              <p>Sincronizar</p>
            </div>
          </li>
          <li class="nav-item justify-content-end">
            <div :class="['nav-link text-white pointer', { 'open-link': drawer, 'disabled': offOn }]" @click="logout">
              <i class="nav-icon fas fa-power-off"></i>
              <p>Cerrar sesion</p>
            </div>
          </li>
        </ul>
      </div>
    </aside>

    <div class="content-wrapper default-bg-color">
      <div v-if="showCafeteriaLayoutWrapper" class="d-flex flex-wrap">
        <div :class="['col-md-4 col-12 panelLateral', { 'hiddenPanel': !drawerPanel }]">
          <boards-actives v-model="offOn" />
        </div>
        <div
          :class="['col-12 bodyPanelLatera', (!drawerPanel) ? 'noPanelLateral' : 'paddingPanelLateral', { 'paddingDrawer': drawer }]">

          <router-view v-model="offOn" :feedsWatch="feedsWatch"></router-view>
        </div>
      </div>
      <router-view v-else v-model="offOn" :feedsWatch="feedsWatch"></router-view>
    </div>

    <!-- Carga dinamica -->
    <div class="loaderDinamic">
      <div id="loaderDinamic" class="vld-parent loaderDinamicChildren"></div>
    </div>

    <modal-expirated @refreshApp="refreshApp" v-model="dataExpired" />
    <ifConnected @refresh="initIntervalConnected" />
    <initMoney />

  </div>
</template>

<script>
// Components
import ifConnected from '@/components/modals/ifConnected.vue';
import ModalExpirated from '../components/modals/expired.vue';
import boardsActives from '@/components/boardsActives.vue';
import initMoney from '@/components/modals/initMoney.vue';


// Helpers y plugins
const fs = require('fs');
import Loader from '@/helpers/Loader';
import ConfigHelper from '@/helpers/ConfigHelper';
import $ from 'jquery';

export default {
  components: { ModalExpirated, boardsActives, initMoney, ifConnected },
  name: 'layout',
  data() {
    return {
      menu: null,
      offOn: false,
      drawer: false,
      drawerPanel: true,
      dataExpired: null,
      feedsWatch: false
    }
  },
  async mounted() {
    this.feedsWatch = false;
    let request = await this.initIntervalConnected(false);
    if (!request) return false;
    this.offOn = true;
    Loader.fullPage();
    setTimeout(async () => {
      var req = await this.verifyExpire();
      if (req.success) {
        await this.verifyJson();
        await this.$store.dispatch('main/getEnvs');
      }
      Loader.hide();
      this.offOn = false;
      // Monto inicial
      if (ConfigHelper.ConfStr('modulos.ventas.submodulos.reporte') && ConfigHelper.HavePermission('monto_inicial')) this.verifyInitMoney();
      this.feedsWatch = true;
    }, 2000);



    // Verificacion de la app cada 5 minutos
    setInterval(async () => {

      await this.verifyExpire();
      if (ConfigHelper.ConfStr('modulos.cafeteria')) {
        if (this.isGarzonModeEnabled) await this.$store.dispatch('cafeteria/getAllOrdersActives');
        else await this.$store.dispatch('boards/getBoardsActives');
      }

    }, 1000 * 60 * 5);

    setTimeout(() => {
      this.feedsWatch = true;
    }, 5000)
  },
  computed: {
    isGarzonModeEnabled: { get() { return ConfigHelper.ConfStr('modulos.cafeteria.submodulos.garzon_mode'); } },
    envs: { get() { return this.$store.getters['main/getEnvs'] } },
    /*
      Retorna VERDADERO, si en la ruta actual se debe mostrar la opcion de
      ordenes activas
    */
    showCafeteriaLayoutWrapper() {
      // Rutas de cafeteria
      let routes = ['/inicio/meseros', '/inicio/mesas', '/inicio/cafeteria'];
      let isCafeteria = false;

      // Si SOLO deberia aparecer en la pestaña de cafeteria solo sera esa unica ruta
      if (ConfigHelper.ConfStr('modulos.cafeteria.ajuste.layout_only_cafeteria')) routes = ['/inicio/cafeteria'];
      // Si no deberia aparecer en ningun sitio, la ruta seria ninguna
      if (ConfigHelper.ConfStr('modulos.cafeteria.ajuste.not_cafeteria_layout')) routes = [];

      routes.map((item) => {
        if (this.$router.app._route.path == item) isCafeteria = true;
      });

      return isCafeteria;

    },
  },
  methods: {
    // Verificar conexion a internet
    initIntervalConnected(isRefresh = false) {
      if (!window.navigator.onLine) {
        $('#ifConnected').modal('show');
        return false;
      }
      let myInterval = setInterval(() => {
        let connected = window.navigator.onLine;
        if (!connected) {
          $('#ifConnected').modal('show');
          clearInterval(myInterval);
        }
      }, 800);

      if (isRefresh) this.refreshApp();

      return true;
    },

    // Monto inicial
    async verifyInitMoney() {
      // let request = await this.$store.dispatch('main/getMyInitMoney');
      // if(request.success && request.data){
      //   $('#initMoney').modal('show');
      // }
      let start_workshift = localStorage.getItem('start_workshift');
      if (start_workshift === null) {
        $('#initMoney').modal('show');
      }
    },

    // Funcion para verificar serial en JSON
    verifyJson() {
      ConfigHelper.readAppFile((err, data) => {
        if (err) {
          if (window.location.hash != '#/') this.$router.push('/');
          return;
        };

        //Si el archivo se leyo correctamente, saltar al login
        ConfigHelper.ConfigHandler(false, JSON.parse(data), false);

        //Se actualiza el menu
        this.migrateDrawer();
      });
    },

    // Funcion para verificar de expiracion
    async verifyExpire() {
      var request = await this.$store.dispatch('main/refreshData', '?slim');
      console.log("///////////////////////////////////////////", request);
      if (request.data == 'Failed to fetch') return false;
      if (!request.success && (request.aplicacionVencida || request.aplicacionBloqueada)) {
        if (request.aplicacionBloqueada) {
          this.dataExpired = {
            titleExpired: 'SERVICIO BLOQUEADO',
            propExpired: 'Este es un mensaje automatizado del sistema EasyERP. Se registra que su servicio a sido bloqueado. Por favor ponganse en contacto para seguir Conectado al sistema.',
            link: 'https://api.whatsapp.com/send?phone=56949939922&text=Tienes+problemas+de+Bloqueo,%20¿Enviame+un+mensaje+para+poder+ayudarte?'
          }
        } else if (request.aplicacionVencida) {
          this.dataExpired = {
            titleExpired: 'SERVICIO SUSPENDIDO POR FALTA DE PAGO',
            propExpired: 'Este es un mensaje automatizado del sistema EasyERP. Se registra que su plazo de pago ha vencido. Por favor ponganse en contacto para seguir Conectado al sistema.',
            link: 'https://api.whatsapp.com/send?phone=56949939922&text=Tienes+problemas+de+Suspencion,%20¿Enviame+un+mensaje+para+poder+ayudarte?'
          }
        }

        $('#expired').modal('show');
        if (window.location.hash != '#/inicio') this.$router.push('/inicio');
      } else {
        $('#expired').modal('hide');
      }
      return request;
    },

    // Funcion para sincronizar app
    async refreshApp() {
      this.feedsWatch = false;
      this.offOn = true;
      var checkAuth = false;
      Loader.fullPage();
      if (this.$route.path != '/inicio') this.$router.push('/inicio');
      var request = await this.verifyExpire();
      if (request.success) {
        await this.verifyJson();
        await this.$store.dispatch('main/getEnvs');
        var request = await this.$store.dispatch('main/refreshData');
        ConfigHelper.writeAppFile(request.data);
        checkAuth = await this.$store.dispatch('main/checkAuth');
        checkAuth = checkAuth.exitLogin;
      }
      Loader.hide();
      this.offOn = false;
      this.feedsWatch = true;
      if (checkAuth) this.$router.push('/login');
    },

    // Funcion para migrar el drawer
    async migrateDrawer() {
      // modulos
      var products = ConfigHelper.ConfStr('modulos.productos');
      var clients = ConfigHelper.ConfStr('modulos.clientes');
      var follows = ConfigHelper.ConfStr('modulos.seguimientos');
      var sells = ConfigHelper.ConfStr('modulos.ventas');
      var cafeteria = ConfigHelper.ConfStr('modulos.cafeteria');
      const clientOrders = ConfigHelper.ConfStr('modulos.client_orders');
      var pedidos = ConfigHelper.ConfStr('modulos.pedidos');
      var reposteria = ConfigHelper.ConfStr('modulos.reposteria');
      var operations = ConfigHelper.ConfStr('modulos.operations');
      var stocks = ConfigHelper.ConfStr('modulos.stocks');
      // Submodulos
      var devoluciones = ConfigHelper.ConfStr('modulos.ventas.submodulos.devolutions');
      var sii = ConfigHelper.ConfStr('modulos.ventas.submodulos.sii');
      var fastSell = ConfigHelper.ConfStr('modulos.ventas') && ConfigHelper.ConfStr('modulos.ventas.submodulos.sell_fast');
      const kitchenMode = ConfigHelper.ConfStr('modulos.cafeteria') && ConfigHelper.ConfStr('modulos.cafeteria.submodulos.modo_cocina');
      var report = ConfigHelper.ConfStr('modulos.ventas') && ConfigHelper.ConfStr('modulos.ventas.submodulos.reporte');
      var expenses_day = ConfigHelper.ConfStr('modulos.ventas') && ConfigHelper.ConfStr('modulos.ventas.submodulos.expenses_day');

      let mobileDevicesOrders = null;
      if (clientOrders) {
        mobileDevicesOrders = ConfigHelper.ConfStr('modulos.client_orders.submodulos.co_mobile_device');
      }

      // permisos
      var usersGestion = ConfigHelper.HavePermission('usuarios_gestion');
      var productsGestion = ConfigHelper.HavePermission('productos_gestion');
      var sellsCreate = ConfigHelper.HavePermission('crear_venta');
      var tickets = ConfigHelper.HavePermission('getionar_tickets');
      var noNewSell = ConfigHelper.ConfStr('modulos.ventas.ajustes.no_new_sell');
      var sellsGestion = ConfigHelper.HavePermission('gestionar_ventas');
      var reportsGestion = ConfigHelper.HavePermission('gestionar_reportes');
      var expensesGestion = ConfigHelper.HavePermission('gestionar_gastos');
      var arqueoGestion = ConfigHelper.HavePermission('gestionar_arqueo') || ConfigHelper.HavePermission('obtener_arqueo');
      var getWaiters = ConfigHelper.HavePermission('obtener_meseros');
      var boardsModify = ConfigHelper.HavePermission('gestionar_mesas');
      var getBoards = ConfigHelper.HavePermission('obtener_mesas');
      const retrieveClientOrdersPermission = ConfigHelper.HavePermission('retrieve_client_orders');
      const createClientOrdersPermission = ConfigHelper.HavePermission('create_client_order');
      this.menu = null;

      setTimeout(() => {
        // Por defecto
        var menu = [{
          label: 'Inicio',
          route: '/inicio',
          icon: 'fa-home'
        }];
        // Modulo de usuarios
        if (usersGestion) {
          menu.push({
            label: 'Usuarios',
            route: '/inicio/usuarios',
            icon: 'fa-user-alt'
          });
        };
        // Modulo de productos
        if (products && productsGestion) {
          if (!ConfigHelper.ConfStr('modulos.productos.ajustes.hide_products_menu'))
            menu.push({
              label: 'Productos',
              route: '/inicio/productos',
              icon: 'fa-dolly-flatbed'
            });
        };
        // Modulo de clientes
        if (clients) {
          menu.push({
            label: 'Clientes',
            route: '/inicio/clientes',
            icon: 'fa-users'
          });
        };
        // Modulo de seguimientos de clientes compras
        if (follows) {
          menu.push({
            label: 'Seguimientos de ventas',
            route: '/inicio/seguimientos',
            icon: 'fas fa-chart-line'
          });
        };


        // Modulo de ventas
        if (sells) {
          if ((sellsCreate || tickets) && !noNewSell) {
            menu.push({
              label: 'Nueva venta',
              route: '/inicio/nueva/venta',
              icon: 'fa-shopping-cart'
            });
          }
          if (sellsGestion) {
            // Gestion de ventas
            if (!ConfigHelper.ConfStr('modulos.ventas.ajustes.hide_sells_menu'))
              menu.push({
                label: 'Ventas',
                route: '/inicio/ventas',
                icon: 'fa-clipboard-list'
              });

            // Submodulos de venta rapida
            if (fastSell) {
              menu.push({
                label: 'Venta rapida',
                route: '/inicio/nueva/venta/rapida',
                icon: 'fa-shipping-fast'
              });
            }
            // Submodulos de reporte
            if (report && reportsGestion) {
              menu.push({
                label: 'Reportes',
                route: '/inicio/reportes',
                icon: 'fa-scroll'
              });
            }
            // Submodulos de gastos del dia
            if (expenses_day && expensesGestion) {
              menu.push({
                label: 'Gastos',
                route: '/inicio/gastos',
                icon: 'fa-coins'
              });
            }
            
            // Submodulo de arqueo de caja - SIEMPRE VISIBLE
            menu.push({
              label: 'Arqueo de Caja',
              route: '/inicio/arqueo-caja',
              icon: 'fa-cash-register'
            });

          }
        }
        // Cafetaria
        if (cafeteria) {
          menu.push({
            label: this.envs.name_panel_tickets.value,
            route: '/inicio/cafeteria',
            icon: 'fa-coffee'
          });
          if (getWaiters) {
            menu.push({
              label: 'Meseros',
              route: '/inicio/meseros',
              icon: 'fa-user-friends'
            });
          }
          if (boardsModify && getBoards) {
            menu.push({
              label: 'Mesas',
              route: '/inicio/mesas',
              icon: 'fa-clipboard-list'
            });
          }
          console.log('kitchen kitchenMode kitchenMode', kitchenMode);
          // Modo cocina
          if (kitchenMode) {
            menu.push({
              label: 'Cocina',
              route: '/inicio/cafeteria/kitchen-mode/kitchen',
              icon: 'fa-sticky-note'
            });
          }
        }

        // Client Orders
        if (clientOrders) {
          // Submodulo de Mobile Device Orders
          if (mobileDevicesOrders) {
            if (createClientOrdersPermission) {
              menu.push({
                label: 'Creacion de orden',
                route: '/inicio/client-orders/mobile-devices/create',
                icon: 'fa-shopping-cart'
              });
            }
            if (retrieveClientOrdersPermission) {
              menu.push({
                label: 'Lista de ordenes',
                route: '/inicio/client-orders/mobile-devices/lista',
                icon: 'fa-clipboard-list'
              });
            }
          }
        }

        if (sii) {
          menu.push({
            label: 'Cargar Folios',
            // label: this.envs.name_panel_tickets.value,
            route: '/inicio/folios',
            icon: 'fa-file-upload'
          });

          menu.push({
            label: 'Franquiciados',
            route: '/inicio/franquiciados',
            icon: 'fas fa-handshake'
          });
        }
        //Modulo de pedidos
        if (pedidos) {
          menu.push({
            label: 'Crear Pedido',
            route: '/inicio/pedidos',
            icon: 'fas fa-truck-loading'
          });
        }
        
        //Modulo de Uber Eats
        menu.push({
          label: 'Uber Eats',
          route: '/inicio/uber-eats',
          icon: 'fas fa-motorcycle',
          badge: 'NEW'
        });
        
        //Modulo de pedidos de pasteles
        if (reposteria) {
          menu.push({
            label: 'Pedido Reposteria',
            route: '/inicio/reposteria',
            icon: 'fas fa-birthday-cake'
          });
        }
        //Modulo de operaciones
        if (operations) {
          menu.push({
            label: 'Crear Operacion',
            route: '/inicio/operaciones',
            icon: 'fas fa-cogs'
          });
        }

        //Modulo de devoluciones
        if (devoluciones) {
          menu.push({
            label: 'Devoluciones',
            route: '/inicio/devoluciones',
            icon: 'fas fa-undo-alt'
          });
        }

        // //Modulo de stocks
        // if (stocks) {
        //   menu.push({
        //     label: 'Stocks',
        //     route: '/inicio/stocks',
        //     icon: 'fas fa-boxes'
        //   });
        // }

        //Modulo de stocks
        if (stocks) {
          menu.push({
            label: 'Ingredientes',
            route: '/inicio/ingredients',
            icon: 'far fa-file-alt'
          })
        }
        // console.log(menu);
        this.menu = menu;
      }, 500);
    },

    // Cerrar sesion
    async logout() {
      await fs.unlink('authorization.json', (error) => {
        if (error) {
          // console.log(error);
        }
        this.$router.push('/login');
      });
    },
  }
}
</script>

<style scoped>
/*rgba(57,173,219,.25) */
.navar-text {

  color: #a3b8cc
}

.navar-color {

  background: #0d1d35;
  background: -moz-radial-gradient(0% 100%, ellipse cover, #0d1d35 10%, rgba(138, 114, 76, 0) 40%), -moz-linear-gradient(top, #192b5f 0%, #243158 100%), -moz-linear-gradient(-45deg, #06061b 0%, #0d1d35 100%);
  background: -webkit-radial-gradient(0% 100%, ellipse cover, #0d1d35 10%, rgba(138, 114, 76, 0) 40%), -webkit-linear-gradient(top, #192b5f 0%, #243158 100%), -webkit-linear-gradient(-45deg, #192b5f 0%, #0d1d35 100%);
  background: -o-radial-gradient(0% 100%, ellipse cover, #0d1d35 10%, rgba(138, 114, 76, 0) 40%), -o-linear-gradient(top, #192b5f 0%, #243158 100%), -o-linear-gradient(-45deg, #192b5f 0%, #0d1d35 100%);
  background: -ms-radial-gradient(0% 100%, ellipse cover, #0d1d35 10%, rgba(138, 114, 76, 0) 40%), -ms-linear-gradient(top, #192b5f 0%, #243158 100%), -ms-linear-gradient(-45deg, #192b5f 0%, #0d1d35 100%);
  background: -webkit-radial-gradient(0% 100%, ellipse cover, #0d1d35 10%, rgba(138, 114, 76, 0) 40%), linear-gradient(to bottom, #192b5f 0%, #243158 100%), linear-gradient(135deg, #192b5f 0%, #0d1d35 100%);
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#3E1D6D', endColorstr='#092756', GradientType=1);
}

.loaderDinamic {
  position: fixed;
  bottom: 15px;
  left: 90px;
  margin: 0px;
  z-index: 9999;
}

.loaderDinamicChildren {
  width: 35px;
  height: 35px;
  padding: 30px;
}

.text-next-scale {
  color: #fff;
  text-decoration: none;
}

.nav-link {
  width: 65.5px !important;
}

.nav-link:hover {
  width: 65.5px !important;
}

.open-link {
  width: 241.5px !important;
}

.open-link:hover {
  width: 241.5px !important;
}

.sidebar1 {
  padding-left: 0.5rem !important;
  padding-right: 0rem !important;
}

.active {
  border-top-left-radius: 50px !important;
  border-bottom-left-radius: 50px !important;
  border-top-right-radius: 0px !important;
  border-bottom-right-radius: 0px !important;

  background: rgb(243, 242, 242) !important;
  color: #292f36 !important;
}

.panelLateral {
  z-index: 3;
  transition: .3s all ease;
}

.panelLateral div {
  margin-top: 5px;
}

.bodyPanelLatera {
  transition: .3s all ease;
  margin-top: 15px;
  padding: 0px;
  overflow: auto;
  max-height: 100vh;
}

.hiddenPanel {
  opacity: 0;
  z-index: 0;
}

.noPanelLateral {
  padding: 0px !important;
}

@media (min-width: 768px) {
  .paddingPanelLateral {
    padding-left: calc(33.333333% + 19px);
  }

  .paddingDrawer {
    padding-left: calc(33.333333% + 23px + 55px);
  }

  .bodyPanelLatera {
    margin-top: 0px;
  }

  .panelLateral div {
    margin: 15px 0px;
  }

  .panelLateral {
    position: absolute;
    height: 95vh;
  }






}
</style>
