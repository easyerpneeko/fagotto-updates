<template>
    <div>
        <div class="card card-widget elevation-1">
            <div class="card-header d-flex justify-content-between bg-primario">
                <span v-if="cardTitle" class="text-bold">{{ cardTitle }}</span>
                <div class="ml-auto">
                    <button type="button" class="btn btn-tool elevation-0" data-toggle="collapse"
                        :data-target="'#' + idTarget" aria-expanded="false" :aria-controls="idTarget">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="px-3 collapse show scrollTableReport" :id="idTarget">
                <bar-chart :data="chartData"></bar-chart>
            </div>
        </div>
    </div>
</template>
  
<script>
export default {
    props: {
        chartData: {
            type: Array,
            required: true
        },
        cardTitle: '',
        idTarget: ''
    }
};
</script>