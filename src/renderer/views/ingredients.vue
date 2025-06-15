<template>
  <div class="bg-home">
    <div class="pt-5 pb-3 px-3 px-sm-5">
      <h4 class="mb-4">Stocks de Ingredientes</h4>

      <div class="container">
        <div class="row">
          <div
            v-for="ingredient in ingredients"
            :key="ingredient.id"
            class="col-md-4 col-xl-3"
          >
            <div
              :class="
                ingredient.category_id == 2
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
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- <div class="container mt-5">
        <h4 class="mb-4">Asignar Ingredientes a Productos</h4>
        <div class="card">
          <div class="card-body">
            <div class="form-group">
              <label for="productSelect">Seleccionar Producto:</label>
              <select
                id="productSelect"
                class="form-control"
                v-model="selectedProduct"
                @change="onProductSelect"
              >
                <option :value="null">-- Seleccione un producto --</option>
                <option
                  v-for="product in products"
                  :key="product.id"
                  :value="product"
                >
                  {{ product.name }}
                </option>
              </select>
            </div>

            <div v-if="selectedProduct">
              <h5 class="mt-4">
                Ingredientes Asignados a {{ selectedProduct.name }}
              </h5>
              <ul class="list-group mb-3">
                <li
                  v-for="(assignment, index) in assignedIngredients"
                  :key="index"
                  class="list-group-item d-flex justify-content-between align-items-center"
                >
                  {{ assignment.ingredient_name }} -
                  {{ assignment.quantity_grams }} gramos
                  <button
                    class="btn btn-danger btn-sm"
                    @click="removeAssignedIngredient(index)"
                  >
                    Eliminar
                  </button>
                </li>
                <li
                  v-if="assignedIngredients.length === 0"
                  class="list-group-item text-muted"
                >
                  No hay ingredientes asignados a este producto.
                </li>
              </ul>

              <h5 class="mt-4">Añadir Nuevo Ingrediente:</h5>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="newIngredientSelect">Ingrediente:</label>
                  <select
                    id="newIngredientSelect"
                    class="form-control"
                    v-model="newAssignment.ingredient_id"
                  >
                    <option :value="null">
                      -- Seleccione un ingrediente --
                    </option>
                    <option
                      v-for="ing in ingredients"
                      :key="ing.id"
                      :value="ing.id"
                    >
                      {{ ing.name }}
                    </option>
                  </select>
                </div>
                <div class="form-group col-md-4">
                  <label for="newQuantity">Cantidad (gramos):</label>
                  <input
                    type="number"
                    id="newQuantity"
                    class="form-control"
                    v-model.number="newAssignment.quantity_grams"
                    min="0"
                  />
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                  <button
                    class="btn btn-success w-100"
                    @click="addIngredientToProduct"
                  >
                    Añadir
                  </button>
                </div>
              </div>
              <button
                class="btn btn-primary mt-3 w-100"
                @click="saveProductIngredients"
              >
                Guardar Asignaciones
              </button>
            </div>
            <div v-else class="alert alert-info mt-3">
              Por favor, seleccione un producto para asignar ingredientes.
            </div>
          </div>
        </div>
      </div>

      <div
        class="container mt-4"
        v-if="products && products.length > 0 && totalPages > 1"
      >
        <nav aria-label="Page navigation example">
          <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <a
                class="page-link"
                href="#"
                @click.prevent="changePage(currentPage - 1)"
                >Anterior</a
              >
            </li>
            <li
              class="page-item"
              v-for="page in totalPages"
              :key="page"
              :class="{ active: page === currentPage }"
            >
              <a class="page-link" href="#" @click.prevent="changePage(page)">{{
                page
              }}</a>
            </li>
            <li
              class="page-item"
              :class="{ disabled: currentPage === totalPages }"
            >
              <a
                class="page-link"
                href="#"
                @click.prevent="changePage(currentPage + 1)"
                >Siguiente</a
              >
            </li>
          </ul>
        </nav>
      </div> -->
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
      products: null, // Para almacenar los productos paginados
      selectedProduct: null, // Producto seleccionado para asignar ingredientes
      assignedIngredients: [], // Ingredientes asignados al producto seleccionado
      newAssignment: {
        ingredient_id: null,
        quantity_grams: 0,
      },
      // Datos para la paginación de productos
      currentPage: 1,
      totalPages: 1,
      itemsPerPage: 12, // Valor predeterminado, se actualizará con la respuesta del backend
      totalProducts: 0,
    };
  },
  async mounted() {
    console.log("=============INGREDIENTES================");
    await this.getIngredients();
    await this.getProducts(this.currentPage); // Obtener los productos de la primera página
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
      if (request.success) {
        this.ingredients = request.data;
      } else {
        this.$awn.alert("Error al obtener los ingredientes");
      }
    },
    async getProducts(page = 1) {
      Loader.dinamic();
      // Asumiendo que tu acción 'getProducts' en el store 'products'
      // puede aceptar un parámetro de página.
      // Si tu backend usa un query parameter como '?page=X', la acción debería construirlo.
      var request = await this.$store.dispatch(
        "products/getProducts",
        `?page=${page}`
      );
      console.log("p", request);
      Loader.hide();
      if (request.success) {
        this.products = request.data.items;
        this.currentPage = request.data.page;
        this.totalPages = request.data.pages;
        this.itemsPerPage = request.data.perpage;
        this.totalProducts = request.data.total;
      } else {
        this.$awn.alert("Error al obtener los productos");
      }
    },
    async onProductSelect() {
      this.assignedIngredients = []; // Limpiar asignaciones anteriores
      if (this.selectedProduct && this.selectedProduct.id) {
        Loader.dinamic();
        const request = await this.$store.dispatch(
          "products/getProductIngredients",
          this.selectedProduct.id
        );
        Loader.hide();
        if (request.success) {
          console.log("data", request);

          this.assignedIngredients = request.data.map((item) => ({
            ingredient_id: item.ingredient_id,
            ingredient_name: item.name,
            quantity_grams: item.pivot.quantity_grams,
          }));
        } else {
          this.$awn.alert("Error al obtener los ingredientes del producto");
        }
      }
    },
    // addIngredientToProduct() {
    //   if (
    //     this.newAssignment.ingredient_id &&
    //     this.newAssignment.quantity_grams > 0
    //   ) {
    //     const selectedIngredient = this.ingredients.find(
    //       (ing) => ing.id === this.newAssignment.ingredient_id
    //     );

    //     if (selectedIngredient) {
    //       const existingAssignmentIndex = this.assignedIngredients.findIndex(
    //         (assign) => assign.ingredient_id === selectedIngredient.id
    //       );

    //       if (existingAssignmentIndex !== -1) {
    //         this.assignedIngredients[existingAssignmentIndex].quantity_grams =
    //           this.newAssignment.quantity_grams;
    //         this.$awn.info("Cantidad del ingrediente actualizada.");
    //       } else {
    //         this.assignedIngredients.push({
    //           ingredient_id: selectedIngredient.id,
    //           ingredient_name: selectedIngredient.name,
    //           quantity_grams: this.newAssignment.quantity_grams,
    //         });
    //         this.$awn.success("Ingrediente añadido a la lista.");
    //       }

    //       this.newAssignment = {
    //         ingredient_id: null,
    //         quantity_grams: 0,
    //       };
    //     }
    //   } else {
    //     this.$awn.warning(
    //       "Por favor, seleccione un ingrediente y una cantidad válida."
    //     );
    //   }
    // },
    // removeAssignedIngredient(index) {
    //   this.assignedIngredients.splice(index, 1);
    //   this.$awn.info("Ingrediente eliminado de la lista.");
    // },
    // async saveProductIngredients() {
    //   if (!this.selectedProduct) {
    //     this.$awn.alert(
    //       "Por favor, seleccione un producto para guardar las asignaciones."
    //     );
    //     return;
    //   }
    //   Loader.dinamic();

    //   // Crear un objeto FormData
    //   const formData = new FormData();
    //   // Añadir el productId
    //   formData.append("productId", this.selectedProduct.id);
    //   // Añadir los ingredientes como una cadena JSON
    //   formData.append(
    //     "ingredients",
    //     JSON.stringify(
    //       this.assignedIngredients.map((item) => ({
    //         ingredient_id: item.ingredient_id,
    //         quantity_grams: item.quantity_grams,
    //       }))
    //     )
    //   );

    //   // Enviar el FormData a la acción del store
    //   const response = await this.$store.dispatch(
    //     "products/assignIngredientsToProduct",
    //     formData // Enviamos el FormData directamente
    //   );
    //   Loader.hide();

    //   if (response.success) {
    //     this.$awn.success("Asignaciones guardadas correctamente.");
    //     this.onProductSelect(); // Recargar para mostrar los cambios
    //   } else {
    //     this.$awn.alert(
    //       "Error al guardar las asignaciones: " +
    //         (response.message || "Error desconocido")
    //     );
    //   }
    // },
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
    changePage(page) {
      if (page >= 1 && page <= this.totalPages && page !== this.currentPage) {
        this.currentPage = page;
        this.getProducts(this.currentPage);
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
