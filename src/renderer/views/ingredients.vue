<template>
  <div class="bg-home">
    <div class="pt-5 pb-3 px-3 px-sm-5">
      <h4 class="mb-4">Stocks de Ingredientes</h4>

      <div class="container">
        <div class="row">
          <div v-for="ingredient in ingredients" class="col-md-4 col-xl-3">
            <div
              :class="
                ingredient.category_id == 1
                  ? 'card bg-c-yellow order-card'
                  : 'card bg-c-blue order-card'
              "
            >
              <div class="card-block">
                <h6 class="m-b-20">{{ ingredient.name }}</h6>
                <h2 class="text-right">
                  <i class="fas fa-wine-bottle f-left"></i>
                  <span
                    >{{ parseFloat(ingredient.stock_quantity).toFixed(2) }}
                    {{ ingredient.unit_of_measurement }}</span
                  >
                </h2>
                <p class="m-b-20">
                  Puede vender:
                  <span class="font-weight-bold">{{ ingredient.vasos }}</span>
                  vasos
                </p>
                <button class="btn btn-primary" @click="openModal(ingredient)">
                  Cargar Stock
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div
      class="modal fade"
      id="stockModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="stockModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="stockModalLabel">
              Cargar Stock de Ingrediente
            </h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="stock">Stock (Kg)</label>
              <input
                type="number"
                class="form-control"
                id="stock"
                v-model.number="modalIngredient.stock_quantity"
              />
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-dismiss="modal"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              @click="guardarStock()"
            >
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// components
import customTable from "@/components/tables/table.vue";
// helpers
import ConfigHelper from "@/helpers/ConfigHelper.js";
import FormatNumber from "@/helpers/FormatNumber.js";
import Loader from "@/helpers/Loader";
import moment from "moment";
import $ from "jquery";

export default {
  name: "sellsList",
  data() {
    return {
      ingredients: null,
      modalIngredient: {
        id: null,
        stock_quantity: 0,
      }, // Para almacenar el ingrediente del modal
    };
  },
  mounted() {
    console.log("=============INGREDIENTES================");
    this.getIngredients();
  },
  components: {
    customTable,
  },
  props: {
    value: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    async getIngredients() {
      Loader.dinamic();
      var request = await this.$store.dispatch("products/getIngredients");
      Loader.hide();
      console.log(request);
      if (request.success) {
        this.ingredients = request.data;
      } else {
        this.$awn.alert("Error al obtener los productos");
      }
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
    openModal(ingredient) {
      this.modalIngredient = { ...ingredient }; // Copiar el ingrediente al objeto del modal
      $("#stockModal").modal("show"); // Mostrar el modal
    },
    async guardarStock() {
      Loader.dinamic();
      // Enviar la actualización al backend (Laravel)
      let data = new FormData();
      data.append("new_stock", this.modalIngredient.stock_quantity);

      const response = await this.$store.dispatch(
        "products/updateIngredientStock",
        {
          ingredientId: this.modalIngredient.id,
          data: data, // Cambiamos newStock por data
        }
      );
      Loader.hide();
      if (response.success) {
        this.$awn.success("Stock de ingrediente actualizado correctamente");
        $("#stockModal").modal("hide"); // Cerrar el modal
        // Vuelve a cargar los ingredientes para reflejar los cambios
        this.getIngredients();
      } else {
        this.$awn.alert("Error al actualizar el stock del ingrediente");
      }
    },
  },
  computed: {
    offOn: {
      get() {
        return this.value;
      },
      set(offOn) {
        this.$emit("input", offOn);
      },
    },
  },
};
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
  justify-content: space-between;
  align-items: center;
}

#ofBar-logo img {
  max-width: 100px;
}

#ofBar-content {
  font-size: 18px;
  flex: 1;
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
