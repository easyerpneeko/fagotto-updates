<template>
    <div class="modal fade" id="verifyDelete" tabindex="-1" role="dialog" aria-labelledby="verifyDelete" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primario">
            <h5 class="modal-title text-capitalize">
              <i class="text-white fas fa-exclamation-triangle"></i>
              <span v-if="propVerify" class="pl-1 pt-1">{{propVerify.title}}</span>
            </h5>
          </div>
          <div class="modal-body px-0 pt-2">
            <h5 v-if="propVerify" class="px-3">
              {{propVerify.text}}
            </h5>
            <div class="col-12 px-3">
              <label for="trash_comment">Comentario</label>
              <input id="trash_comment" required placeholder="Escribe el motivo de la eliminacion" v-model="trashComment" type="text" class="form-control" :class="{ 'invalid-input': submitted && !isValidComment}" />
            </div>
            <div class="text-right px-3">
              <button type="button" class="btn bg-secundario text-white" data-dismiss="modal">Cerrar</button>
              <button type="button" class="btn bg-primario text-white" @click="removeData">Confirmar</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import Loader from '@/helpers/Loader';
  
  export default {
    props:[
      'propVerify',
    ],
    data() {
        return {
            submitted:false,
            trashComment:''
        }
    },
    methods:{
      async removeData(){
        // this.submitted = true;
        // if(!this.isValidComment) return false;
        $('#verifyDelete').modal('hide');
        
        Loader.dinamic();
        // Iniciando peticion
        let fd = new FormData();
        fd.append('trash_comment',this.trashComment)
        
        var request = await this.$store.dispatch(this.propVerify.store,{id:this.propVerify.params,formData:fd}); 

        Loader.hide();
        // Validando respuesta
        if(request.success){
          this.$awn.success(this.propVerify.success,{labels:{success:'CORRECTO'}});
          this.$emit('refreshData', request.data, true);
          this.submitted = false;
        }else{
            this.submitted = false;
          if(request.data.id){
            this.$awn.success(this.propVerify.success,{labels:{success:'CORRECTO'}});
            this.$emit('refreshData', request.data, true);
          }
          console.log(request.data);
          let allErrors = request.data;
          if (typeof(allErrors) == 'object') {
            for (var errorkey in allErrors) {
              if (allErrors[errorkey]){
                for (var error of allErrors[errorkey]) {
                  this.$awn.alert(error);
                }
              }
            }
          }else{
            this.$awn.alert(allErrors);
          }
        }
      },
    },
    computed:{
      isValidComment:{
        get() {return this.trashComment.length>0}
      }
    }
  }
  </script>
  
  <style scoped>
    input{
      border-radius: 5px;
    }
    .invalid-input{
      border-color: red;
    }
    .modal-md{
      width: 40vw !important;
    }
    @media (max-width: 1000px){
      .modal-md{
        width: 60vw !important;
      }
    }
    @media (max-width: 650px){
      .modal-md{
        width: 100vw !important;
        margin: 0px !important;
      }
    }
  </style>
  