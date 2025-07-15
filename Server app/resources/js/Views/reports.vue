<template>
    <div v-if="true" class="container-fluid">
        <HeaderAdmin :title="'Reportes'" />
        <pre>Json</pre>
        <h4>dasdsa</h4>
    </div>
</template>
<script>
import HeaderAdmin from '../Components/header.vue';
import paginate from '../Components/paginate.vue';

export default {
    name: 'Reports',
    data() {
        return {
            pageActual: 1
        }
    },
    components: {
        HeaderAdmin,
        paginate
    },

    mounted() {
        this.getAplications();
    },
    methods: {
        async getAplicationRut() {
            await this.$store.dispatch('aplication/getAplicationRut', this.rutClient);
        },
        async getAplications(val = false) {
            let loader = this.$loading.show({
                // Optional parameters
                container: this.$refs.formContainer,
                color: '#007bff',
                width: 80,
                height: 80,
                backgroundColor: '#000000',
                opacity: 0.8,
                zIndex: 999,
            });
            if (val) {
                this.pageActual = val;
            }
            var filterdata = '?page=' + this.pageActual;
            await this.$store.dispatch('aplication/getAplications', filterdata);
            loader.hide();
        },
        openModalDetails(id) {
            this.dataEdit = id;
            this.modalDetails = true;
        },
    },
    computed: {
        aplications: {
            get() {
                return this.$store.getters['aplication/getterAplications'];
            }
        }
    },
}

</script>