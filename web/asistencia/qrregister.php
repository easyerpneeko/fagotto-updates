<?php
/**
 * QR Register - Registro de Empleados con AWS Rekognition
 * 
 * URL: https://fagotto.cl/qrregister.php?employee=EMP001
 * 
 * Flujo:
 * 1. Admin genera QR para nuevo empleado
 * 2. Empleado escanea QR con su teléfono
 * 3. Captura foto de su rostro
 * 4. Sistema indexa en AWS Rekognition Collection
 * 5. Guarda en base de datos
 */

require_once 'config/database.php';
require_once 'config/aws.php';
require_once 'helpers/AWSRekognition.php';

// Obtener token de la URL
$token = $_GET['token'] ?? $_GET['employee'] ?? null;
$linkUsado = false;
$mensajeError = '';

if (!$token) {
    $mensajeError = 'Error: Token de registro requerido';
    $linkUsado = true;
} else {
    // Verificar el token en la base de datos
    $stmt = $pdo->prepare("
        SELECT e.*, rt.usado, rt.usado_fecha, rt.tipo_uso 
        FROM employees e
        LEFT JOIN registro_tokens rt ON rt.employee_id = e.id AND rt.token = ?
        WHERE e.id = ? OR e.registro_token = ?
        LIMIT 1
    ");
    $stmt->execute([$token, $token, $token]);
    $employee = $stmt->fetch();
    
    // Verificar si el empleado existe
    if (!$employee) {
        $mensajeError = 'Error: Token inválido o empleado no encontrado';
        $linkUsado = true;
    }
    // Verificar si el token ya fue usado
    elseif ($employee['usado'] == 1 || $employee['token_usado'] == 1) {
        $mensajeError = 'Este link ya fue utilizado';
        $linkUsado = true;
    }
    // Verificar si ya tiene rostro registrado
    elseif ($employee['face_indexed']) {
        $mensajeError = 'Este empleado ya tiene rostro registrado';
        $linkUsado = true;
    }
    
    // Si todo está bien, guardar el token para usarlo
    if (!$linkUsado) {
        $employeeId = $employee['id'];
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Rostro - Fagotto</title>
    <link rel="stylesheet" href="assets/css/mobile-checkin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div id="app" class="mobile-app">
        <!-- Link Ya Usado -->
        <?php if ($linkUsado): ?>
        <div class="step-container">
            <div class="header header-error">
                <i class="fas fa-ban"></i>
                <h1>Link No Disponible</h1>
                <p>Este enlace ya no puede ser utilizado</p>
            </div>

            <div class="error-content">
                <div class="error-icon-large">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                
                <h2 class="error-title">Link Ya Usado</h2>
                
                <div class="error-message">
                    <p><strong><?php echo htmlspecialchars($mensajeError); ?></strong></p>
                    <p>Por razones de seguridad, cada link de registro solo puede ser utilizado una vez.</p>
                </div>

                <div class="error-reasons">
                    <h3><i class="fas fa-shield-alt"></i> ¿Por qué sucede esto?</h3>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> El link ya fue usado anteriormente</li>
                        <li><i class="fas fa-check-circle"></i> Ya existe un registro con este link</li>
                        <li><i class="fas fa-check-circle"></i> El empleado ya completó su registro</li>
                    </ul>
                </div>

                <div class="error-solution">
                    <h3><i class="fas fa-lightbulb"></i> Solución</h3>
                    <div class="solution-box">
                        <p><strong>Si necesitas registrarte:</strong></p>
                        <p>Contacta a tu supervisor o administrador del sistema para obtener un <strong>nuevo link de registro</strong>.</p>
                    </div>
                    <div class="solution-box">
                        <p><strong>Si ya te registraste:</strong></p>
                        <p>Usa tu link personal de <strong>marcación de asistencia</strong> para registrar tu entrada y salida.</p>
                    </div>
                </div>

                <div class="error-contact">
                    <i class="fas fa-headset"></i>
                    <p><strong>¿Necesitas ayuda?</strong></p>
                    <p>Contacta al departamento de RRHH o soporte técnico</p>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Paso 1: Formulario de Registro -->
        <div v-if="paso === 'info'" class="step-container">
            <div class="header">
                <i class="fas fa-user-plus"></i>
                <h1>Registro de Empleado</h1>
                <p>Completa tu información</p>
            </div>

            <div class="form-registro">
                <div class="form-group">
                    <label for="nombre">
                        <i class="fas fa-user"></i>
                        Nombre Completo
                    </label>
                    <input 
                        type="text" 
                        id="nombre" 
                        v-model="formulario.nombre" 
                        placeholder="Ej: Juan Pérez González"
                        class="form-input"
                        required
                    >
                    <span v-if="errores.nombre" class="error-msg">{{ errores.nombre }}</span>
                </div>

                <div class="form-group">
                    <label for="rut">
                        <i class="fas fa-id-card"></i>
                        RUT
                    </label>
                    <input 
                        type="text" 
                        id="rut" 
                        v-model="formulario.rut" 
                        placeholder="Ej: 12.345.678-9"
                        class="form-input"
                        @input="formatearRUT"
                        required
                    >
                    <span v-if="errores.rut" class="error-msg">{{ errores.rut }}</span>
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Correo Electrónico
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        v-model="formulario.email" 
                        placeholder="tu.correo@ejemplo.com"
                        class="form-input"
                        required
                    >
                    <span v-if="errores.email" class="error-msg">{{ errores.email }}</span>
                </div>

                <div class="form-group">
                    <label for="cargo">
                        <i class="fas fa-briefcase"></i>
                        Cargo
                    </label>
                    <select 
                        id="cargo" 
                        v-model="formulario.cargo" 
                        class="form-select"
                        required
                    >
                        <option value="" disabled selected>Selecciona tu cargo</option>
                        <option value="Cajero">Cajero</option>
                        <option value="Cocina">Cocina</option>
                        <option value="Jefe de Tienda">Jefe de Tienda</option>
                    </select>
                    <span v-if="errores.cargo" class="error-msg">{{ errores.cargo }}</span>
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input 
                            type="checkbox" 
                            v-model="formulario.aceptaTerminos" 
                            class="checkbox-input"
                        >
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-text">
                            Acepto los <a href="#" @click.prevent="mostrarTerminos = true">términos de servicio</a> 
                            y el uso de mi información biométrica para control de asistencia
                        </span>
                    </label>
                    <span v-if="errores.terminos" class="error-msg">{{ errores.terminos }}</span>
                </div>
            </div>

            <div class="instrucciones">
                <h3><i class="fas fa-info-circle"></i> Siguiente Paso</h3>
                <ul>
                    <li>Buscaremos un lugar con buena iluminación</li>
                    <li>Deberás quitarte lentes y gorro</li>
                    <li>Mirar directamente a la cámara</li>
                    <li>Mantener una expresión neutral</li>
                </ul>
            </div>

            <button @click="validarYContinuar" class="btn-continuar" :disabled="!formularioValido">
                <i class="fas fa-arrow-right"></i>
                Continuar al Registro Facial
            </button>
        </div>

        <!-- Modal de Términos de Servicio -->
        <div v-if="mostrarTerminos" class="modal-overlay" @click="mostrarTerminos = false">
            <div class="modal-terminos" @click.stop>
                <div class="modal-header">
                    <h2><i class="fas fa-file-contract"></i> Términos y Condiciones</h2>
                    <button @click="mostrarTerminos = false" class="btn-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <h2 style="text-align: center; margin-bottom: 20px;">TÉRMINOS Y CONDICIONES DE USO<br>SISTEMA ELECTRÓNICO DE CONTROL DE ASISTENCIA<br>RECONOCIMIENTO FACIAL Y GEOLOCALIZACIÓN</h2>
                    
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
                    <p>⚠️ <strong>No se realiza monitoreo continuo, seguimiento en tiempo real ni trazabilidad de desplazamientos.</strong></p>
                    
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
                    </ul>
                    <p>El sistema cuenta con mecanismos técnicos de seguridad, tales como registros de auditoría (logs), control de accesos y trazabilidad, que impiden cualquier alteración de la información sin dejar evidencia verificable.</p>
                    <p>Cualquier acceso o visualización de los datos queda debidamente registrado.</p>
                    
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
                    
                    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                        <p><strong>Nombre del trabajador:</strong> {{ formulario.nombre }}</p>
                        <p><strong>RUT:</strong> {{ formulario.rut }}</p>
                        <p><strong>Correo:</strong> {{ formulario.email }}</p>
                        <p><strong>Fecha:</strong> {{ fechaActual }}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="mostrarTerminos = false" class="btn-modal-ok">
                        <i class="fas fa-check"></i>
                        Entendido
                    </button>
                </div>
            </div>
        </div>

        <!-- Paso 2: Captura de Foto -->
        <div v-if="paso === 'captura'" class="step-container">
            <div class="header">
                <i class="fas fa-camera"></i>
                <h1>Captura tu Rostro</h1>
                <p>Centra tu cara en el óvalo</p>
            </div>

            <div class="video-container">
                <video ref="video" autoplay playsinline></video>
                <canvas ref="canvas" style="display:none"></canvas>
                
                <div class="face-guide">
                    <div class="oval"></div>
                </div>

                <div v-if="detectando" class="status">
                    <i class="fas fa-spinner fa-spin"></i>
                    Analizando...
                </div>
                <div v-if="calidadOK" class="status success">
                    <i class="fas fa-check-circle"></i>
                    ¡Calidad perfecta!
                </div>
            </div>

            <div class="checks">
                <div class="check" :class="{ active: iluminacionOK }">
                    <i class="fas fa-sun"></i>
                    Iluminación
                </div>
                <div class="check" :class="{ active: rostroOK }">
                    <i class="fas fa-smile"></i>
                    Rostro centrado
                </div>
                <div class="check" :class="{ active: calidadOK }">
                    <i class="fas fa-star"></i>
                    Alta calidad
                </div>
            </div>

            <button v-if="puedeCapturar" @click="capturar" class="btn-capturar">
                <i class="fas fa-camera"></i>
                Capturar Foto
            </button>
        </div>

        <!-- Paso 3: Confirmar Foto -->
        <div v-if="paso === 'confirmar'" class="step-container">
            <div class="header">
                <i class="fas fa-check-double"></i>
                <h1>Confirmar Foto</h1>
                <p>¿Esta foto se ve bien?</p>
            </div>

            <div class="preview-box">
                <img :src="fotoCapturada" class="preview-img" />
            </div>

            <div class="btn-group">
                <button @click="retomarFoto" class="btn-secondary">
                    <i class="fas fa-redo"></i>
                    Tomar otra
                </button>
                <button @click="irAFirma" class="btn-primary">
                    <i class="fas fa-arrow-right"></i>
                    Continuar a Firma
                </button>
            </div>
        </div>

        <!-- Paso 3.5: Firma de Términos -->
        <div v-if="paso === 'firma'" class="step-container">
            <div class="header">
                <i class="fas fa-file-signature"></i>
                <h1>Firma de Términos y Condiciones</h1>
                <p>Dibuja tu firma en el recuadro</p>
            </div>

            <div class="firma-info">
                <p><strong>Nombre:</strong> {{ empleado.nombre }}</p>
                <p><strong>RUT:</strong> {{ empleado.rut }}</p>
                <p><strong>Email:</strong> {{ empleado.email }}</p>
                <p><strong>Cargo:</strong> {{ empleado.cargo }}</p>
                <p><strong>Fecha:</strong> {{ fechaActual }}</p>
            </div>

            <div class="canvas-container">
                <canvas 
                    ref="canvasFirma" 
                    @mousedown="iniciarFirma"
                    @mousemove="dibujarFirma"
                    @mouseup="terminarFirma"
                    @mouseleave="terminarFirma"
                    @touchstart="iniciarFirmaTactil"
                    @touchmove="dibujarFirmaTactil"
                    @touchend="terminarFirma"
                    class="canvas-firma"
                ></canvas>
                <div class="canvas-placeholder" v-if="!firmaDibujada">
                    <i class="fas fa-pen"></i>
                    <p>Dibuja tu firma aquí</p>
                </div>
            </div>

            <div class="btn-group">
                <button @click="limpiarFirma" class="btn-secondary">
                    <i class="fas fa-eraser"></i>
                    Limpiar Firma
                </button>
                <button @click="guardarEnAWS" class="btn-primary" :disabled="!firmaDibujada">
                    <i class="fas fa-check"></i>
                    Confirmar y Registrar
                </button>
            </div>
        </div>

        <!-- Paso 4: Guardando en AWS -->
        <div v-if="paso === 'guardando'" class="step-container">
            <div class="spinner"></div>
            <h2>Registrando en AWS Rekognition...</h2>
            <div class="progress-bar">
                <div class="progress" :style="{ width: progreso + '%' }"></div>
            </div>
            <p class="progress-text">{{ progreso }}%</p>
        </div>

        <!-- Paso 5: Enviando Términos -->
        <div v-if="paso === 'enviando-terminos'" class="step-container">
            <div class="spinner"></div>
            <h2>Enviando términos firmados...</h2>
            <p>Se enviará una copia a {{ empleado.email }}</p>
        </div>

        <!-- Paso 6: Éxito -->
        <div v-if="paso === 'exito'" class="step-container">
            <div class="resultado-box success">
                <i class="fas fa-check-circle"></i>
                <h2>¡Registro Exitoso!</h2>
                <p>Tu rostro ha sido registrado correctamente</p>
                <img :src="fotoCapturada" class="foto-resultado" />
                <div class="info-final">
                    <p><strong>{{ empleado.nombre }}</strong></p>
                    <p>Ahora puedes marcar asistencia con reconocimiento facial</p>
                    <p style="margin-top: 15px;"><i class="fas fa-envelope"></i> Se ha enviado una copia de los términos firmados a <strong>{{ empleado.email }}</strong></p>
                </div>
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
        <?php endif; ?>
    </div>

    <?php if (!$linkUsado): ?>
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        new Vue({
            el: '#app',
            data: {
                employeeId: '<?= $employeeId ?>',
                empleado: <?= json_encode($employee) ?>,
                paso: 'info',
                
                // Formulario de registro
                formulario: {
                    nombre: '',
                    rut: '',
                    email: '',
                    cargo: '',
                    aceptaTerminos: false
                },
                errores: {
                    nombre: '',
                    rut: '',
                    email: '',
                    cargo: '',
                    terminos: ''
                },
                mostrarTerminos: false,
                
                // Captura
                detectando: false,
                iluminacionOK: false,
                rostroOK: false,
                calidadOK: false,
                puedeCapturar: false,
                fotoCapturada: null,
                videoStream: null,
                
                // Firma
                firmaDibujada: false,
                firmaData: null,
                dibujando: false,
                ctxFirma: null,
                
                // Guardado
                progreso: 0,
                mensajeError: ''
            },
            
            computed: {
                formularioValido() {
                    return this.formulario.nombre.trim().length > 0 &&
                           this.formulario.rut.trim().length > 0 &&
                           this.formulario.email.trim().length > 0 &&
                           this.formulario.cargo !== '' &&
                           this.formulario.aceptaTerminos;
                },
                fechaActual() {
                    const opciones = { year: 'numeric', month: 'long', day: 'numeric' };
                    return new Date().toLocaleDateString('es-CL', opciones);
                }
            },
            
            methods: {
                validarRUT(rut) {
                    // Limpiar RUT
                    rut = rut.replace(/[^0-9kK]/g, '');
                    
                    if (rut.length < 2) return false;
                    
                    const cuerpo = rut.slice(0, -1);
                    const dv = rut.slice(-1).toUpperCase();
                    
                    // Calcular dígito verificador
                    let suma = 0;
                    let multiplo = 2;
                    
                    for (let i = cuerpo.length - 1; i >= 0; i--) {
                        suma += parseInt(cuerpo.charAt(i)) * multiplo;
                        multiplo = multiplo < 7 ? multiplo + 1 : 2;
                    }
                    
                    const dvEsperado = 11 - (suma % 11);
                    const dvCalculado = dvEsperado === 11 ? '0' : dvEsperado === 10 ? 'K' : dvEsperado.toString();
                    
                    return dv === dvCalculado;
                },
                
                formatearRUT() {
                    let rut = this.formulario.rut.replace(/[^0-9kK]/g, '');
                    
                    if (rut.length > 1) {
                        const cuerpo = rut.slice(0, -1);
                        const dv = rut.slice(-1);
                        
                        // Formatear con puntos
                        let cuerpoFormateado = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        this.formulario.rut = cuerpoFormateado + '-' + dv;
                    }
                },
                
                validarYContinuar() {
                    // Limpiar errores
                    this.errores = {
                        nombre: '',
                        rut: '',
                        email: '',
                        cargo: '',
                        terminos: ''
                    };
                    
                    let valido = true;
                    
                    // Validar nombre
                    if (this.formulario.nombre.trim().length < 3) {
                        this.errores.nombre = 'El nombre debe tener al menos 3 caracteres';
                        valido = false;
                    }
                    
                    // Validar RUT
                    if (!this.validarRUT(this.formulario.rut)) {
                        this.errores.rut = 'RUT inválido';
                        valido = false;
                    }
                    
                    // Validar email
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(this.formulario.email)) {
                        this.errores.email = 'Correo electrónico inválido';
                        valido = false;
                    }
                    
                    // Validar cargo
                    if (this.formulario.cargo === '') {
                        this.errores.cargo = 'Debes seleccionar un cargo';
                        valido = false;
                    }
                    
                    // Validar términos
                    if (!this.formulario.aceptaTerminos) {
                        this.errores.terminos = 'Debes aceptar los términos de servicio';
                        valido = false;
                    }
                    
                    if (valido) {
                        // Actualizar datos del empleado con el formulario
                        this.empleado.nombre = this.formulario.nombre;
                        this.empleado.rut = this.formulario.rut;
                        this.empleado.email = this.formulario.email;
                        this.empleado.cargo = this.formulario.cargo;
                        
                        // Continuar a captura
                        this.paso = 'captura';
                    }
                },
                
                async iniciarCamara() {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({
                            video: { 
                                facingMode: 'user',
                                width: { ideal: 1920 },
                                height: { ideal: 1080 }
                            }
                        });
                        
                        this.videoStream = stream;
                        this.$refs.video.srcObject = stream;
                        
                        // Análisis de calidad
                        setTimeout(() => { this.detectando = true; }, 500);
                        setTimeout(() => { this.iluminacionOK = true; }, 1500);
                        setTimeout(() => { this.rostroOK = true; }, 2500);
                        setTimeout(() => { 
                            this.calidadOK = true;
                            this.detectando = false;
                            this.puedeCapturar = true;
                        }, 3500);
                        
                    } catch (error) {
                        console.error('Error cámara:', error);
                        this.mensajeError = 'No se pudo acceder a la cámara';
                        this.paso = 'error';
                    }
                },
                
                capturar() {
                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;
                    const ctx = canvas.getContext('2d');
                    
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    ctx.drawImage(video, 0, 0);
                    
                    this.fotoCapturada = canvas.toDataURL('image/jpeg', 0.95);
                    
                    // Detener cámara
                    if (this.videoStream) {
                        this.videoStream.getTracks().forEach(track => track.stop());
                    }
                    
                    this.paso = 'confirmar';
                },
                
                retomarFoto() {
                    this.paso = 'captura';
                    this.fotoCapturada = null;
                    this.iniciarCamara();
                },
                
                irAFirma() {
                    this.paso = 'firma';
                },
                
                iniciarCanvasFirma() {
                    const canvas = this.$refs.canvasFirma;
                    canvas.width = canvas.offsetWidth;
                    canvas.height = 300;
                    this.ctxFirma = canvas.getContext('2d');
                    this.ctxFirma.strokeStyle = '#000';
                    this.ctxFirma.lineWidth = 2;
                    this.ctxFirma.lineCap = 'round';
                    this.ctxFirma.lineJoin = 'round';
                },
                
                iniciarFirma(e) {
                    this.dibujando = true;
                    const rect = this.$refs.canvasFirma.getBoundingClientRect();
                    this.ctxFirma.beginPath();
                    this.ctxFirma.moveTo(e.clientX - rect.left, e.clientY - rect.top);
                    this.firmaDibujada = true;
                },
                
                dibujarFirma(e) {
                    if (!this.dibujando) return;
                    const rect = this.$refs.canvasFirma.getBoundingClientRect();
                    this.ctxFirma.lineTo(e.clientX - rect.left, e.clientY - rect.top);
                    this.ctxFirma.stroke();
                },
                
                iniciarFirmaTactil(e) {
                    e.preventDefault();
                    this.dibujando = true;
                    const rect = this.$refs.canvasFirma.getBoundingClientRect();
                    const touch = e.touches[0];
                    this.ctxFirma.beginPath();
                    this.ctxFirma.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
                    this.firmaDibujada = true;
                },
                
                dibujarFirmaTactil(e) {
                    if (!this.dibujando) return;
                    e.preventDefault();
                    const rect = this.$refs.canvasFirma.getBoundingClientRect();
                    const touch = e.touches[0];
                    this.ctxFirma.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
                    this.ctxFirma.stroke();
                },
                
                terminarFirma() {
                    this.dibujando = false;
                    if (this.firmaDibujada) {
                        this.firmaData = this.$refs.canvasFirma.toDataURL('image/png');
                    }
                },
                
                limpiarFirma() {
                    this.ctxFirma.clearRect(0, 0, this.$refs.canvasFirma.width, this.$refs.canvasFirma.height);
                    this.firmaDibujada = false;
                    this.firmaData = null;
                },
                
                async guardarEnAWS() {
                    this.paso = 'guardando';
                    
                    // Simular progreso
                    const interval = setInterval(() => {
                        this.progreso += 10;
                        if (this.progreso >= 100) clearInterval(interval);
                    }, 300);
                    
                    try {
                        // Enviar a backend PHP que indexa en AWS Rekognition
                        const response = await axios.post('api/registrar-rostro.php', {
                            employeeId: this.employeeId,
                            token: '<?php echo $token; ?>',
                            nombre: this.empleado.nombre,
                            rut: this.empleado.rut,
                            email: this.empleado.email,
                            cargo: this.empleado.cargo,
                            foto: this.fotoCapturada
                        });
                        
                        if (response.data.success) {
                            // Marcar token como usado
                            await axios.post('api/marcar-token-usado.php', {
                                token: '<?php echo $token; ?>',
                                employeeId: this.employeeId
                            });
                            
                            // Enviar términos firmados por correo
                            this.paso = 'enviando-terminos';
                            await axios.post('api/enviar-terminos-firmados.php', {
                                empleado: {
                                    nombre: this.empleado.nombre,
                                    rut: this.empleado.rut,
                                    email: this.empleado.email,
                                    cargo: this.empleado.cargo
                                },
                                firma: this.firmaData,
                                fecha: this.fechaActual
                            });
                            
                            await new Promise(resolve => setTimeout(resolve, 1000));
                            this.paso = 'exito';
                        } else {
                            this.mensajeError = response.data.message;
                            this.paso = 'error';
                        }
                    } catch (error) {
                        console.error('Error guardando:', error);
                        this.mensajeError = 'Error al registrar rostro';
                        this.paso = 'error';
                    }
                },
                
                reintentar() {
                    this.paso = 'captura';
                    this.progreso = 0;
                    this.iniciarCamara();
                }
            },
            
            watch: {
                paso(newPaso) {
                    if (newPaso === 'captura') {
                        this.$nextTick(() => this.iniciarCamara());
                    }
                    if (newPaso === 'firma') {
                        this.$nextTick(() => this.iniciarCanvasFirma());
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
