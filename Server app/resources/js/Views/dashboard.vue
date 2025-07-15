<template>
  <div v-if="dataGeneral" class="no container-fluid">
    <HeaderAdmin :title="'Inicio'" />

    <section class="content">
      <div class="">
        <h5 class="mb-2">Informacion general</h5>
        <div class="row">

          <div class="col-xl-3 col-md-4 col-sm-6 col-12 my-2">
            <router-link tag="a" to="/admin/aplicaciones" style="text-decoration: none; color: #000;">
              <div class="info-box">
                <span class="info-box-icon bg-one"><i class="fas fa-rocket"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Aplicaciones</span>
                  <span class="info-box-number">{{dataGeneral.aplications}}</span>
                </div>
              </div>
            </router-link>
          </div>

          <div class="col-xl-3 col-md-4 col-sm-6 col-12 my-2">
            <router-link tag="a" to="/admin/modulos" style="text-decoration: none; color: #000;">
              <div class="info-box">
                <span class="info-box-icon bg-two"><i class="far fa-copy"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Modulos</span>
                  <span class="info-box-number">{{dataGeneral.modules}}</span>
                </div>
              </div>
            </router-link>
          </div>

          <div class="col-xl-3 col-md-4 col-sm-6 col-12 my-2">
            <div class="info-box">
              <span class="info-box-icon bg-dark"><i class="fas fa-database"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Bases de datos</span>
                <span class="info-box-number">{{dataGeneral.databases}}</span>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-4 col-sm-6 col-12 my-2">
            <router-link tag="a" to="/admin/clientes" style="text-decoration: none; color: #000;">
              <div class="info-box">
                <span class="info-box-icon bg-warning text-white"><i class="fas fa-user-friends"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Clientes</span>
                  <span class="info-box-number">{{dataGeneral.clients}}</span>
                </div>
              </div>
            </router-link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import HeaderAdmin from '../Components/header.vue';
export default {
  data(){
    return{
    }
  },
  components:{
    HeaderAdmin,
  },
  mounted(){
    this.getData();
  },
  methods:{
    async getData(){
      await this.$store.dispatch('general/getData');
    },
  },
  computed:{
    dataGeneral:{
      get() {
        return this.$store.getters['general/getterData'];
      }
    }
  },
}
</script>
