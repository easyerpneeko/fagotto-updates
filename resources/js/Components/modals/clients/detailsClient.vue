<template>
  <StackModal
    v-if="dataDetails"
    :show="showModal"
    @close="$emit('changeState', false)"
    :modal-class="{ [modalClass]: true }"
  >
    <div slot="modal-header">
      <div class="modal-header bg-one">
        <h5 class="modal-title">Detalles de cliente</h5>
        <a class="close p-2" aria-label="Close" @click="$emit('changeState', false)">
          <span aria-hidden="true">&times;</span>
        </a>
      </div>
    </div>

    <div class="modal-body">
      <div class="row scrollApp">

        <div class="col-md-6 col-12">
          <div class="card card-primary card-outline">
            <div class="box-profile p-3">
              <div class="text-center">
                <img class="profile-user-img img-fluid img-circle"
                :src="getImage(dataDetails.client.sexo)"
                alt="User profile picture">
              </div>

              <h3 class="profile-username text-center">{{dataDetails.client.username}}</h3>

              <p class="text-muted text-center">{{dataDetails.client.rut}}</p>

              <ul class="list-group list-group-unbordered border-bottom-none">
                <li class="list-group-item p-2 px-3">
                  <b>Aplicaciones</b> <a class="float-right">{{dataDetails.client.numAplications}}</a>
                </li>
                <li class="list-group-item p-2 px-3">
                  <b>Sexo</b> <a class="float-right">{{(dataDetails.client.sexo == 'male') ? 'Hombre' : 'Mujer' }}</a>
                </li>
                <li class="list-group-item p-2 px-3">
                  <b>Dirección</b> <a class="float-right">{{dataDetails.client.direction}}</a>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-12">
          <div class="card card-widget">
            <div class="card-header">
              <div class="user-block">
                <img class="img-circle" :src="getImage(dataDetails.client.sexo)" alt="User Image">
                <span class="username c-one"><a href="#">{{dataDetails.client.username}}</a></span>
                <span class="description">{{dataDetails.client.rut}}</span>
              </div>
            </div>
            <!-- /.card-header -->
            <div class="p-4">
              <!-- post text -->
              <p>
                {{dataDetails.client.description}}
              </p>
            </div>
          </div>
        </div>

        <div v-for="(aplication, index) in dataDetails.aplication"  :key="index" class="col-12">
          <div class="card collapsed-card" :class="(aplication.expiration) ? 'bg-one' : 'bg-dark'">
            <div class="card-header">
              <h3 class="card-title card-title-padding">{{aplication.name}}</h3>

              <div class="card-tools">
                <button type="button" class="btn btn-tool text-white margin-n1" data-card-widget="collapse"><i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="info-box" :class="(aplication.expiration) ? 'bg-one' : 'bg-dark'">
                <span v-if="aplication.expiration" class="info-box-icon"><i class="far fa-check-circle"></i></span>
                <span v-if="!aplication.expiration" class="info-box-icon"><i class="far fa-times-circle"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">Cliente: {{dataDetails.client.username}}</span>
                  <span class="info-box-number"><span class="dis-none">Serial: </span>{{aplication.serial}}</span>

                  <div v-if="!aplication.expiration" class="progress">
                    <div class="progress-bar" style="width: 100%"></div>
                  </div>
                  <div v-if="aplication.expiration" class="progress">
                    <div class="progress-bar" style="width: 60%"></div>
                  </div>
                  <span v-if="aplication.expiration" class="progress-description">
                    La aplicacion vence en {{ aplication.expiration | moment("from") }}
                  </span>
                  <span v-if="!aplication.expiration" class="progress-description">
                    Aplicacion vencida
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div slot="modal-footer">
      <div class="modal-footer">
        <a @click="$emit('changeState', false)" class="btn bg-one text-white">Cerrar</a>
      </div>
    </div>
  </StackModal>
</template>

<script>
import StackModal from '@innologica/vue-stackable-modal';
import BaseUrl from "../../../../assets/helpers/BaseUrl";

export default {
  name: 'detailsClients',
  data(){
    return{
      modalClass: 'modal-xl',
    }
  },
  mounted(){
  },
  components:{
    StackModal
  },
  props:[
    'dataDetails',
    'showModal'
  ],
  methods:{
    getImage(val){
      if(val == "male")
        return BaseUrl.getUrl('images/male.jpg');
      else
        return BaseUrl.getUrl('images/female.jpg');
    },
  },
}
</script>

<style scoped>
  .card-primary{
    /* background-color: #0B4F6C !important; */
    border-top-color: #0B4F6C !important;
  }
  .border-bottom-none{
    border-bottom: none !important;
  }
  .modal-body {
    padding-left: 10px !important;
  }
  .card-body{
    margin: 0px !important;
    padding: 0px !important;
  }
  .info-box{
    margin-bottom: 0px !important;
  }
  .info-box-icon{
    font-size: 50px !important;
  }
  @media (max-width: 575px) {
    .modal-body {
      padding: 0px !important;
    }
  }
  @media (max-width: 450px) {
    .dis-none {
      display: none;
    }
  }
  @media (max-width: 400px) {
    .info-box-number{
      font-size: 14px !important;
    }
    .progress-description{
      font-size: 14px !important;
    }
    .progress-bar{
      min-width: 0px !important;
    }
    .info-box-icon{
      font-size: 40px !important;
    }
    .card-title{
      font-size: 15px !important;
    }
  }
</style>
