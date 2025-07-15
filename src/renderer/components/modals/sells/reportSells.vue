<template>
    <div class="modal fade" id="reportSellsModal" tabindex="-1" role="dialog" aria-labelledby="reportSellsModal"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reporte de ventas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label>Rango de fechas</label>
                    <div class="col-12">
                        <date-picker class="widthInput" format="YYYY-MM-DD" type="date" v-model="rangeDate" range
                            placeholder="Fechas" confirm></date-picker>
                    </div>
                    <label for="type">Tipo de ventas</label>
                    <div class="bg-white col-12 border-radius-4 elevation-1 mt-2">
                        <div class="row p-3">
                            <div v-for="(item, index) in ckecks" :key="index" class="col-6 w-auto px-3">
                                <div class="custom-control custom-checkbox mx-2 my-1">
                                    <input :disabled="offOn" type="checkbox" class="custom-control-input" :id="item.key"
                                        v-model="item.value">
                                    <label class="custom-control-label" :for="item.key">{{ item.label }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn bg-secundario text-white" data-dismiss="modal"
                        :disabled="waitResponse">Cerrar</button>
                    <button type="button" class="btn bg-primario text-white" :disabled="waitResponse"
                        data-toggle="modal" @click="generarReporte()">Generar Reporte</button>
                </div>
            </div>

        </div>
    </div>
    </div>
</template>

<script>
import $ from 'jquery';
import Loader from '@/helpers/Loader';
import moment from 'moment';
import Print from '@/helpers/Print.js';

export default {
    name: 'ReportSells',

    data() {
        return {
            waitResponse: false,

            rangeDate: [],
            // checks
            ckecks: [
                { key: 'factura', label: 'Facturas', value: false },
                { key: 'boleta', label: 'Boletas', value: false },
                { key: 'nota_de_credito', label: 'Nota de Credito', value: false },
                { key: 'guia_de_despacho', label: 'Guia de despacho', value: false },
                { key: 'debito', label: 'Debito', value: false },
                { key: 'transferencia', label: 'Transferencia', value: false },
                { key: 'cheque', label: 'Cheque', value: false },
                { key: 'banco', label: 'Banco', value: false },
                { key: 'amipass', label: 'Amipass', value: false },
                { key: 'multicaja', label: 'Multicaja', value: false },
                { key: 'edenred', label: 'Edenred', value: false },
                { key: 'convenio_empresa', label: 'Convenio Empresa', value: false },
                { key: 'sodexo', label: 'Sodexo', value: false },
                { key: 'efectivo', label: 'Efectivo', value: false },
                { key: 'credito', label: 'Credito', value: false },
                { key: 'rappi', label: 'Rappi', value: false },
                { key: 'junaeb', label: 'Junaeb', value: false },
                { key: 'uber', label: 'Uber', value: false },
                { key: 'pedidos_ya', label: 'Pedidos Ya', value: false },
                { key: 'pluxee', label: 'Pluxee', value: false }
            ]
        }
    },
    mounted() {

    },
    methods: {
        closeModal() {
            $('#subcategoriesCrudModal').modal('hide');
        },
        async refreshData() {
            this.waitResponse = true;
            this.getSubcategoriesByCategory(this.category.id);
            this.waitResponse = false;
        },
        async getTypeSells() {
            // this.waitResponse = true;
            // let request = await this.$store.dispatch("operations/getSubcategoriesByCategory", id);
            // console.log(request);
            // if (request.success) {
            //     // this.$awn.success('Subcategorias', { labels: { success: 'CORRECTO' } });
            //     // this.refreshData();
            //     this.subcategoriesByCategory = request.data;
            // } else {
            //     this.$awn.alert('Error en el servidor');
            // }
            // this.waitResponse = false;
        },
        async generarReporte() {
            // Parametros para los contadores
            var params = '?params=true';
            this.ckecks.map((key) => {
                if (key.value){
                    params += '&' + key.key + '=' + key.value;
                } 
            });
            
            if (this.rangeDate && this.rangeDate.length > 0) {
                // Rango de fechas
                var startDate = moment(this.rangeDate[0]).format('YYYY-MM-DD') + ' ' + '00:00:00';
                var endDate = moment(this.rangeDate[1]).format('YYYY-MM-DD') + ' ' + '23:59:59';
                params += '&startDate=' + startDate;
                params += '&endDate=' + endDate;
            } else {
                return this.$awn.alert('Indique la fecha del reporte');
            }

            this.waitResponse = true;
            let request = await this.$store.dispatch("sells/getReport", params);
            if (request.success) {
                // Guardando documento
                await Print.downloadExcel(request.data);
                this.$awn.success('Descarga exitosa', { labels: { success: 'CORRECTO' } });
            } else {
                this.$awn.alert('Error en el servidor');
            }
            this.waitResponse = false;
        }
    },
    computed: {
        // categories: {
        //     get() {
        //         return this.$store.getters['operations/categories'];
        //     }
        // },
        offOn: {
            get() { return this.value },
            set(offOn) { this.$emit('input', offOn) }
        },
    },
}
</script>