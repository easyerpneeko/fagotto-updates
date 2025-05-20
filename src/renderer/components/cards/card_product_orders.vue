<template>
  <div
    @click="$emit('clickEmit', product)"
    class="cardModalProducts card my-2"
    :class="{ 'disabled': !product.tiene_stock }"
  >
    <div v-if="!product.tiene_stock" class="card-body p-1 text-center d-flex flex-column align-items-center">
      <h6 class="mt-0 text-capitalize text-bold">
        {{ product.name }}
      </h6>
      <span class="text-danger">Sin Stock</span>
    </div>
    <div v-else :class="['card-body p-1 text-center d-flex flex-column align-items-center', (product.image == 'productDefault') ? '' : 'pa-12']">
      <div v-if="product.image != 'productDefault'" class="view">
        <img
          :src="getImage(product.image)"
          class="product-img img-fluid"
        />
        <a href="#">
          <div class="mask waves-effect waves-light rgba-white-slight"></div>
        </a>
      </div>

      <div class="w-100 d-flex flex-column align-items-start p-2">
        <h6 class="mt-0 text-capitalize text-bold">
          {{ product.name }}
        </h6>
        <span>
          <span class="color-money">${{
            formatNumber(deFormatNumber(product.price))
          }}</span
          ><span v-if="product.cecina && cecinaInstalled">/KG</span>
        </span>
      </div>
    </div>
  </div>
</template>

<script>
// Helpers
import BaseUrl from '@/helpers/baseUrl.js';
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
export default {
  name: 'card_product_orders',
  props:{
    product:{
      type: Object,
      default: null
    }
  },
  methods:{
    // Obtener URL
    getImage(image){
      var backgroundImage =  BaseUrl.getUrl('images/product.png');
      if(image) backgroundImage = BaseUrl.getUrl('images/'+image);

      return backgroundImage;
    },
    formatNumber(number){
      return FormatNumber.format(number);
    },
    deFormatNumber(number,backend = true){
      if (backend) {
        return FormatNumber.deFormatBackend(number);
      }else{
        return FormatNumber.deFormat(number);
      }
    },
  },
  computed:{
    cecinaInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.productos.ajustes.permitir_cecina'); } }
  }
}
</script>

<style lang="scss">
.cardModalProducts{
  height: calc(100% - 15px);
  cursor: pointer;
  transition: .4s all ease !important;
  box-shadow: 0px 0px 8px 3px rgba(0,0,0,.4);
}
.cardModalProducts:hover{
  box-shadow: 0px 0px 8px 5px rgba(0,0,0,.4);
}
.cardModalProducts:active{
  box-shadow: 0px 0px 8px 1.5px rgba(0,0,0,.4);;
}
</style>
