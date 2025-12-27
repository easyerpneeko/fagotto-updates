<?php
/**
 * QR Check - Marcado de Asistencia con Reconocimiento Facial
 * 
 * URL: https://fagotto.cl/qrcheck.php?session=ABC123
 * 
 * Flujo:
 * 1. Usuario escanea QR desde PC de entrada
 * 2. Abre esta página en su teléfono
 * 3. Selecciona su nombre
 * 4. Valida GPS
 * 5. Captura foto con cámara del teléfono
 * 6. Envía a AWS Rekognition para comparar
 * 7. Registra entrada en BD
 */

// Configuración
require_once 'config.php';
require_once 'helpers/AWSRekognition.php';

// Obtener session ID
$sessionId = $_GET['session'] ?? null;

if (!$sessionId) {
    die('Error: Session ID requerido');
}

// Validar sesión
try {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT * FROM asistencias_sessions 
        WHERE session_id = ? 
        AND expires_at > NOW()
        AND used = 0
    ");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch();

    if (!$session) {
        // Debug: Mostrar información útil
        $debugInfo = "Session ID: $sessionId<br>";
        $debugInfo .= "Hora actual servidor: " . date('Y-m-d H:i:s') . "<br>";
        
        // Verificar si la sesión existe pero expiró
        $stmt2 = $pdo->prepare("SELECT session_id, expires_at, used FROM asistencias_sessions WHERE session_id = ?");
        $stmt2->execute([$sessionId]);
        $expiredSession = $stmt2->fetch();
        
        if ($expiredSession) {
            $debugInfo .= "Sesión encontrada pero:<br>";
            $debugInfo .= "- Expira: " . $expiredSession['expires_at'] . "<br>";
            $debugInfo .= "- Usada: " . ($expiredSession['used'] ? 'Sí' : 'No') . "<br>";
        } else {
            $debugInfo .= "La sesión no existe en la base de datos.<br>";
        }
        
        die('Error: Sesión inválida o expirada. Escanea el QR nuevamente.<br><br>' . $debugInfo);
    }
} catch (Exception $e) {
    die('Error de conexión: ' . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Marcar Asistencia - Fagotto</title>
    <link rel="stylesheet" href="assets/css/mobile-checkin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div id="app" class="mobile-app">
        <!-- Paso 0: Seleccionar Tipo de Marcación -->
        <div v-if="paso === 'tipo'" class="step-container">
            <div class="header">
                <i class="fas fa-clock"></i>
                <h1>¿Qué quieres marcar?</h1>
                <p>Selecciona el tipo de marcación</p>
            </div>

            <div class="tipo-list">
                <div @click="seleccionarTipo('entrada')" class="tipo-item entrada">
                    <i class="fas fa-sign-in-alt"></i>
                    <h3>🟢 Entrada</h3>
                    <p>Llegada al trabajo</p>
                </div>
                
                <div @click="seleccionarTipo('salida_colacion')" class="tipo-item salida">
                    <i class="fas fa-utensils"></i>
                    <h3>🍽️ Salida a Colación</h3>
                    <p>Ir a almorzar</p>
                </div>
                
                <div @click="seleccionarTipo('regreso_colacion')" class="tipo-item entrada">
                    <i class="fas fa-undo"></i>
                    <h3>🟢 Regreso de Colación</h3>
                    <p>Volver de almorzar</p>
                </div>
                
                <div @click="seleccionarTipo('salida')" class="tipo-item salida">
                    <i class="fas fa-sign-out-alt"></i>
                    <h3>🔴 Salida Final</h3>
                    <p>Término de jornada</p>
                </div>
            </div>
        </div>

        <!-- Paso 1: Seleccionar Empleado -->
        <div v-if="paso === 'seleccionar'" class="step-container">
            <div class="header">
                <i class="fas fa-user-check"></i>
                <h1>¿Quién eres?</h1>
                <p>Selecciona tu nombre</p>
            </div>

            <div class="empleados-list">
                <div v-for="empleado in empleados" 
                     :key="empleado.id"
                     @click="seleccionarEmpleado(empleado)"
                     class="empleado-item">
                    <div class="avatar">
                        <img v-if="empleado.foto" :src="empleado.foto" />
                        <i v-else class="fas fa-user"></i>
                    </div>
                    <div class="info">
                        <h3>{{ empleado.nombre }}</h3>
                        <p>{{ empleado.cargo }}</p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>

        <!-- Paso 2: Validar GPS -->
        <div v-if="paso === 'gps'" class="step-container">
            <i class="fas fa-map-marker-alt icon-lg pulse"></i>
            <h2>Validando ubicación...</h2>
            <p>{{ mensajeGPS }}</p>
        </div>

        <!-- Paso 3: Capturar Foto -->
        <div v-if="paso === 'foto'" class="step-container">
            <div class="header">
                <i class="fas fa-camera"></i>
                <h1>Hola {{ empleadoSeleccionado.nombre }}</h1>
                <p>Posiciona tu rostro en el óvalo</p>
            </div>

            <div class="video-container">
                <video ref="video" autoplay playsinline></video>
                <canvas ref="canvas" style="display:none"></canvas>
                
                <!-- Guía facial -->
                <div class="face-guide">
                    <div class="oval"></div>
                </div>

                <!-- Estado de detección -->
                <div v-if="detectando" class="status">
                    <i class="fas fa-spinner fa-spin"></i>
                    Detectando rostro...
                </div>
                <div v-if="rostroOK" class="status success">
                    <i class="fas fa-check-circle"></i>
                    ¡Rostro detectado!
                </div>
            </div>

            <div class="checks">
                <div class="check" :class="{ active: iluminacionOK }">
                    <i class="fas fa-lightbulb"></i>
                    Buena luz
                </div>
                <div class="check" :class="{ active: distanciaOK }">
                    <i class="fas fa-expand-arrows-alt"></i>
                    Distancia OK
                </div>
                <div class="check" :class="{ active: rostroOK }">
                    <i class="fas fa-smile"></i>
                    Rostro OK
                </div>
            </div>

            <button v-if="puedeCapturar" @click="capturar" class="btn-capturar">
                <i class="fas fa-camera"></i>
                Capturar y Validar
            </button>
        </div>

        <!-- Paso 4: Validando con AWS Rekognition -->
        <div v-if="paso === 'validando'" class="step-container">
            <div class="spinner"></div>
            <h2>Validando identidad...</h2>
            <p>Comparando con AWS Rekognition</p>
            <div class="progress-bar">
                <div class="progress" :style="{ width: progreso + '%' }"></div>
            </div>
            <p class="progress-text">{{ progreso }}%</p>
        </div>

        <!-- Paso 5: Resultado Exitoso -->
        <div v-if="paso === 'exito'" class="step-container">
            <div class="resultado-box success">
                <i class="fas fa-check-circle"></i>
                <h2>¡Entrada Registrada!</h2>
                <img :src="fotoCapturada" class="foto-resultado" />
                <div class="datos">
                    <div class="dato">
                        <i class="fas fa-user"></i>
                        {{ empleadoSeleccionado.nombre }}
                    </div>
                    <div class="dato">
                        <i class="fas fa-clock"></i>
                        {{ horaRegistro }}
                    </div>
                    <div class="dato">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ ubicacion }}
                    </div>
                    <div class="dato">
                        <i class="fas fa-percentage"></i>
                        Coincidencia: {{ coincidencia }}%
                    </div>
                </div>
                <p class="mensaje">Puedes cerrar esta ventana</p>
            </div>
        </div>

        <!-- Paso 6: Error -->
        <div v-if="paso === 'error'" class="step-container">
            <div class="resultado-box error">
                <i class="fas fa-times-circle"></i>
                <h2>Error</h2>
                <p>{{ mensajeError }}</p>
                <button @click="reintentar" class="btn-retry">
                    <i class="fas fa-redo"></i>
                    Reintentar
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        new Vue({
            el: '#app',
            data: {
                sessionId: '<?= $sessionId ?>',
                paso: 'tipo',
                tipoMarcacion: '',
                empleados: [],
                empleadoSeleccionado: null,
                
                // GPS
                mensajeGPS: 'Obteniendo ubicación...',
                ubicacion: '',
                
                // Foto
                detectando: false,
                iluminacionOK: false,
                distanciaOK: false,
                rostroOK: false,
                puedeCapturar: false,
                fotoCapturada: null,
                videoStream: null,
                
                // Validación
                progreso: 0,
                coincidencia: 0,
                
                // Resultado
                horaRegistro: '',
                mensajeError: ''
            },
            
            async mounted() {
                await this.cargarEmpleados();
            },
            
            methods: {
                async cargarEmpleados() {
                    try {
                        const response = await axios.get('api/empleados.php');
                        this.empleados = response.data;
                    } catch (error) {
                        console.error('Error cargando empleados:', error);
                        this.mensajeError = 'Error cargando empleados';
                        this.paso = 'error';
                    }
                },
                
                seleccionarTipo(tipo) {
                    this.tipoMarcacion = tipo;
                    this.paso = 'seleccionar';
                },
                
                async seleccionarEmpleado(empleado) {
                    this.empleadoSeleccionado = empleado;
                    this.paso = 'gps';
                    await this.validarGPS();
                },
                
                async validarGPS() {
                    if (!navigator.geolocation) {
                        this.mensajeError = 'GPS no disponible';
                        this.paso = 'error';
                        return;
                    }
                    
                    try {
                        const position = await new Promise((resolve, reject) => {
                            navigator.geolocation.getCurrentPosition(resolve, reject, {
                                enableHighAccuracy: true,
                                timeout: 10000
                            });
                        });
                        
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        this.ubicacion = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                        
                        // Validar distancia en el servidor
                        const response = await axios.post('api/validar-gps.php', {
                            sessionId: this.sessionId,
                            lat: lat,
                            lng: lng
                        });
                        
                        if (response.data.valid) {
                            this.mensajeGPS = '✅ Ubicación validada';
                            setTimeout(() => {
                                this.paso = 'foto';
                                this.iniciarCamara();
                            }, 1000);
                        } else {
                            this.mensajeError = `Estás muy lejos del local (${response.data.distance}m)`;
                            this.paso = 'error';
                        }
                    } catch (error) {
                        console.error('Error GPS:', error);
                        this.mensajeError = 'No se pudo obtener tu ubicación';
                        this.paso = 'error';
                    }
                },
                
                async iniciarCamara() {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({
                            video: { facingMode: 'user', width: 1280, height: 720 }
                        });
                        
                        this.videoStream = stream;
                        this.$refs.video.srcObject = stream;
                        
                        // Simular detección de rostro
                        setTimeout(() => { this.detectando = true; }, 500);
                        setTimeout(() => { this.iluminacionOK = true; }, 1500);
                        setTimeout(() => { this.distanciaOK = true; }, 2500);
                        setTimeout(() => { 
                            this.rostroOK = true; 
                            this.detectando = false;
                            this.puedeCapturar = true;
                        }, 3500);
                        
                    } catch (error) {
                        console.error('Error cámara:', error);
                        this.mensajeError = 'No se pudo acceder a la cámara';
                        this.paso = 'error';
                    }
                },
                
                async capturar() {
                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;
                    const ctx = canvas.getContext('2d');
                    
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    ctx.drawImage(video, 0, 0);
                    
                    this.fotoCapturada = canvas.toDataURL('image/jpeg', 0.9);
                    
                    // Detener cámara
                    if (this.videoStream) {
                        this.videoStream.getTracks().forEach(track => track.stop());
                    }
                    
                    this.paso = 'validando';
                    await this.validarConAWS();
                },
                
                async validarConAWS() {
                    // Simular progreso
                    const interval = setInterval(() => {
                        this.progreso += 10;
                        if (this.progreso >= 100) clearInterval(interval);
                    }, 200);
                    
                    try {
                        // Enviar a backend PHP que llama a AWS Rekognition
                        const response = await axios.post('api/reconocimiento-facial.php', {
                            sessionId: this.sessionId,
                            employeeId: this.empleadoSeleccionado.id,
                            foto: this.fotoCapturada,
                            ubicacion: this.ubicacion,
                            tipoMarcacion: this.tipoMarcacion
                        });
                        
                        if (response.data.success) {
                            this.coincidencia = response.data.similarity;
                            this.horaRegistro = response.data.timestamp;
                            this.paso = 'exito';
                        } else {
                            this.mensajeError = response.data.message;
                            this.paso = 'error';
                        }
                    } catch (error) {
                        console.error('Error validación:', error);
                        this.mensajeError = 'Error al validar identidad';
                        this.paso = 'error';
                    }
                },
                
                reintentar() {
                    this.paso = 'seleccionar';
                    this.empleadoSeleccionado = null;
                    this.fotoCapturada = null;
                    this.progreso = 0;
                }
            }
        });
    </script>
</body>
</html>
