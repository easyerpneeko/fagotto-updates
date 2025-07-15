<template>
    <!-- Componente autoUpdate deshabilitado - Se usa UpdateDialog.vue -->
</template>
<script>
export default {
    name: 'autoUpdate',
    data(){
        return{
            appInProduction: process.env.NODE_ENV
        }
    },
    mounted() {
        // Auto-update manejado por UpdateDialog.vue
        console.log('autoUpdate component disabled - using UpdateDialog.vue');
    },
    methods: {
        closeNotification() {
            // Disabled
        },
        restartApp() {
            // Disabled
        }
    }
}
</script>