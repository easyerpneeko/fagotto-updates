<template>
    <div class="modal fade" id="verifyDelete" tabindex="-1" role="dialog" aria-labelledby="verifyDelete"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primario">
                    <h5 class="modal-title text-capitalize">
                        <i class="text-white fas fa-exclamation-triangle"></i>
                        <span v-if="propVerify" class="pl-1 pt-1">{{ propVerify.title }}</span>
                    </h5>
                </div>
                <div class="modal-body px-0 pt-2">
                    <h5 v-if="propVerify" class="px-3">
                        {{ propVerify.text }}
                    </h5>
                    <div class="col-12 px-3">
                        <label for="trash_comment">Razon:</label>
                        <input id="trash_comment" required placeholder="Escribe la razon de la devolucion"
                            v-model="reason" type="text" class="form-control"
                            :class="{ 'invalid-input': submitted && !isValidReason }" />
                    </div>
                    <div class="col-12 px-3">
                        <label for="trash_comment">Cantidad:</label>
                        <input id="trash_comment" min="1" required v-model="this.quantity" type="number"
                            class="form-control" :class="{ 'invalid-input': submitted && !isValidQuantity }" />
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
    props: [
        'propVerify',
    ],
    data() {
        return {
            submitted: false,
            reason: '',
            quantity: 1
        }
    },
    methods: {
        async removeData() {
            // this.submitted = true;
            // if(!this.isValidComment) return false;
            $('#verifyDelete').modal('hide');
            $('#detailSell').modal('hide');

            Loader.dinamic();
            // Iniciando peticion
            let fd = new FormData();
            fd.append('reason', this.reason);
            fd.append('quantity', this.quantity);

            var request = await this.$store.dispatch(this.propVerify.store, { params: this.propVerify.params, formData: fd });

            Loader.hide();
            // Validando respuesta
            if (request.success) {
                this.$awn.success(this.propVerify.success, { labels: { success: 'CORRECTO' } });
                this.$emit('refreshData', request.data, true);
                this.submitted = false;
                this.reason = "";
                this.quantity = 1;
            } else {
                this.submitted = false;
                this.reason = "";
                this.quantity = 1;
                if (request.data.id) {
                    this.$awn.success(this.propVerify.success, { labels: { success: 'CORRECTO' } });
                    this.$emit('refreshData', request.data, true);
                }
                console.log(request.data);
                let allErrors = request.data;
                if (typeof (allErrors) == 'object') {
                    for (var errorkey in allErrors) {
                        if (allErrors[errorkey]) {
                            for (var error of allErrors[errorkey]) {
                                this.$awn.alert(error);
                            }
                        }
                    }
                } else {
                    this.$awn.alert(allErrors);
                }
            }
        },
    },
    computed: {
        isValidReason: {
            get() { return this.reason.length > 0 }
        },
        isValidQuantity: {
            get() { return this.quantity.length > 1 && this.quantity.length <= propVerify.quantity }
        }
    }
}
</script>

<style scoped>
input {
    border-radius: 5px;
}

.invalid-input {
    border-color: red;
}

.modal-md {
    width: 40vw !important;
}

@media (max-width: 1000px) {
    .modal-md {
        width: 60vw !important;
    }
}

@media (max-width: 650px) {
    .modal-md {
        width: 100vw !important;
        margin: 0px !important;
    }
}
</style>