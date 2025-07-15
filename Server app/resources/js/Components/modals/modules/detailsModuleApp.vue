<template>
  <div>
    <StackModal
    :show="showDetails"
    @close="$emit('closeModals', false)"
    :modal-class="{ [modalClass]: true }"
    v-if="(dataModule && dataModule.module)"
    >
      <div slot="modal-header">
        <div class="modal-header">
          <h5 class="modal-title">{{dataModule.module.name}} {{dataModule.module.version}}</h5>
          <a class="close p-2" aria-label="Close" @click="$emit('closeModals', false)">
            <span aria-hidden="true">&times;</span>
          </a>
        </div>
      </div>

      <div class="modal-body">
        <div class="row scrollApp">
          <div class="col-md-5 col-12 mb-2">
            <p class="text-description">
              {{dataModule.module.description}}
            </p>
            <div class="card card-widget widget-user-2 m-0">
              <div class="card-header bg-one">
                <h3 class="card-title card-title-padding">Agregar submodulo</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool text-white" data-card-widget="collapse"><i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body">
                <v-select :options="OptionsSubmodules" v-model="submoduleAdd" style="width: 100%;"></v-select>

                <a @click="addSubmodule" class="btn bg-one text-white mt-3" style="float:right;">Agregar</a>
              </div>
            </div>
          </div>

          <div class="col-md-7 col-12">
            <div class="card card-widget widget-user-2 m-0">
              <div class="card-header bg-one">
                <h3 class="card-title card-title-padding">Ajustes del modulo</h3>
              </div>
              <div v-if="dataModule.module.settings.lenght != 0" class="card-body p-0">
                <div v-for="(settings, index) in dataModule.module.settings" :key="index" class="col-12 card-modules">
                  <div class="card collapsed-card bg-grey card-modules">
                    <div class="card-header p-2 pl-3">
                      <h6 class="card-title card-title-padding text-font-modules">{{settings.name}}</h6>
                      <div class="card-tools">
                        <a @click="active_desactive((!settings.active) ? 1 : 0, settings.relid)" class="btn btn-sm mx-2" :class="(!settings.active) ? 'bg-two' : 'bg-dark' ">
                          <i class="fas" :class="(!settings.active) ? 'fa-check' : 'fa-trash-alt' "></i>
                          <span class="mobile-modules">{{(!settings.active) ? 'Activar' : 'Desactivar' }}</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="dataModule.module.settings.length == 0" class="card-body">
                <div class="text-center">
                  <a class="btn-add-module p-5" href="#">
                    <h4 class="text-add-module">No existen ajustes para este modulo</h4>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 pt-2">
            <div class="card card-widget widget-user-2 m-0">
              <div class="card-header bg-one">
                <h3 class="card-title card-title-padding">Submodulos</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool text-white" data-card-widget="collapse"><i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>
              <div v-if="dataModule.module.sub != ''" class="card-body p-0">
                <div v-for="(submodulo, index) in dataModule.module.sub" :key="index" class="col-12 card-modules">
                  <div class="card collapsed-card bg-grey card-modules">
                    <div class="card-header p-2 pl-3">
                      <h6 class="card-title card-title-padding text-font-modules">{{submodulo.name}} {{submodulo.version}}</h6>
                      <div class="card-tools">
                        <a @click="openSubmodules(submodulo)" class="btn btn-sm mx-2 bg-two">
                          <i class="fas fa-cog"></i>
                        </a>
                      </div>
                      <div class="card-tools">
                        <a @click="migrate(submodulo.relid, index)" class="btn btn-sm mx-2 bg-one">
                          <i class="fas fa-share-square"></i>
                          <span class="mobile-modules">Migrar Submodulo</span>
                        </a>
                      </div>
                      <div class="card-tools">
                        <a @click="unistallSubmodule(submodulo.relid)" class="btn btn-sm mx-2 bg-dark">
                          <i class="fas fa-trash-alt"></i>
                          <span class="mobile-modules">Desintalar</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="dataModule.module.sub == ''" class="card-body">
                <div class="text-center">
                  <a class="btn-add-module p-5" href="#">
                    <h3 class="text-add-module">No existen submodulos para este modulo</h3>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div slot="modal-footer">
        <div class="modal-footer">
          <a @click="actualization(dataModule.module.relid)" class="btn bg-one text-white">Actualizar modulo</a>
        </div>
      </div>
    </StackModal>
    <configSubmodules :showSubmodules="showSubmodules" :dataSubmodule="dataSubmodule" @closeModal="closeModal" @refreshDataLocal="refreshDataLocal" />

    <verifyMigrate @refreshDataLocal="refreshDataLocal" :showCheck="modalVerify" @closeModal="closeModal" :dataId="dataId"/>
  </div>
</template>

<script>
import StackModal from '@innologica/vue-stackable-modal';
import configSubmodules from './configSubmodules.vue';
import verifyMigrate from '../migrations/verifyMigrate.vue';

export default {
  name: 'detailsModulesApp',
  data(){
    return{
      modalClass: 'modal-xl',
      showSubmodules: false,
      dataSubmodule: null,
      optionsMod: [],
      submoduleAdd: null,
      OptionsSubmodules: [],
      dataId: null,
      modalVerify: false,
    }
  },
  components:{
    StackModal,
    configSubmodules,
    verifyMigrate
  },
  props:[
    'showDetails',
    'dataModule'
  ],
  async mounted(){
    await this.$store.dispatch('submodules/getSubodulesOptions');
    this.getOptionsSubmodules();
  },
  methods:{
    async getOptionsSubmodules(){
      var request = await this.$store.dispatch('submodules/getSubodulesOptionsById', this.dataModule.module.id);
      if(!request.success){
        console.log(request);
      }else{
        this.OptionsSubmodules = [];
        for (var i = 0; i < request.data.length; i++) {
          this.OptionsSubmodules[i] = {
            label: request.data[i].name,
            value: request.data[i].id
          };
        }
      }
    },
    refreshDataLocal(index){
      this.$emit('refreshData', index);
    },
    // Cerrar modal de setting de submodulos
    closeModal(val, index = this.dataModule.index){
      this.refreshDataLocal(index);
      this.showSubmodules = val;
      this.modalVerify = val;
    },
    // open modals de submodulos
    openSubmodules(val, index){
      this.dataSubmodule ={
        data: val,
        index: this.dataModule.index,
      };
      this.showSubmodules = true;
    },
    migrate(id, index){
      console.log(id);
        this.dataId = {
          name: 'submodules',
          id,
          index
        }
        this.modalVerify = true;
    },
    // Añadir un submodulo
    async addSubmodule(){
      let loader = this.$loading.show({
        container: this.$refs.formContainer,
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 9999,
      });
        if(this.submoduleAdd != '' && this.submoduleAdd != null){
          console.log(this.submoduleAdd.value);
          console.log(this.dataModule.idApp);
          const data = {
            submodule: this.submoduleAdd.value,
          };
          var thing = new FormData();
          for (let key in data) if (data[key]) thing.append(key, data[key]);

          var request = await this.$store.dispatch('submodules/addSubmodule', {data: thing, id:this.dataModule.idApp});
          if(!request.success){
            this.$toastr.error(request.data, 'Error');
          }else{
            this.$emit('refreshData', this.dataModule.index);
            this.$toastr.success(request.data, 'Exitoso');
          }
        }else{
          let messageError = null;
          this.$toastr.error("Porfavor inserte un submodulo adecuado", 'Error');
        }
      loader.hide();
    },
    // Activar o desactivar un ajuste del modulo
    async active_desactive(value, id){
      let loader = this.$loading.show({
        container: this.$refs.formContainer,
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 9999,
      });
      console.log(value);
      console.log(id);
        var thing = new FormData();
        thing.append('value', value);

        var request = await this.$store.dispatch('modules/activeDesactiveSetting', {data: thing, id});
        if(!request.success){
          this.$toastr.error(request.data, 'Error');
        }else{
          this.$emit('refreshData', this.dataModule.index);
          this.$toastr.success(request.data, 'Exitoso');
        }
      loader.hide();
    },
    // Actualizar modulo
    async actualization(id){
      let loader = this.$loading.show({
        container: this.$refs.formContainer,
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 9999,
      });
        var request = await this.$store.dispatch('modules/actualizationModule', id);
        if(!request.success){
          this.$toastr.error(request.data, 'Error');
        }else{
          this.$emit('refreshData', this.dataModule.index);
          this.$toastr.success(request.data, 'Exitoso');
        }
      loader.hide();
    },
    // Desintalar submodulo
    async unistallSubmodule(id){
      let loader = this.$loading.show({
        container: this.$refs.formContainer,
        color: '#007bff',
        width: 80,
        height: 80,
        backgroundColor: '#000000',
        opacity: 0.8,
        zIndex: 9999,
      });
        var request = await this.$store.dispatch('submodules/deleteSubmodule', id);
        if(!request.success){
          this.$toastr.error(request.data, 'Error');
        }else{
          this.$emit('refreshData', this.dataModule.index);
          this.$toastr.success(request.data, 'Exitoso');
        }
      loader.hide();
    }
  },
  watch: {
    dataModule: {
      handler() {
        this.getOptionsSubmodules();
      }
    }
  },
}
</script>
