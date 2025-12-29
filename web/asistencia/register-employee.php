<?php
/**
 * Registro de Empleado - Captura facial y registro en AWS
 */

// Headers para evitar caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

require_once 'config.php';

$sessionId = $_GET['session'] ?? null;
$negocioParam = $_GET['negocio'] ?? null;

if (!$sessionId || !$negocioParam) {
    die('Error: Parámetros inválidos (session y negocio requeridos)');
}

// Convertir el slug del negocio a nombre normal (ej: FAGOTTO_MANUEL_MONT → Fagotto Manuel Mont)
$negocioNombre = str_replace('_', ' ', $negocioParam);

// Validar sesión de registro
try {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT * FROM asistencias_sessions 
        WHERE session_id = ? 
        AND negocio_nombre = ?
        AND expires_at > NOW()
        AND used = 0
    ");
    $stmt->execute([$sessionId, $negocioNombre]);
    $session = $stmt->fetch();
    
    if (!$session) {
        // Debug: Mostrar información útil
        $debugInfo = "<br><br>Session ID: $sessionId<br>";
        $debugInfo .= "Negocio: $negocioNombre<br>";
        $debugInfo .= "Hora actual servidor: " . date('Y-m-d H:i:s') . "<br>";
        
        // Verificar si la sesión existe
        $stmt2 = $pdo->prepare("SELECT session_id, negocio_nombre, expires_at, used FROM asistencias_sessions WHERE session_id = ?");
        $stmt2->execute([$sessionId]);
        $expiredSession = $stmt2->fetch();
        
        if ($expiredSession) {
            $debugInfo .= "<br>Sesión encontrada pero:<br>";
            $debugInfo .= "- Negocio en DB: " . $expiredSession['negocio_nombre'] . "<br>";
            $debugInfo .= "- Expira: " . $expiredSession['expires_at'] . "<br>";
            $debugInfo .= "- Usada: " . ($expiredSession['used'] ? 'Sí' : 'No') . "<br>";
        } else {
            $debugInfo .= "<br>⚠️ La sesión no existe en la base de datos.<br>";
            $debugInfo .= "Verifica que la aplicación esté conectada a internet y que el servidor API esté funcionando.";
        }
        
        die('<h2>Error: Sesión inválida o expirada</h2>' . $debugInfo);
    }
    
    // El nombre del negocio viene directamente de la sesión
    $localNombre = $session['negocio_nombre'];
    
} catch (Exception $e) {
    die('Error de conexión: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Registrar Empleado - Fagotto</title>
    <link rel="stylesheet" href="assets/css/mobile-checkin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-registro {
            padding: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #28a745;
        }
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            background: white;
            cursor: pointer;
        }
        .form-group select:focus {
            outline: none;
            border-color: #28a745;
        }
        .btn-continuar {
            width: 100%;
            background: #28a745;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-continuar:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        .btn-continuar:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        /* Estilos para términos y firma */
        .firma-info {
            padding: 2rem;
        }
        .btn-ver-terminos {
            width: 100%;
            background: #007bff;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1rem;
            margin-bottom: 2rem;
            cursor: pointer;
        }
        .canvas-container {
            text-align: center;
            margin: 2rem 0;
        }
        .canvas-firma {
            border: 3px dashed #ddd;
            border-radius: 10px;
            width: 100%;
            max-width: 500px;
            height: 200px;
            background: white;
            touch-action: none;
            cursor: crosshair;
        }
        .canvas-firma.firma-vacia {
            border-color: #ffc107;
        }
        .btn-limpiar-firma {
            margin-top: 1rem;
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
        }
        .btn-confirmar-firma {
            width: 100%;
            background: #28a745;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
        }
        .btn-confirmar-firma:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 1rem;
        }
        .modal-terminos {
            background: white;
            border-radius: 15px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 2rem;
            position: relative;
        }
        .btn-cerrar-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #dc3545;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
        }
        .terminos-contenido {
            margin: 1rem 0;
        }
        .terminos-contenido h3 {
            color: #28a745;
            margin-top: 1.5rem;
        }
        .terminos-contenido ul {
            margin-left: 1.5rem;
        }
        .btn-aceptar-modal {
            width: 100%;
            background: #28a745;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div id="app" class="mobile-app">
        <!-- Paso 1: Datos del Empleado -->
        <div v-if="paso === 'datos'" class="step-container">
            <div class="header">
                <i class="fas fa-user-plus" style="font-size: 3rem; color: #28a745;"></i>
                <h1>Registrar Nuevo Empleado</h1>
                <p>{{ localNombre }}</p>
            </div>

            <div class="form-registro">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nombre Completo</label>
                    <input v-model="empleado.nombre" type="text" placeholder="Juan Pérez" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> RUT</label>
                    <input v-model="empleado.rut" type="text" placeholder="12345678-9" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-briefcase"></i> Cargo</label>
                    <select v-model="empleado.cargo" required>
                        <option value="">Seleccionar cargo...</option>
                        <option value="Operador">Operador</option>
                        <option value="Cajero">Cajero</option>
                        <option value="Encargado de local">Encargado de local</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Correo Electrónico</label>
                    <input v-model="empleado.email" type="email" placeholder="correo@ejemplo.com" required>
                </div>

                <button 
                    @click="continuarAFoto" 
                    class="btn-continuar"
                    :disabled="!formularioValido">
                    <i class="fas fa-camera"></i>
                    Continuar a Foto
                </button>
            </div>
        </div>

        <!-- Paso 2: Captura Foto -->
        <div v-if="paso === 'foto'" class="step-container">
            <div class="header">
                <i class="fas fa-camera"></i>
                <h1>Hola {{ empleado.nombre }}</h1>
                <p>Captura tu rostro para el registro</p>
            </div>

            <div class="video-container">
                <video ref="video" autoplay playsinline></video>
                <canvas ref="canvas" style="display:none"></canvas>
                
                <div class="face-guide">
                    <div class="oval"></div>
                </div>

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
                Capturar y Continuar
            </button>
        </div>

        <!-- Paso 3: Términos y Firma -->
        <div v-if="paso === 'firma'" class="step-container">
            <div class="header">
                <i class="fas fa-file-signature"></i>
                <h1>Términos y Condiciones</h1>
                <p>Lee y firma para completar el registro</p>
            </div>

            <div class="firma-info">
                <button @click="mostrarTerminos" class="btn-ver-terminos">
                    <i class="fas fa-file-alt"></i>
                    Ver Términos Completos
                </button>

                <div class="canvas-container">
                    <p><strong>Firma Aquí:</strong></p>
                    <canvas 
                        ref="canvasFirma" 
                        class="canvas-firma"
                        :class="{ 'firma-vacia': firmaVacia }"
                        @touchstart="iniciarFirma"
                        @touchmove="dibujarFirma"
                        @touchend="finalizarFirma"
                        @mousedown="iniciarFirma"
                        @mousemove="dibujarFirma"
                        @mouseup="finalizarFirma"
                        @mouseleave="finalizarFirma">
                    </canvas>
                    <button @click="limpiarFirma" class="btn-limpiar-firma">
                        <i class="fas fa-eraser"></i>
                        Limpiar Firma
                    </button>
                </div>

                <button 
                    @click="confirmarYRegistrar" 
                    class="btn-confirmar-firma"
                    :disabled="firmaVacia">
                    <i class="fas fa-check"></i>
                    Confirmar y Registrar
                </button>
            </div>
        </div>

        <!-- Modal Términos -->
        <div v-if="mostrandoTerminos" class="modal-overlay" @click="cerrarTerminos">
            <div class="modal-terminos" @click.stop>
                <button class="btn-cerrar-modal" @click="cerrarTerminos">
                    <i class="fas fa-times"></i>
                </button>
                <h2>Términos y Condiciones de Uso</h2>
                <div class="terminos-contenido">
                    <h3>SISTEMA ELECTRÓNICO DE CONTROL DE ASISTENCIA</h3>
                    <p><strong>RECONOCIMIENTO FACIAL Y GEOLOCALIZACIÓN</strong></p>

                    <h3>I. IDENTIFICACIÓN DEL EMPLEADOR</h3>
                    <p><strong>Razón Social:</strong> Fagotto<br>
                    <strong>RUT:</strong> 77.742.774-1<br>
                    <strong>Domicilio:</strong> Avenida Las Condes N° 7253, comuna de Las Condes, Región Metropolitana, Chile.</p>

                    <h3>II. OBJETO DEL SISTEMA</h3>
                    <p>El presente documento regula el uso del Sistema Electrónico de Control de Asistencia implementado por Fagotto, cuyo único y exclusivo objetivo es el registro de la jornada laboral, horas ordinarias y extraordinarias de los trabajadores, en conformidad con lo dispuesto en el artículo 33 del Código del Trabajo, la Ley N°19.628 sobre Protección de la Vida Privada, y los dictámenes e instrucciones de la Dirección del Trabajo.</p>
                    <p><strong>Este sistema no constituye un mecanismo de vigilancia, supervisión permanente, control conductual ni herramienta disciplinaria.</strong></p>

                    <h3>III. DATOS PERSONALES TRATADOS</h3>
                    <p>El sistema podrá tratar exclusivamente los siguientes datos:</p>
                    <ul>
                        <li>Identificación del trabajador/a</li>
                        <li>Fecha y hora de marcaje de entrada y salida</li>
                        <li>Patrón biométrico facial (dato matemático cifrado)</li>
                        <li>Ubicación geográfica únicamente al momento del marcaje</li>
                    </ul>
                    <p><strong>⚠️ No se realiza monitoreo continuo, seguimiento en tiempo real ni trazabilidad de desplazamientos.</strong></p>

                    <h3>IV. RECONOCIMIENTO FACIAL</h3>
                    <p>El reconocimiento facial se utiliza exclusivamente como mecanismo de validación de identidad al momento de registrar la asistencia.</p>
                    <ul>
                        <li>No se almacenan fotografías ni imágenes faciales utilizables.</li>
                        <li>El dato biométrico se transforma en un patrón matemático cifrado, irreversible e irreproducible.</li>
                        <li>Dicho patrón no puede ser reconstruido, reutilizado ni destinado a fines distintos al control de asistencia.</li>
                    </ul>

                    <h3>V. GEOLOCALIZACIÓN</h3>
                    <p>La geolocalización:</p>
                    <ul>
                        <li>Se activa única y exclusivamente al momento de efectuar el marcaje de entrada o salida.</li>
                        <li>Permite verificar el lugar de prestación de servicios.</li>
                        <li>No permanece activa fuera del proceso de marcaje.</li>
                        <li>No genera seguimiento, monitoreo ni historial de desplazamientos del trabajador.</li>
                    </ul>

                    <h3>VI. INTEGRIDAD, INVIOLABILIDAD Y NO MANIPULACIÓN DE LOS DATOS</h3>
                    <p>Fagotto garantiza expresamente que los datos registrados en el sistema:</p>
                    <ul>
                        <li>Son íntegros, inviolables e inalterables.</li>
                        <li>No pueden ser modificados, corregidos, editados, reemplazados ni eliminados manualmente, ni por el empleador ni por terceros.</li>
                        <li>No pueden ser intervenidos, manipulados ni ultrajados bajo ninguna circunstancia.</li>
                        <li>El sistema cuenta con mecanismos técnicos de seguridad, tales como registros de auditoría (logs), control de accesos y trazabilidad, que impiden cualquier alteración de la información sin dejar evidencia verificable.</li>
                        <li>Cualquier acceso o visualización de los datos queda debidamente registrado.</li>
                    </ul>

                    <h3>VII. CONFIDENCIALIDAD Y SEGURIDAD DE LA INFORMACIÓN</h3>
                    <p>Los datos personales:</p>
                    <ul>
                        <li>Son de uso exclusivo del empleador para fines laborales legales.</li>
                        <li>Se almacenan en sistemas seguros con medidas técnicas y organizativas adecuadas.</li>
                        <li>No se comunican, ceden ni transfieren a terceros ajenos a la relación laboral.</li>
                        <li>Se conservan únicamente por el período exigido por la normativa vigente.</li>
                    </ul>

                    <h3>VIII. CUMPLIMIENTO DE LA NORMATIVA LABORAL</h3>
                    <p>El sistema cumple con:</p>
                    <ul>
                        <li>Artículo 33 del Código del Trabajo.</li>
                        <li>Dictámenes de la Dirección del Trabajo sobre sistemas electrónicos de control de asistencia.</li>
                        <li>Ley N°19.628 sobre protección de datos personales.</li>
                    </ul>
                    <p>Los registros generados por este sistema tienen valor legal equivalente al libro de asistencia y no pueden ser objeto de correcciones arbitrarias, debiendo cualquier rectificación realizarse conforme a los procedimientos legales, con respaldo y trazabilidad.</p>

                    <h3>IX. DERECHOS DEL TRABAJADOR</h3>
                    <p>El trabajador podrá ejercer en todo momento los derechos de:</p>
                    <ul>
                        <li>Acceso a sus registros de asistencia.</li>
                        <li>Rectificación de datos erróneos conforme a la ley.</li>
                        <li>Solicitud de eliminación una vez cumplidos los plazos legales de conservación.</li>
                        <li>Reclamo ante la Dirección del Trabajo o autoridad competente.</li>
                    </ul>

                    <h3>X. CONSENTIMIENTO EXPRESO</h3>
                    <p>El trabajador declara haber sido informado de manera clara, suficiente y previa respecto del funcionamiento del sistema, otorgando su consentimiento libre, específico, informado y expreso para el tratamiento de sus datos personales y biométricos, conforme a la Ley N°19.628.</p>
                    <p>Este consentimiento podrá ser revocado, sin perjuicio del cumplimiento de las obligaciones legales del empleador.</p>

                    <h3>XI. SISTEMA ALTERNATIVO</h3>
                    <p>En caso de falla técnica, Fagotto garantizará un mecanismo alternativo de registro de asistencia, conforme a la normativa laboral vigente.</p>

                    <h3>XII. ACEPTACIÓN</h3>
                    <p>El trabajador declara haber leído, comprendido y aceptado íntegramente los presentes términos y condiciones.</p>
                </div>
                <button @click="cerrarTerminos" class="btn-aceptar-modal">
                    <i class="fas fa-check"></i>
                    Entendido
                </button>
            </div>
        </div>

        <!-- Paso 4: Registrando en AWS -->
        <div v-if="paso === 'registrando'" class="step-container">
            <div class="spinner"></div>
            <h2>Registrando en sistema...</h2>
            <p>Creando perfil facial en AWS Rekognition</p>
            <div class="progress-bar">
                <div class="progress" :style="{ width: progreso + '%' }"></div>
            </div>
            <p class="progress-text">{{ progreso }}%</p>
        </div>

        <!-- Paso 4: Resultado Exitoso -->
        <div v-if="paso === 'exito'" class="step-container">
            <div class="resultado-box success">
                <i class="fas fa-check-circle"></i>
                <h2>¡Registro Exitoso!</h2>
                <img :src="fotoCapturada" class="foto-resultado" />
                <div class="datos">
                    <div class="dato">
                        <i class="fas fa-user"></i>
                        {{ empleado.nombre }}
                    </div>
                    <div class="dato">
                        <i class="fas fa-id-card"></i>
                        {{ empleado.rut }}
                    </div>
                    <div class="dato">
                        <i class="fas fa-briefcase"></i>
                        {{ empleado.cargo }}
                    </div>
                    <div class="dato">
                        <i class="fas fa-fingerprint"></i>
                        Face ID: {{ faceId }}
                    </div>
                </div>
                <p class="mensaje">Ya puedes usar el sistema de asistencia</p>
            </div>
        </div>

        <!-- Paso 5: Error -->
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
                appId: '<?= $appId ?>',
                paso: 'datos',
                localNombre: '<?= $localNombre ?>',
                
                empleado: {
                    nombre: '',
                    rut: '',
                    cargo: '',
                    email: ''
                },
                
                // Foto
                detectando: false,
                iluminacionOK: false,
                distanciaOK: false,
                rostroOK: false,
                puedeCapturar: false,
                fotoCapturada: null,
                videoStream: null,
                
                // Registro
                progreso: 0,
                faceId: '',
                
                // Resultado
                mensajeError: '',
                
                // Términos y firma
                mostrandoTerminos: false,
                firmaVacia: true,
                firmando: false,
                firmaDataURL: null
            },
            
            computed: {
                formularioValido() {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return this.empleado.nombre.length > 3 &&
                           this.empleado.rut.length > 8 &&
                           this.empleado.cargo !== '' &&
                           emailRegex.test(this.empleado.email);
                }
            },
            
            methods: {
                async continuarAFoto() {
                    this.paso = 'foto';
                    await this.iniciarCamara();
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
                    
                    // Ir a términos y firma
                    this.paso = 'firma';
                    this.$nextTick(() => {
                        this.iniciarCanvasFirma();
                    });
                },
                
                // Métodos de firma
                mostrarTerminos() {
                    this.mostrandoTerminos = true;
                },
                
                cerrarTerminos() {
                    this.mostrandoTerminos = false;
                },
                
                iniciarCanvasFirma() {
                    const canvas = this.$refs.canvasFirma;
                    if (!canvas) return;
                    
                    const rect = canvas.getBoundingClientRect();
                    canvas.width = rect.width * 2;
                    canvas.height = rect.height * 2;
                    const ctx = canvas.getContext('2d');
                    ctx.scale(2, 2);
                    ctx.fillStyle = 'white';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                },
                
                iniciarFirma(e) {
                    e.preventDefault();
                    this.firmando = true;
                    const canvas = this.$refs.canvasFirma;
                    const ctx = canvas.getContext('2d');
                    const pos = this.obtenerPosicion(e);
                    ctx.beginPath();
                    ctx.moveTo(pos.x, pos.y);
                },
                
                dibujarFirma(e) {
                    if (!this.firmando) return;
                    e.preventDefault();
                    
                    const canvas = this.$refs.canvasFirma;
                    const ctx = canvas.getContext('2d');
                    const pos = this.obtenerPosicion(e);
                    
                    ctx.strokeStyle = '#000';
                    ctx.lineWidth = 2;
                    ctx.lineCap = 'round';
                    ctx.lineTo(pos.x, pos.y);
                    ctx.stroke();
                    
                    this.firmaVacia = false;
                },
                
                finalizarFirma() {
                    this.firmando = false;
                },
                
                obtenerPosicion(e) {
                    const canvas = this.$refs.canvasFirma;
                    const rect = canvas.getBoundingClientRect();
                    const touch = e.touches ? e.touches[0] : e;
                    return {
                        x: touch.clientX - rect.left,
                        y: touch.clientY - rect.top
                    };
                },
                
                limpiarFirma() {
                    const canvas = this.$refs.canvasFirma;
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = 'white';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    this.firmaVacia = true;
                },
                
                async confirmarYRegistrar() {
                    if (this.firmaVacia) {
                        alert('Por favor firma antes de continuar');
                        return;
                    }
                    
                    // Capturar firma
                    const canvas = this.$refs.canvasFirma;
                    this.firmaDataURL = canvas.toDataURL('image/png');
                    
                    this.paso = 'registrando';
                    await this.registrarEnAWS();
                },
                
                async registrarEnAWS() {
                    // Simular progreso
                    const interval = setInterval(() => {
                        this.progreso += 10;
                        if (this.progreso >= 100) clearInterval(interval);
                    }, 300);
                    
                    try {
                        console.log('🚀 Iniciando registro en sistema...');
                        console.log('📦 Datos a enviar:', {
                            sessionId: this.sessionId,
                            appId: this.appId,
                            nombre: this.empleado.nombre,
                            rut: this.empleado.rut,
                            cargo: this.empleado.cargo,
                            email: this.empleado.email,
                            fotoSize: this.fotoCapturada ? this.fotoCapturada.length : 0,
                            firmaSize: this.firmaDataURL ? this.firmaDataURL.length : 0
                        });
                        
                        // Enviar a backend PHP que llama a AWS Rekognition IndexFaces
                        const response = await axios.post('api/registrar-rostro.php', {
                            sessionId: this.sessionId,
                            appId: this.appId,
                            nombre: this.empleado.nombre,
                            rut: this.empleado.rut,
                            cargo: this.empleado.cargo,
                            email: this.empleado.email,
                            foto: this.fotoCapturada
                        });
                        
                        console.log('📨 Respuesta del servidor:', response.data);
                        
                        if (response.data.success) {
                            this.faceId = response.data.face_id;
                            
                            console.log('📧 Enviando términos firmados al email...');
                            
                            // Enviar términos firmados por email
                            const emailResponse = await axios.post('api/enviar-terminos-firmados.php', {
                                nombre: this.empleado.nombre,
                                email: this.empleado.email,
                                firma: this.firmaDataURL
                            });
                            
                            console.log('📧 Respuesta email:', emailResponse.data);
                            
                            this.paso = 'exito';
                        } else {
                            console.error('❌ Error del servidor:', response.data);
                            this.mensajeError = response.data.message || 'Error desconocido del servidor';
                            alert('Error: ' + this.mensajeError);
                            this.paso = 'error';
                        }
                    } catch (error) {
                        console.error('❌ Error crítico en registro:', error);
                        console.error('📋 Detalles del error:', {
                            message: error.message,
                            response: error.response ? error.response.data : 'No hay respuesta',
                            status: error.response ? error.response.status : 'N/A',
                            url: error.config ? error.config.url : 'N/A'
                        });
                        
                        this.mensajeError = error.response?.data?.message || error.message || 'Error al registrar en el sistema';
                        alert('Error al registrar:\n\n' + this.mensajeError + '\n\nRevisa la consola (F12) para más detalles.');
                        this.paso = 'error';
                    }
                },
                
                reintentar() {
                    this.paso = 'datos';
                    this.empleado = { nombre: '', rut: '', cargo: '', email: '' };
                    this.fotoCapturada = null;
                    this.progreso = 0;
                    this.firmaVacia = true;
                    this.firmaDataURL = null;
                }
            }
        });
    </script>
</body>
</html>
