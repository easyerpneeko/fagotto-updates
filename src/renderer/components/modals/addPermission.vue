<template>
  <div class="modal fade" id="addPermissionModal" tabindex="-1" role="dialog" aria-labelledby="addPermissionModal" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Añadir Nuevo Privilegio</h5>
          <button type="button" class="close" @click="closeModal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body modal-body-p-0">
          <form role="form" class="p-4">
            <div class="row">
              <div class="form-group col-12">
                <label for="module">Módulo</label>
                <select v-model="selectedModule" class="form-control" id="module" :disabled="waitResponse">
                  <option value="">Selecciona un módulo</option>
                  <option value="productos">Productos</option>
                  <option value="ventas">Ventas</option>
                  <option value="cafeteria">Cafetería</option>
                  <option value="client_orders">Órdenes de clientes</option>
                  <option value="pedidos">Pedidos</option>
                  <option value="operations">Operaciones</option>
                </select>
              </div>
              <div class="form-group col-12">
                <label for="permissionKey">Clave del privilegio</label>
                <input 
                  v-model="permissionKey" 
                  type="text" 
                  :disabled="waitResponse" 
                  class="form-control" 
                  id="permissionKey" 
                  placeholder="acceso_crear_productos"
                  @keypress="validaInput($event)"
                >
                <small class="form-text text-muted">Solo letras minúsculas, números y guiones bajos</small>
              </div>
              <div class="form-group col-12">
                <label for="permissionDescription">Descripción del privilegio</label>
                <input 
                  v-model="permissionDescription" 
                  type="text" 
                  :disabled="waitResponse" 
                  class="form-control" 
                  id="permissionDescription" 
                  placeholder="Acceso a crear productos"
                >
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal" :disabled="waitResponse">
            Cancelar
          </button>
          <button type="button" class="btn btn-success" :disabled="waitResponse || !isFormValid" @click="addPermission">
            {{ waitResponse ? 'Añadiendo...' : 'Añadir Privilegio' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Loader from '@/helpers/Loader';
import ConfigHelper from '@/helpers/ConfigHelper';

export default {
  name: 'addPermissionModal',
  data() {
    return {
      waitResponse: false,
      selectedModule: '',
      permissionKey: '',
      permissionDescription: ''
    }
  },
  computed: {
    isFormValid() {
      return this.selectedModule && 
             this.permissionKey && 
             this.permissionDescription &&
             this.permissionKey.length >= 3;
    }
  },
  methods: {
    validaInput(event) {
      var ch = String.fromCharCode(event.which);
      // Solo permitir letras minúsculas, números y guiones bajos
      if ((/[^a-z0-9_]+/.test(ch))) {
        event.preventDefault();
      }
    },
    
    async addPermission() {
      if (!this.isFormValid) {
        this.$awn.alert('Por favor completa todos los campos correctamente');
        return;
      }

      this.waitResponse = true;
      Loader.fullPage();

      try {
        // Preparar los datos
        const data = new FormData();
        data.append('module_keyname', this.selectedModule);
        data.append('permission_key', this.permissionKey);
        data.append('permission_description', this.permissionDescription);

        // Hacer la petición al backend
        const response = await fetch('/api/local/permissions/add', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'App-Key': ConfigHelper.Config().Serial
          },
          body: data
        });

        const result = await response.json();

        if (result.success) {
          this.$awn.success('Privilegio añadido exitosamente', {labels:{success:'CORRECTO'}});
          
          // Actualizar la configuración
          await this.$store.dispatch('main/refreshData');
          
          this.resetForm();
          this.$emit('permissionAdded', result.new_permission);
          this.closeModal();
        } else {
          this.$awn.alert(result.error || 'Error al añadir el privilegio');
        }
      } catch (error) {
        console.error('Error:', error);
        this.$awn.alert('Error de conexión al añadir el privilegio');
      }

      Loader.hide();
      this.waitResponse = false;
    },

    resetForm() {
      this.selectedModule = '';
      this.permissionKey = '';
      this.permissionDescription = '';
    },

    closeModal() {
      this.resetForm();
      $('#addPermissionModal').modal('hide');
    }
  }
}
</script>

<style scoped>
.form-control:disabled {
  background-color: #f8f9fa;
}
</style>
