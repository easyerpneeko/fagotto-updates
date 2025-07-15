<template>
  <StackModal
  :show="showModal"
  @close="$emit('changeState', false)"
  :modal-class="{ [modalClass]: true }"
  v-if="dataShow"
  >
    <div slot="modal-header">
      <div class="modal-header bg-primary">
        <h5 class="modal-title">{{ dataShow.module.name }} {{ dataShow.module.version }}</h5>
        <a class="close" aria-label="Close" @click="$emit('changeState', false)">
          <span aria-hidden="true">&times;</span>
        </a>
      </div>
    </div>

    <div class="modal-body">
      <div class="card-body p-0">
        <div class="col-12">
          <p class="text-description">
            {{dataShow.module.description}}
          </p>
        </div>
        <div class="col-12">
          <h5 class="">
            Submodulos
          </h5>
        </div>
        <div v-if="dataShow.submodules.length" v-for="(sub, index) in dataShow.submodules" :key="index" class="col-12 card-modules">
          <div class="collapsed-card bg-grey card-modules">
            <div class="card-header p-2 pl-3 border-b">
              <h6 class="card-title card-title-padding text-font-modules">{{sub.name}}</h6>
            </div>
          </div>
        </div>
        <div v-if="dataShow.submodules.length == 0" class="col-12">
          <div class="text-center">
            <a class="btn-add-module p-5" href="#">
              <h4 class="text-add-module p-4">No existen submodulos para {{ dataShow.module.name }}</h4>
            </a>
          </div>
        </div>
      </div>
    </div>

    <div slot="modal-footer">
      <div class="modal-footer">
        <a @click="$emit('changeState', false)" class="btn btn-primary text-white">Cerrar</a>
      </div>
    </div>
  </StackModal>
</template>

<script>
import StackModal from '@innologica/vue-stackable-modal';
export default {
  name: 'detailsModules',
  data(){
    return{
      modalClass: '',
    }
  },
  mounted(){
  },
  components:{
    StackModal
  },
  props:[
    'showModal',
    'dataShow'
  ],
  methods:{
  },
}
</script>

<style scoped>
  .border-b{
    border-bottom: 1px solid rgba(0,0,0,.2);
  }
</style>
