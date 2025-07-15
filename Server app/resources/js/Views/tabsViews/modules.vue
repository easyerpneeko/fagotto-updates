<template>
  <div>
    <div class="row m-0">
      <div class="cardModule px-2 mb-2">
        <card-module @refreshApp="refreshApp" />
      </div>
      <div class="cardAddModule px-2 mb-2">
        <add-module  @refreshApp="refreshApp" />
      </div>
    </div>
  </div>
</template>

<script>
import addModule from '../../Components/cards/addModule.vue';
import cardModule from '../../Components/cards/modules.vue';

export default {
  components:{
    addModule,
    cardModule
  },
  methods:{
    refreshApp(key = true){
      this.$emit('refreshApp', key)
    }
  },
  computed:{
    app:{ get(){ return this.$store.getters['aplication/getterApp']; } },
  },
}
</script>

<style lang="scss">
// Styles home
.cardModule{
  flex: 0 0 60%;
  width: 60%;
}
.cardAddModule{
  flex: 0 0 40%;
  width: 40%;
}
@media (max-width: 1250px){
  .cardModule{
    flex: 0 0 55%;
    width: 55%;
  }
  .cardAddModule{
    flex: 0 0 45%;
    width: 45%;
  }
}
@media (max-width: 1080px){
  .cardModule{
    flex: 0 0 100%;
    width: 100%;
  }
  .cardAddModule{
    flex: 0 0 50%;
    width: 50%;
  }
}
@media (max-width: 991px){
  .cardModule{
    flex: 0 0 55%;
    width: 55%;
  }
  .cardAddModule{
    flex: 0 0 45%;
    width: 45%;
  }
}
@media (max-width: 850px){
  .cardModule{
    flex: 0 0 100%;
    width: 100%;
  }
  .cardAddModule{
    flex: 0 0 50%;
    width: 50%;
  }
}
</style>
