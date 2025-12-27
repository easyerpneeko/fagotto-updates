<template>
  <div class="admin-carnets-container">
    <div class="header">
      <h1><i class="fas fa-id-card-alt"></i> Generador de Carnets</h1>
      <p>Crea y administra los carnets con QR personal de cada empleado</p>
    </div>

    <!-- Lista de empleados -->
    <div class="empleados-grid">
      <div class="empleado-card" v-for="empleado in empleados" :key="empleado.id">
        <div class="card-header">
          <img :src="empleado.foto || 'https://via.placeholder.com/150'" class="empleado-foto" />
          <div class="empleado-info">
            <h3>{{ empleado.nombre }}</h3>
            <p>{{ empleado.cargo }}</p>
            <p class="rut">RUT: {{ empleado.rut }}</p>
          </div>
        </div>

        <div class="qr-section">
          <div class="qr-container">
            <canvas :ref="`qr-${empleado.id}`" class="qr-canvas"></canvas>
          </div>
          <p class="qr-info">QR Personal • ID: {{ empleado.id }}</p>
        </div>

        <div class="card-actions">
          <button @click="descargarCarnet(empleado)" class="btn-descargar">
            <i class="fas fa-download"></i>
            Descargar Carnet
          </button>
          <button @click="imprimirCarnet(empleado)" class="btn-imprimir">
            <i class="fas fa-print"></i>
            Imprimir
          </button>
        </div>
      </div>
    </div>

    <!-- Carnet virtual para descarga -->
    <div ref="carnetTemplate" class="carnet-template">
      <div class="carnet-content">
        <div class="carnet-header">
          <div class="company-logo">
            <i class="fas fa-store"></i>
            <span>FAGOTTO</span>
          </div>
        </div>

        <div class="carnet-body">
          <div class="empleado-foto-carnet">
            <img :src="empleadoActual.foto || 'https://via.placeholder.com/150'" />
          </div>

          <div class="empleado-datos">
            <h2>{{ empleadoActual.nombre }}</h2>
            <p class="cargo">{{ empleadoActual.cargo }}</p>
            <p class="rut">{{ empleadoActual.rut }}</p>
          </div>

          <div class="qr-carnet">
            <canvas ref="qrCarnetDescarga" class="qr-canvas-grande"></canvas>
            <p>ID: {{ empleadoActual.id }}</p>
          </div>
        </div>

        <div class="carnet-footer">
          <p>CARNET DE IDENTIFICACIÓN</p>
          <p>Válido para control de asistencia</p>
        </div>
      </div>
    </div>

    <!-- Modal para agregar nuevo empleado -->
    <button @click="abrirModalNuevo" class="btn-nuevo-empleado">
      <i class="fas fa-user-plus"></i>
      Nuevo Empleado
    </button>

    <div v-if="mostrarModal" class="modal-overlay" @click="cerrarModal">
      <div class="modal-content" @click.stop>
        <h2><i class="fas fa-user-plus"></i> Nuevo Empleado</h2>
        
        <div class="form-group">
          <label>Nombre Completo</label>
          <input v-model="nuevoEmpleado.nombre" type="text" placeholder="Juan Pérez" />
        </div>

        <div class="form-group">
          <label>RUT</label>
          <input v-model="nuevoEmpleado.rut" type="text" placeholder="12345678-9" />
        </div>

        <div class="form-group">
          <label>Cargo</label>
          <input v-model="nuevoEmpleado.cargo" type="text" placeholder="Cajero" />
        </div>

        <div class="form-group">
          <label>Foto de Perfil</label>
          <div class="foto-upload">
            <button @click="capturarFoto" class="btn-capturar">
              <i class="fas fa-camera"></i>
              Capturar Foto
            </button>
            <input type="file" @change="subirFoto" accept="image/*" />
          </div>
          <img v-if="nuevoEmpleado.foto" :src="nuevoEmpleado.foto" class="foto-preview" />
        </div>

        <div class="modal-actions">
          <button @click="guardarEmpleado" class="btn-guardar">
            <i class="fas fa-save"></i>
            Guardar
          </button>
          <button @click="cerrarModal" class="btn-cancelar">
            Cancelar
          </button>
        </div>
      </div>
    </div>

    <!-- Webcam para captura -->
    <div v-if="capturandoFoto" class="webcam-overlay">
      <div class="webcam-container">
        <video ref="webcam" autoplay playsinline></video>
        <canvas ref="canvasFoto" style="display: none;"></canvas>
        <div class="webcam-actions">
          <button @click="tomarFoto" class="btn-tomar">
            <i class="fas fa-camera"></i>
            Tomar Foto
          </button>
          <button @click="cancelarFoto" class="btn-cancelar">
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import QRCode from 'qrcode';
import html2canvas from 'html2canvas';

export default {
  name: 'AdminCarnets',
  data() {
    return {
      empleados: [],
      empleadoActual: {
        id: null,
        nombre: '',
        cargo: '',
        rut: '',
        foto: ''
      },
      mostrarModal: false,
      nuevoEmpleado: {
        nombre: '',
        rut: '',
        cargo: '',
        foto: ''
      },
      capturandoFoto: false,
      streamWebcam: null
    }
  },
  
  mounted() {
    this.cargarEmpleados();
  },
  
  beforeDestroy() {
    if (this.streamWebcam) {
      this.streamWebcam.getTracks().forEach(track => track.stop());
    }
  },
  
  methods: {
    async cargarEmpleados() {
      // TODO: Cargar desde base de datos
      this.empleados = [
        {
          id: 'EMP001',
          nombre: 'Juan Pérez',
          cargo: 'Cajero',
          rut: '12345678-9',
          foto: 'https://via.placeholder.com/150'
        },
        {
          id: 'EMP002',
          nombre: 'María González',
          cargo: 'Supervisor',
          rut: '98765432-1',
          foto: 'https://via.placeholder.com/150'
        },
        {
          id: 'EMP003',
          nombre: 'Pedro Sánchez',
          cargo: 'Bodeguero',
          rut: '11223344-5',
          foto: 'https://via.placeholder.com/150'
        }
      ];
      
      // Generar QRs
      this.$nextTick(() => {
        this.empleados.forEach(empleado => {
          this.generarQREmpleado(empleado);
        });
      });
    },
    
    generarQREmpleado(empleado) {
      const qrData = JSON.stringify({
        type: 'employee',
        employee_id: empleado.id,
        nombre: empleado.nombre,
        rut: empleado.rut,
        token: btoa(`${empleado.id}_${empleado.rut}`)
      });
      
      const refKey = `qr-${empleado.id}`;
      const canvas = this.$refs[refKey];
      
      if (canvas && canvas[0]) {
        QRCode.toCanvas(
          canvas[0],
          qrData,
          {
            width: 150,
            margin: 1,
            color: {
              dark: '#2c3e50',
              light: '#ffffff'
            }
          },
          (error) => {
            if (error) console.error('Error generando QR:', error);
          }
        );
      }
    },
    
    async descargarCarnet(empleado) {
      this.empleadoActual = { ...empleado };
      
      await this.$nextTick();
      
      // Generar QR grande para el carnet
      const qrData = JSON.stringify({
        type: 'employee',
        employee_id: empleado.id,
        nombre: empleado.nombre,
        rut: empleado.rut,
        token: btoa(`${empleado.id}_${empleado.rut}`)
      });
      
      if (this.$refs.qrCarnetDescarga) {
        await QRCode.toCanvas(
          this.$refs.qrCarnetDescarga,
          qrData,
          {
            width: 200,
            margin: 2
          }
        );
      }
      
      await this.$nextTick();
      
      // Capturar como imagen
      const carnetElement = this.$refs.carnetTemplate;
      
      try {
        const canvas = await html2canvas(carnetElement, {
          backgroundColor: '#ffffff',
          scale: 2
        });
        
        const link = document.createElement('a');
        link.download = `carnet_${empleado.id}_${empleado.nombre.replace(/\s/g, '_')}.png`;
        link.href = canvas.toDataURL();
        link.click();
        
        this.$awn.success('Carnet descargado exitosamente');
      } catch (error) {
        console.error('Error generando carnet:', error);
        this.$awn.alert('Error al generar el carnet');
      }
    },
    
    imprimirCarnet(empleado) {
      this.$awn.info('Función de impresión en desarrollo');
      // TODO: Implementar impresión directa
    },
    
    abrirModalNuevo() {
      this.mostrarModal = true;
      this.nuevoEmpleado = {
        nombre: '',
        rut: '',
        cargo: '',
        foto: ''
      };
    },
    
    cerrarModal() {
      this.mostrarModal = false;
    },
    
    async capturarFoto() {
      try {
        this.capturandoFoto = true;
        
        const stream = await navigator.mediaDevices.getUserMedia({ 
          video: { facingMode: 'user' } 
        });
        
        this.streamWebcam = stream;
        
        await this.$nextTick();
        
        if (this.$refs.webcam) {
          this.$refs.webcam.srcObject = stream;
        }
      } catch (error) {
        console.error('Error accediendo a la cámara:', error);
        this.$awn.alert('No se pudo acceder a la cámara');
        this.capturandoFoto = false;
      }
    },
    
    tomarFoto() {
      const video = this.$refs.webcam;
      const canvas = this.$refs.canvasFoto;
      const context = canvas.getContext('2d');
      
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      
      context.drawImage(video, 0, 0);
      
      this.nuevoEmpleado.foto = canvas.toDataURL('image/jpeg', 0.8);
      
      // Detener stream
      if (this.streamWebcam) {
        this.streamWebcam.getTracks().forEach(track => track.stop());
      }
      
      this.capturandoFoto = false;
    },
    
    cancelarFoto() {
      if (this.streamWebcam) {
        this.streamWebcam.getTracks().forEach(track => track.stop());
      }
      this.capturandoFoto = false;
    },
    
    subirFoto(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.nuevoEmpleado.foto = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    
    async guardarEmpleado() {
      if (!this.nuevoEmpleado.nombre || !this.nuevoEmpleado.rut) {
        this.$awn.alert('Completa todos los campos obligatorios');
        return;
      }
      
      const nuevoId = `EMP${String(this.empleados.length + 1).padStart(3, '0')}`;
      
      const empleado = {
        id: nuevoId,
        ...this.nuevoEmpleado
      };
      
      // TODO: Guardar en base de datos
      console.log('Guardando empleado:', empleado);
      
      this.empleados.push(empleado);
      
      await this.$nextTick();
      this.generarQREmpleado(empleado);
      
      this.$awn.success('Empleado agregado exitosamente');
      this.cerrarModal();
    }
  }
}
</script>

<style scoped>
.admin-carnets-container {
  padding: 2rem;
  min-height: 100vh;
  background: #f5f7fa;
}

.header {
  text-align: center;
  margin-bottom: 3rem;
}

.header h1 {
  font-size: 2.5rem;
  color: #2c3e50;
  margin-bottom: 0.5rem;
}

.header p {
  color: #7f8c8d;
  font-size: 1.1rem;
}

/* Grid de empleados */
.empleados-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.empleado-card {
  background: white;
  border-radius: 15px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.3s;
}

.empleado-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.card-header {
  display: flex;
  gap: 1.5rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.empleado-foto {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid white;
}

.empleado-info h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.3rem;
}

.empleado-info p {
  margin: 0.25rem 0;
  opacity: 0.9;
}

.empleado-info .rut {
  font-size: 0.9rem;
  opacity: 0.8;
}

.qr-section {
  padding: 1.5rem;
  text-align: center;
  background: #f8f9fa;
}

.qr-container {
  display: inline-block;
  padding: 1rem;
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.qr-canvas {
  display: block;
}

.qr-info {
  margin-top: 1rem;
  color: #7f8c8d;
  font-size: 0.9rem;
  font-weight: 600;
}

.card-actions {
  display: flex;
  gap: 0.5rem;
  padding: 1rem;
  border-top: 1px solid #ecf0f1;
}

.btn-descargar,
.btn-imprimir {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-descargar {
  background: #667eea;
  color: white;
}

.btn-descargar:hover {
  background: #5568d3;
}

.btn-imprimir {
  background: #f8f9fa;
  color: #2c3e50;
  border: 2px solid #ecf0f1;
}

.btn-imprimir:hover {
  background: #ecf0f1;
}

/* Carnet template (oculto, solo para generar imagen) */
.carnet-template {
  position: absolute;
  left: -9999px;
  width: 400px;
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.carnet-content {
  padding: 2rem;
}

.carnet-header {
  text-align: center;
  margin-bottom: 2rem;
}

.company-logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  font-size: 2rem;
  font-weight: 800;
  color: #667eea;
}

.carnet-body {
  text-align: center;
}

.empleado-foto-carnet img {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  object-fit: cover;
  border: 5px solid #667eea;
  margin-bottom: 1rem;
}

.empleado-datos h2 {
  margin: 0.5rem 0;
  color: #2c3e50;
  font-size: 1.5rem;
}

.empleado-datos .cargo {
  color: #667eea;
  font-weight: 600;
  margin: 0.25rem 0;
}

.empleado-datos .rut {
  color: #7f8c8d;
  font-size: 0.9rem;
}

.qr-carnet {
  margin-top: 1.5rem;
}

.qr-canvas-grande {
  display: block;
  margin: 0 auto;
}

.qr-carnet p {
  margin-top: 0.5rem;
  font-size: 0.85rem;
  color: #7f8c8d;
}

.carnet-footer {
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 2px solid #ecf0f1;
  text-align: center;
}

.carnet-footer p {
  margin: 0.25rem 0;
  font-size: 0.8rem;
  color: #95a5a6;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Botón nuevo empleado */
.btn-nuevo-empleado {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  padding: 1rem 2rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 50px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
  transition: all 0.3s;
  z-index: 100;
}

.btn-nuevo-empleado:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 25px rgba(16, 185, 129, 0.5);
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 2rem;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 20px;
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content h2 {
  margin-top: 0;
  color: #2c3e50;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #2c3e50;
  font-weight: 600;
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #ecf0f1;
  border-radius: 8px;
  font-size: 1rem;
}

.foto-upload {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.btn-capturar {
  padding: 0.75rem 1.5rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.foto-preview {
  width: 150px;
  height: 150px;
  border-radius: 10px;
  object-fit: cover;
  margin-top: 1rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-guardar {
  flex: 1;
  padding: 1rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.btn-cancelar {
  flex: 1;
  padding: 1rem;
  background: #f8f9fa;
  border: 2px solid #ecf0f1;
  border-radius: 8px;
  cursor: pointer;
}

/* Webcam overlay */
.webcam-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.webcam-container {
  text-align: center;
}

.webcam-container video {
  max-width: 640px;
  border-radius: 15px;
}

.webcam-actions {
  margin-top: 2rem;
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn-tomar {
  padding: 1rem 2rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1.1rem;
  cursor: pointer;
}
</style>
