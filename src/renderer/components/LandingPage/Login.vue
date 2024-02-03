<template>
  <div class="login-bg">
    <div class="d-flex w-100 h-100">
      <div class="login h-100 d-flex flex-column align-items-center justify-content-center">
        <div class="text-center mb-5 pb-5">
          <h1 class="logo text-center text-capitalize text-fontlogo pb-1">{{ name }}</h1>
          <h4 class="logo-text">Autentificacion</h4>
        </div>
        <div class="d-flex flex-column w-100 align-items-center px-4 pb-5">
          <div class="form-group w-100">
            <input type="text" class="form-control" placeholder="Nombre de Usuario" :disabled="waitResponse"
              v-model="username" @keyup.enter="sendLogin">
          </div>
          <div class="form-group w-100">
            <input type="password" class="form-control" placeholder="Contraseña" :disabled="waitResponse"
              v-model="password" @keyup.enter="sendLogin">
          </div>
          <div class="d-flex w-100 px-3">
            <div class="custom-control custom-checkbox pb-3">
              <input type="checkbox" class="custom-control-input" id="rememberPass" v-model="rememberPass">
              <label class="custom-control-label" for="rememberPass">Recuerdame</label>
            </div>
          </div>
          <button type="button" class="mt-2 btn btn-acceder py-2 px-4 text-capitalize" @click="sendLogin"
            :disabled="waitResponse">Acceder</button>
          <br />

        </div>
        <div class="gray-text-version" v-if="true">
          {{ version }}
        </div>
        <div class="gray-text-version" v-if="true">
          <b id="version"></b>
        </div>
        <autoUpdate />
      </div>
    </div>
  </div>
</template>

<script>
import ConfigHelper from '@/helpers/ConfigHelper';
import Loader from '@/helpers/Loader';
import autoUpdate from '../../components/autoUpdate.vue';
const fs = require('fs');
export default {
  components: { autoUpdate },
  data() {
    return {
      waitResponse: false,
      username: '',
      password: '',
      rememberPass: false,
      name: null,
      appInProduction: process.env.NODE_ENV
    }
  },

  mounted() {
    this.name = ConfigHelper.ConfStr('name');
  },
  props: ['version'],
  methods: {
    async sendLogin() {
      this.waitResponse = true;
      Loader.fullPage()
      let fd = new FormData();
      fd.append('username', this.username);
      fd.append('password', this.password);
      let request = await this.$store.dispatch("main/sendLogin", fd);
      console.log(request);
      if (request.success) {
        if (this.rememberPass) {
          fs.writeFile('authorization.json', JSON.stringify(request.data.token), (err) => {
            if (err) {
              this.$awn.alert(err);
              return;
            }
            console.log('Authorization token set');
          });
        }
        this.$router.push('/inicio');
        this.$awn.success('Logueo Exitoso', { labels: { success: 'CORRECTO' } });
      } else {
        this.waitResponse = false;
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
      Loader.hide();
    }
  }
}
</script>

<style scoped>
.login-bg {
  background-image: url('../../assets/login-bg.jpg');
  background-size: cover;
  background-repeat: no-repeat;
  width: 100vw;
  height: 100vh;
}

.login {
  width: 320px;
  background-color: rgba(230, 230, 230, 1);
  border-right: 30px solid #0B4F6C;
  box-shadow: 0 14px 28px rgba(0, 0, 0, 0.8), 0 10px 10px rgba(0, 0, 0, 0.8) !important;
}

.logo {
  color: #6CCFF6;
  font-size: 42px;
}

.logo-sub {
  color: #0B4F6C;
}

.logo::after {
  display: block;
  content: '';
  padding-top: 5px;
  transform: scaleX(0.75);
  border-bottom: 2px solid #6CCFF6;
}

.logo-text {
  color: lightslategray;
  font-size: 24px;
}

.btn-acceder {
  background-color: #0B4F6C;
  color: #ffffff;
}

.btn-acceder:hover {
  background-color: #292F36;
  color: #fff;
}

.text-fontlogo {
  font-size: 30px;
}

.gray-text-version {
  color: rgba(180, 180, 180, 1);
}</style>
