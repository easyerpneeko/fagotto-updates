<template>
  <div v-if="data.items">
    <div class="scroll-table" :style="(data.height) ? 'max-height:' + data.height : ''">
      <table class="m-0 table table-striped table-bordered table-sm w-100">
        <thead>
          <tr>
            <th v-if="getPermission(title.permission)" :class="['text-bold',title.class]" v-for="(title,index) in data.titles" :key="index">
              <div class="m-0">
                {{title.label}}
                <div style="float: right" v-if="title.type == 'orderBy'" @click="$emit('orderBy')">
                  <div v-if="title.orderBy" class="btnOrderBy">
                    <i  class="fas fa-angle-down btnOrderBy"></i>
                  </div>
                  <div v-else >
                    <i  class="fas fa-angle-up btnOrderBy"></i>
                  </div>
                </div>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in data.items" :key="index" :class="item.trash==1 ? 'bg-danger bg-light' : ''">
            <td v-if="getPermission(key.permission)" :class="[key.class, (key.edit) ? 'fieldEditTd' : '' ]" v-for="(key, index2) in data.rows">
              <!-- Editables -->
              <input min="0" v-if="key.edit" @keyup="$emit('changeValue', data.items)" @blur="$emit('onBlur', item)" class="fieldEdit" type="number" v-model="item[key.key]" />
              <!-- No editables -->
              <span v-else class="m-0 p-0">
                <!-- Campo especial para el nombre y rut de un cliente -->
                <div v-if="key.key == 'client_name'" class="m-0">
                  {{ (item.client != null) ? item.client.name + ' ' + item.client.lastname : '-'}}
                </div>
                <div v-if="key.key == 'client_rut'" class="m-0">
                  {{ (item.client != null) ? item.client.rut : '-'}}
                </div>

                <!-- Campo especial para mostrar cantidades de pesos y cecina -->
                <div v-if="(key.key == 'total' || key.key == 'price' || key.key == 'totalPrice' || key.key == 'balance')" class="m-0">
                  ${{formatNumber(deFormatNumber(item[key.key]))}} <span v-if="(item[key.subKey] && getPermission(key.subPermission))">/KG</span>
                </div>

                <!-- Campo especial para mostrar precios por unidad -->
                <div v-if="key.key == 'unitary_price'" class="m-0">
                  ${{formatNumber(deFormatNumber(item[key.key], true))}} <span v-if="(item[key.subKey] && getPermission(key.subPermission))">/KG</span>
                </div>

                <!-- Campo especial para mostrar precios por unidad -->
                <div v-if="key.key == 'subtotal'" class="m-0">
                  ${{formatNumber(item[key.key])}}
                </div>

                <!-- Campo especial para mostrar cantidades -->
                <div v-if="key.key == 'stock'" class="m-0">
                  {{ (item[key.key]) ? item[key.key] : 0 }}
                </div>
                
                <!-- Campo especial para mostrar cantidades en pedidos -->
                <!-- <div v-if="key.key == 'quantity'" class="m-0">
                   {{ (item[key.key]) ? item[key.key]+'kg' : 0  }}
                  {{ (item[key.key])  }}
                </div> -->

                <!-- Campo especial para mostrar estadoss -->
                <div v-if="key.key == 'state'" :class="['m-0 text-bold text-capitalize',(item[key.key] == 'ocupado' || item[key.key] == 'ocupada') ? 'text-dark' : (item[key.key] == 'libre') ? 'text-primario' : 'text-secundario' ]">
                  <div class="">
                    {{ (item[key.key]) }}
                  </div>
                  <div v-if="(item.boards && item.boards.length > 0)" class="">
                    <span v-for="(board, index_board) in item.boards" :key="board">
                      #{{board}} <span v-if="index_board + 1 != item.boards.length"> - </span>
                    </span>
                  </div>
                </div>

                <!-- Campo predeterminado -->
                <div v-else-if="(key.key != 'balance' && key.key != 'state' && key.key != 'subtotal' && key.key != 'stock' && key.key != 'total' && key.key != 'price' && key.key != 'client_rut' && key.key != 'client_name' && key.key != 'unitary_price' && key.key != 'totalPrice')" class="m-0">
                  {{(item[key.key]) ? item[key.key] : '-' }}
                </div>
              </span>
            </td>

            <td v-if="data.btn" class="text-center">
              <slot v-bind:item="item" v-bind:index="index"/>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="data.footer" class="pt-2 px-2 bg-primario d-flex justify-content-between footer_class">
      <h5>
        {{data.footer.label}}
      </h5>
      <h5>
        {{data.footer.value}}
      </h5>
    </div>
  </div>
</template>

<script>
// Helpers
import ConfigHelper from '@/helpers/ConfigHelper.js';
import FormatNumber from '@/helpers/FormatNumber.js';
export default {
  name: 'customTable',
  data(){
    return{
      default: true
    }
  },
  props:{
    value: {
      type: [Object, Array],
      default: false
    },
    onBlur:{
      type: Function,
      default:null
    }
  },
  methods:{
    formatNumber(number){

      return FormatNumber.format(String(number));
    },
    deFormatNumber(number,backend = true){
      if (backend) {
    //JC    console.log("--------------------------hola",number)
        return FormatNumber.deFormatBackend(String(number));
      }else{
        return FormatNumber.deFormat(String(number));
      }
    },
  
    getPermission(key){
      if(this[key] || key == 'default') return true;
      else return false;
    },

  },
  computed:{
    // v-model
    data: {
      get() { return this.value },
      set(value) { this.$emit('input',value) }
    },
    siiInstaller:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.sii'); } },
    clientsInstaller:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.submodulos.clientes'); } },
    displayUser:{ get(){ return ConfigHelper.ConfStr('modulos.ventas.ajustes.mostrar_usuario_venta'); } },
    stockInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.productos.ajustes.permitir_stock'); } },
    cecinaInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.productos.ajustes.permitir_cecina'); } },
    categoriesInstalled:{ get(){ return ConfigHelper.ConfStr('modulos.productos.submodulos.categorias'); } }
  },
}
</script>

<style scoped lang="scss">
  .scroll-table{
    overflow: auto;
    height: 100%;
  }
  .scroll-table::-webkit-scrollbar{
    width: 5px;
    height: 5px;
  }
  .scroll-table::-webkit-scrollbar-track{
    background:#c0c0c0;
    border-radius:50px;
    height: 5px;
  }
  .scroll-table::-webkit-scrollbar-thumb{
    background:#000000;
    border-radius:50px;
    height: 5px;
  }
  .footer_class{
    margin-right: 1px;
  }
  .btnOrderBy{
    cursor: pointer;
  }
  .fieldEdit{
    border: 2px solid #192b5f;
    border-radius: 5px;
    background: transparent;
    width: 100%;
    text-align: center;
  }
  .fieldEditTd{
    width: 100px;
  }
</style>
