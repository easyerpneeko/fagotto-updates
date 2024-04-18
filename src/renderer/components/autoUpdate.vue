<template>
    <div id="notification" class="hidden">
        <p id="message"></p>
        <button id="close-button" @click="closeNotification()" class="btn">
            Cerrar
        </button>
        <button id="restart-button" @click="restartApp()" class="d-none btn btn-primary">
            Reiniciar
        </button>
    </div>
</template>
<script>
const { ipcRenderer } = require('electron');

export default {
    name: 'autoUpdate',
    data(){
    return{
      appInProduction: process.env.NODE_ENV
    }
  },
    mounted() {
        //Para el auto actualizador
        console.log('Buscando actualizacion...');
        const version = document.getElementById('version');
        ipcRenderer.send('app_version');
        ipcRenderer.on('app_version', (event, arg) => {
            ipcRenderer.removeAllListeners('app_version');
            // version.innerText = 'Version ' + arg.version;
        });

        const notification = document.getElementById('notification');
        const message = document.getElementById('message');
        const restartButton = document.getElementById('restart-button');
        ipcRenderer.on('update_available', () => {
            console.log('update available');
            ipcRenderer.removeAllListeners('update_available');
            message.innerText = 'Una nueva version esta disponible. Descargando...';
            notification.classList.remove('hidden');
        });
        ipcRenderer.on('update_downloaded', () => {
            console.log('update downloaded');
            ipcRenderer.removeAllListeners('update_downloaded');
            message.innerText = 'Nueva version descargada. Sera instalada al reiniciar el programa. Reiniciar Ahora mismo?';
            restartButton.classList.remove('d-none');
            notification.classList.remove('hidden');
        });

        ipcRenderer.on('message', function (event, text) {
            var container = document.getElementById('messages');
            var message = document.createElement('div');
            message.innerHTML = text;
            container.appendChild(message);
        })

    },
    methods: {
        closeNotification() {
            notification.classList.add('hidden');
        },
        restartApp() {
            ipcRenderer.send('restart_app');
        }
    }
}
</script>