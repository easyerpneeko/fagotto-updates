<template>
  <div class="card">
    <div :class="['card-header', (app.Expiration && app.Active) ? 'bg-one' : 'bg-dark']">
      <h3 class="card-title card-title-padding">Detalles</h3>

      <div class="card-tools">
        <button type="button" class="btn btn-tool margin-n1 text-white" data-card-widget="collapse">
          <i class="fas fa-minus"></i>
        </button>
      </div>
    </div>
    <div class="card-body p-0 m-0">
      <div class="info-box">

        <span class="icon-check info-box-icon">
          <i :class="['far',(app.Expiration && app.Active) ? 'fa-check-circle c-one' : 'fa-times-circle c-dark'] "></i>
        </span>

        <div  class="info-box-content pb-3">
          <span class="info-box-text d-flex flex-wrap">
            <div>Cliente: &nbsp;</div>
            <div v-if="app.Client">{{app.Client.username}}</div>
          </span>
          <span class="info-box-text">Estado: {{(app.Expiration)  ? 'Activa' : (app.Active) ? 'Expirada' : 'Servicio Cortado' }}</span>
          <span class="info-box-number d-flex flex-wrap">
            <div>Serial: &nbsp;</div>
            <div>{{app.Serial}}</div>
          </span>

          <div class="progress">
            <div class="progress-bar" :style="[(app.Expiration && app.Active) ? 'width: 60%' : 'width: 100%']"></div>
          </div>

          <span v-if="app.Expiration" class="progress-description">
            <div v-if="app.Active">
              La aplicacion vence en {{app.Expiration | moment("from")}}
            </div>
            <div v-else>
              Aplicacion cortada
            </div>
          </span>
          <span v-else class="progress-description">
            {{(app.Active) ? 'Aplicación vencida' : 'Aplicacion cortada'}}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  computed:{
    app:{ get(){ return this.$store.getters['aplication/getterApp']; } },
  },
}
</script>

<style lang="scss" scoped>
// Styles expired
.card{
  margin: 0 !important;
}
.info-box{
  margin-bottom: 0px !important;
  color: #292F36;
}
.info-box .info-box-content{
  min-width: 40px !important;
}
.info-box-icon{
  font-size: 50px !important;
}
.card-title{
  font-size: 1.5rem;
}
.info-box-text{
  text-overflow: none;
  white-space: normal;
}
.progress-description{
  text-overflow: none;
  white-space: normal;
}
@media (max-width: 400px){
  .icon-check{
    display: none;
  }
}
</style>
