<template>
    <div class="product-bg p-3">
        <div class="d-flex w-100 h-100 flex-column justify-content-center align-items-center">
            <div class="pl-2 d-flex row w-100">
                <div class="card title-card col-md-5 col-sm-7 col-12">
                    <div class="card-body">
                        <h5 class="font-weight-bold m-0">Gestion de Folios</h5>
                        <span>Cargas folios del negocio</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-5 pb-3 px-3 px-sm-5">
            <div class="row m-0 mb-2" v-if="this.siiInstalled">

                <div class="col-md-12 col-12 order-md-2">
                    <div class="card card-widget widget-user-2 m-0">
                        <div class="card-header bg-one">
                            <h3 class="card-title card-title-padding">Cargar folio</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group w-100 my-2">
                                <label for="xml">Folios en XML</label>
                                <!-- v-model="xml_string" -->
                                <textarea class="form-control rounded-0" id="xml" rows="5"></textarea>
                                <!-- @change="XMLToString" -->
                                <input type="file" id="filexml" ref="xmlFile" accept="text/xml" @change="XMLToString"
                                    style="display: none">
                            </div>
                            <!-- @click="sendFolios(false)" -->
                            <a class="btn bg-one text-white mt-3 mx-1 text-bold" @click="sendFolios(false)"
                                style="float:right;">Cargar folios</a>
                            <label for="filexml" class="btn bg-one text-white mt-3 mx-1 text-bold"
                                style="float:right;">Subir folios</label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- v-else -->
            <div class="m-0 my-2 text-center w-100" v-else>
                <h5>El modulo de SII no se encuentra activado</h5>
            </div>
        </div>

    </div>
</template>
  
<script>
// Helpers
import ConfigHelper from '@/helpers/ConfigHelper.js';
import BaseUrl from '@/helpers/baseUrl.js';
import Loader from '@/helpers/Loader';

export default {
    name: 'folios',
    // props: ['value', 'feedsWatch'],
    //   components:{ feedCard },
    data() {
        return {
            // foliosFactura: 0,
            // foliosBoleta: 0,
            // foliosNotaCredito: 0,
            // foliosGuiaDespacho: 0,
            xml_string: null,
            app_id: 0
        }
    },
    async mounted() {
        await this.getApp();
    },
    computed: {
        // offOn: {
        //     // get() { return this.value },
        //     // set(offOn) { this.$emit('input', offOn) }
        // },
        // feeds: {
        //     // get() { return this.$store.getters['main/getFeeds'] },
        //     // set(val) { return this.$store.commit('main/setProperty', { key: 'feeds', data: val }) }
        // },
        // me: { get() { return this.$store.getters['main/user']; } },

        siiInstalled: { async get() { return await ConfigHelper.ConfStr('modulos.ventas.submodulos.sii'); } },

    },
    watch: {
        // async feedsWatch(val) {
        //     if (val) {
        //         // this.offOn = true;
        //         // await this.$store.dispatch('main/getFeeds');
        //         // await this.getFolios()
        //         // console.log('SII:', this.siiInstalled);
        //         // this.offOn = false;
        //     }
        // }
    },
    methods: {
        // countFolios(request) {
        //     // if (request.data.length > 0) {
        //     //     request.data.map((item) => {
        //     //         if (item.type == 'factura') this.foliosFactura++;
        //     //         if (item.type == 'boleta') this.foliosBoleta++;
        //     //         if (item.type == 'nota_de_credito') this.foliosNotaCredito++;
        //     //         if (item.type == 'guia_de_despacho') this.foliosGuiaDespacho++;
        //     //     });
        //     // }
        // },
        // Cargar folios
        async sendFolios(xml_string = false) {
            if (xml_string !== false) this.xml_string = xml_string;

            // Verificando campo
            // if(this.xml_string == '' || this.xml_string == null) return this.$toastr.error('Por favor inserte un xml', 'Error');
            if (this.xml_string == '' || this.xml_string == null) console.log('El campo esta vacio');

            let loader = this.$loading.show({
                color: '#007bff',
                width: 80,
                height: 80,
                backgroundColor: '#000000',
                opacity: 0.8,
                zIndex: 9999,
            });
            var data = new FormData();
            data.append('xml_string', this.xml_string);
            // Iniciando peticion
            var request = await this.$store.dispatch('main/sendFolios', { id: this.app_id, data });
            // Verificando datos
            // if(!request.success) this.$toastr.error(request.data, 'Error');
            if (!request.success) console.log('Error: ', request.data);
            else {
                this.xml_string = null;
                this.$refs.xmlFile.files = null;
                // await this.getFolios()
                // this.$toastr.success(request.data, 'Exitoso');
                console.log('Exitoso');
            }
            loader.hide();
        },
        // Traer los folios
        // async getFolios() {
            // this.folios.map((key)=>{
            //   key.value = 0;
            // });
            // Iniciando peticion
            // let app = await this.getApp();
            // console.log('app id:', this.app_id);
            // var request = await this.$store.dispatch('main/getFolios', this.app_id);
            // // Verificando datos
            // this.countFolios(request);
        // },

        async XMLToString() {
            var fileInInput = this.$refs.xmlFile.files[0];
            var reader = new FileReader();
            var _this = this;

            var file = reader.onload = ((theFile) => {
                return async function (e) {
                    await _this.sendFolios(e.target.result);
                }
            })(fileInInput);

            reader.readAsText(fileInInput);

        },

        async getApp() {
            var request = await this.$store.dispatch('main/refreshData', '?slim');
            this.app_id = request.data.Id;
            return request;
        }
    }
}
</script>
<style scoped>

.card {
    border-radius: 5px;
    -webkit-box-shadow: 0 1px 2.94px 0.06px rgba(4,26,55,0.16);
    box-shadow: 0 1px 2.94px 0.06px rgba(4,26,55,0.16);
    border: none;
    margin-bottom: 30px;
    -webkit-transition: all 0.3s ease-in-out;
    transition: all 0.3s ease-in-out;
}

.card .card-block {
    padding: 25px;
}
.card-title {
    float: left;
    font-size: 1.1rem;
    font-weight: 400;
    margin: 0;
}
.bg-one {
    background-color: var(--primary);
    color: #fff!important;
}
</style>
  