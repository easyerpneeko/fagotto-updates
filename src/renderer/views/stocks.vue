<template>
  <div class="bg-home ">
    <div class="pt-5 pb-3 px-3 px-sm-5">
      <h4 class="mb-4">Stocks de productos</h4>

      <div class="container">
        <div class="row">
          <div v-for="product in this.products" class="col-md-4 col-xl-3">
            <div :class="product.category == 1 ? 'card bg-c-yellow order-card' : 'card bg-c-blue order-card'">
              <div class="card-block">
                <h6 class="m-b-20">{{product.name}}</h6>
                <h2 v-if="product.category == 1" class="text-right"><i class="fas fa-wine-bottle f-left"></i><span>{{ product.stock >= 1000 ? product.stock/1000 : product.stock }} {{product.stock >= 1000 ? 'kg' : 'gr' }}</span></h2>
                <h2 v-if="product.category == 2" class="text-right"><i class="fas fa-boxes f-left"></i><span>{{ product.stock }} u </span></h2>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>


</template>

<script>
// components
import customTable from '@/components/tables/table.vue';

// helpers
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
import Loader from '@/helpers/Loader';
import moment from 'moment';
import $ from 'jquery';

export default {
  name: 'sellsList',
  data() {
    return {
      products:null,
    }
  },
  mounted() {
    console.log('=============STOCK================');
    this.getProducts();
  },
  components: {
    customTable,
  },
  props: {
    value: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    async getProducts(){
      // Iniciando peticion
      Loader.dinamic();
      var request = await this.$store.dispatch("products/getProductsOfFagotto");
      Loader.hide();
      console.log(request );
      // Verificando respuesta
      if (request.success) {
        this.products = request.data;

      }
      else this.$awn.alert('Error al obtener los productos');
    },
    formatNumber(number) {
      return FormatNumber.format(number);
    },
    deFormatNumber(number, backend = true) {
      if (backend) {
        return FormatNumber.deFormatBackend(number);
      } else {
        return FormatNumber.deFormat(number);
      }
    },
  },
  computed: {
    // Permisos para obtener las ventas
    // sellsGet: { get() { return ConfigHelper.HavePermission('gestionar_ventas'); } },
    // permissionRemoveSell: { get() { return ConfigHelper.HavePermission('eliminar_venta'); } },

    // v-model
    offOn: {
      get() { return this.value },
      set(offOn) { this.$emit('input', offOn) }
    }

  }
}
</script>

<style scoped>
.bg-light tr {
  background-color: #ffffff !important;
}

.widthInput {
  width: 100% !important;
}

.mx-input {
  height: 38px !important;
}

.mx-input-wrapper {
  height: 38px !important;
}

.page-link {
  font-size: 15px !important;
}

.btnPersonalice {
  border: none !important;
  text-transform: none !important;
  padding: 7px 20px;
  margin-bottom: 0px;
  display: inline !important;
}

.btnOrderBy {
  cursor: pointer;
}

@media (max-width: 885px) {
  .d-none-01 {
    display: none;
  }
}

@media (max-width: 768px) {
  .d-none-0 {
    display: none;
  }
}

@media (max-width: 660px) {
  .d-none-1 {
    display: none;
  }
}

@media (max-width: 575px) {
  .btnPersonalice {
    display: block;
    width: 100%;
  }
}

@media (max-width: 520px) {
  .d-none-2 {
    display: none !important;
  }
}

#ofBar {
  background-color: #192b5f;
  color: #fff;
  padding: 10px;
  text-align: center;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 1000;
  display: flex;
  /* Cambio: Usar display flex para alinear elementos internos */
  justify-content: space-between;
  /* Cambio: Espaciado uniforme entre elementos internos */
  align-items: center;
  /* Cambio: Alinear elementos verticalmente al centro */
}

#ofBar-logo img {
  max-width: 100px;
}

#ofBar-content {
  font-size: 18px;
  flex: 1;
  /* Cambio: Permitir que el contenido ocupe el espacio restante */
}

#ofBar-right {
  display: flex;
  align-items: center;
}

#btn-bar {
  background-color: #27ae60;
  color: #fff;
  padding: 8px 15px;
  text-decoration: none;
  margin-left: 10px;
  /* Cambio: Ajustar margen izquierdo para separar el botón del texto */
  border-radius: 5px;
}

#btn-bar:hover {
  background-color: #2ecc71;
}

#close-bar {
  cursor: pointer;
  font-size: 20px;
}

.order-card {
  color: #fff;
}

.bg-c-blue {
  background: linear-gradient(45deg, #06192f, #4da0ff);
}

.bg-c-green {
  background: linear-gradient(45deg, #006d1d, #4fc38e);
}

.bg-c-yellow {
  background: linear-gradient(45deg, #a26000, #ffb54b);
}

.bg-c-pink {
  background: linear-gradient(45deg, #731a22, #ec0000);
}


.card {
  border-radius: 5px;
  -webkit-box-shadow: 0 1px 2.94px 0.06px rgba(4, 26, 55, 0.16);
  box-shadow: 0 1px 2.94px 0.06px rgba(4, 26, 55, 0.16);
  border: none;
  margin-bottom: 30px;
  -webkit-transition: all 0.3s ease-in-out;
  transition: all 0.3s ease-in-out;
}

.card .card-block {
  padding: 25px;
}

.card-title {
  float: left;
  font-size: 1.1rem;
  font-weight: 400;
  margin: 0;
}

.order-card i {
  font-size: 26px;
}

.f-left {
  float: left;
}

.f-right {
  float: right;
}

.bg-one {
  background-color: var(--primary);
  color: #fff !important;
}
</style>
