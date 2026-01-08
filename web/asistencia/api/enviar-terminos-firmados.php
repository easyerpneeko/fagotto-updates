<?php
/**
 * API: Enviar Términos Firmados por Correo
 * 
 * Genera PDF con términos y condiciones firmados y lo envía al correo del empleado
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $empleado = $input['empleado'] ?? null;
    $firma = $input['firma'] ?? null;
    $fecha = $input['fecha'] ?? date('Y-m-d');
    
    if (!$empleado || !$firma) {
        throw new Exception('Datos incompletos');
    }
    
    // Generar HTML del documento de términos
    $html = generarHTMLTerminos($empleado, $firma, $fecha);
    
    // Crear directorios si no existen
    $timestamp = time();
    $directorioFirmas = '../uploads/firmas';
    $directorioTerminos = '../uploads/terminos';
    
    if (!is_dir($directorioFirmas)) {
        mkdir($directorioFirmas, 0755, true);
    }
    if (!is_dir($directorioTerminos)) {
        mkdir($directorioTerminos, 0755, true);
    }
    
    // Guardar firma
    $rutaFirma = $directorioFirmas . '/' . $empleado['rut'] . '_' . $timestamp . '.png';
    $firmaData = str_replace('data:image/png;base64,', '', $firma);
    $firmaData = str_replace(' ', '+', $firmaData);
    file_put_contents($rutaFirma, base64_decode($firmaData));
    
    // Guardar HTML de términos
    $rutaTerminos = $directorioTerminos . '/' . $empleado['rut'] . '_' . $timestamp . '.html';
    file_put_contents($rutaTerminos, $html);
    
    // Registrar aceptación en BD
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            UPDATE asistencias_employees 
            SET terminos_aceptados = 1,
                terminos_fecha = NOW(),
                terminos_archivo = ?,
                firma_archivo = ?
            WHERE rut = ?
        ");
        $stmt->execute([
            basename($rutaTerminos),
            basename($rutaFirma),
            $empleado['rut']
        ]);
    } catch (Exception $dbError) {
        error_log("⚠️ No se pudo actualizar BD (puede que no existan las columnas): " . $dbError->getMessage());
    }
    
    // Intentar enviar correo (opcional, no detiene el proceso si falla)
    $emailEnviado = false;
    try {
        $emailEnviado = enviarCorreoTerminos($empleado['email'], $empleado['nombre'], $html, $rutaFirma);
    } catch (Exception $emailError) {
        error_log("⚠️ No se pudo enviar email: " . $emailError->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Términos guardados correctamente',
        'email_enviado' => $emailEnviado,
        'archivos' => [
            'firma' => basename($rutaFirma),
            'terminos' => basename($rutaTerminos)
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function generarHTMLTerminos($empleado, $firma, $fecha) {
    $html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        h1 {
            text-align: center;
            color: #667eea;
            font-size: 20px;
            margin-bottom: 30px;
        }
        h2 {
            color: #764ba2;
            font-size: 16px;
            margin-top: 25px;
            margin-bottom: 10px;
        }
        p {
            margin: 10px 0;
            text-align: justify;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .header-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            border-left: 4px solid #667eea;
        }
        .firma-box {
            margin-top: 40px;
            padding: 20px;
            border: 2px solid #667eea;
            border-radius: 8px;
            text-align: center;
        }
        .firma-img {
            max-width: 300px;
            height: auto;
            margin: 20px 0;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>TÉRMINOS Y CONDICIONES DE USO<br>SISTEMA ELECTRÓNICO DE CONTROL DE ASISTENCIA<br>RECONOCIMIENTO FACIAL Y GEOLOCALIZACIÓN</h1>
    
    <div class="header-info">
        <h2>I. IDENTIFICACIÓN DEL EMPLEADOR</h2>
        <p><strong>Razón Social:</strong> Fagotto<br>
        <strong>RUT:</strong> 77.742.774-1<br>
        <strong>Domicilio:</strong> Avenida Las Condes N° 7253, comuna de Las Condes, Región Metropolitana, Chile.</p>
    </div>
    
    <h2>II. OBJETO DEL SISTEMA</h2>
    <p>El presente documento regula el uso del Sistema Electrónico de Control de Asistencia implementado por Fagotto, cuyo único y exclusivo objetivo es el registro de la jornada laboral, horas ordinarias y extraordinarias de los trabajadores, en conformidad con lo dispuesto en el artículo 33 del Código del Trabajo, la Ley N°19.628 sobre Protección de la Vida Privada, y los dictámenes e instrucciones de la Dirección del Trabajo.</p>
    <p><strong>Este sistema no constituye un mecanismo de vigilancia, supervisión permanente, control conductual ni herramienta disciplinaria.</strong></p>
    
    <h2>III. DATOS PERSONALES TRATADOS</h2>
    <p>El sistema podrá tratar exclusivamente los siguientes datos:</p>
    <ul>
        <li>Identificación del trabajador/a</li>
        <li>Fecha y hora de marcaje de entrada y salida</li>
        <li>Patrón biométrico facial (dato matemático cifrado)</li>
        <li>Ubicación geográfica únicamente al momento del marcaje</li>
    </ul>
    <p><strong>⚠️ No se realiza monitoreo continuo, seguimiento en tiempo real ni trazabilidad de desplazamientos.</strong></p>
    
    <h2>IV. RECONOCIMIENTO FACIAL</h2>
    <p>El reconocimiento facial se utiliza exclusivamente como mecanismo de validación de identidad al momento de registrar la asistencia.</p>
    <ul>
        <li>No se almacenan fotografías ni imágenes faciales utilizables.</li>
        <li>El dato biométrico se transforma en un patrón matemático cifrado, irreversible e irreproducible.</li>
        <li>Dicho patrón no puede ser reconstruido, reutilizado ni destinado a fines distintos al control de asistencia.</li>
    </ul>
    
    <h2>V. GEOLOCALIZACIÓN</h2>
    <p>La geolocalización:</p>
    <ul>
        <li>Se activa única y exclusivamente al momento de efectuar el marcaje de entrada o salida.</li>
        <li>Permite verificar el lugar de prestación de servicios.</li>
        <li>No permanece activa fuera del proceso de marcaje.</li>
        <li>No genera seguimiento, monitoreo ni historial de desplazamientos del trabajador.</li>
    </ul>
    
    <h2>VI. INTEGRIDAD, INVIOLABILIDAD Y NO MANIPULACIÓN DE LOS DATOS</h2>
    <p>Fagotto garantiza expresamente que los datos registrados en el sistema:</p>
    <ul>
        <li>Son íntegros, inviolables e inalterables.</li>
        <li>No pueden ser modificados, corregidos, editados, reemplazados ni eliminados manualmente, ni por el empleador ni por terceros.</li>
        <li>No pueden ser intervenidos, manipulados ni ultrajados bajo ninguna circunstancia.</li>
    </ul>
    <p>El sistema cuenta con mecanismos técnicos de seguridad, tales como registros de auditoría (logs), control de accesos y trazabilidad, que impiden cualquier alteración de la información sin dejar evidencia verificable.</p>
    
    <h2>VII. CONFIDENCIALIDAD Y SEGURIDAD DE LA INFORMACIÓN</h2>
    <p>Los datos personales:</p>
    <ul>
        <li>Son de uso exclusivo del empleador para fines laborales legales.</li>
        <li>Se almacenan en sistemas seguros con medidas técnicas y organizativas adecuadas.</li>
        <li>No se comunican, ceden ni transfieren a terceros ajenos a la relación laboral.</li>
        <li>Se conservan únicamente por el período exigido por la normativa vigente.</li>
    </ul>
    
    <h2>VIII. CUMPLIMIENTO DE LA NORMATIVA LABORAL</h2>
    <p>El sistema cumple con:</p>
    <ul>
        <li>Artículo 33 del Código del Trabajo.</li>
        <li>Dictámenes de la Dirección del Trabajo sobre sistemas electrónicos de control de asistencia.</li>
        <li>Ley N°19.628 sobre protección de datos personales.</li>
    </ul>
    
    <h2>IX. DERECHOS DEL TRABAJADOR</h2>
    <p>El trabajador podrá ejercer en todo momento los derechos de:</p>
    <ul>
        <li>Acceso a sus registros de asistencia.</li>
        <li>Rectificación de datos erróneos conforme a la ley.</li>
        <li>Solicitud de eliminación una vez cumplidos los plazos legales de conservación.</li>
        <li>Reclamo ante la Dirección del Trabajo o autoridad competente.</li>
    </ul>
    
    <h2>X. CONSENTIMIENTO EXPRESO</h2>
    <p>El trabajador declara haber sido informado de manera clara, suficiente y previa respecto del funcionamiento del sistema, otorgando su consentimiento libre, específico, informado y expreso para el tratamiento de sus datos personales y biométricos, conforme a la Ley N°19.628.</p>
    
    <h2>XI. SISTEMA ALTERNATIVO</h2>
    <p>En caso de falla técnica, Fagotto garantizará un mecanismo alternativo de registro de asistencia, conforme a la normativa laboral vigente.</p>
    
    <h2>XII. ACEPTACIÓN</h2>
    <p>El trabajador declara haber leído, comprendido y aceptado íntegramente los presentes términos y condiciones.</p>
    
    <div class="firma-box">
        <p><strong>Nombre del trabajador:</strong> ' . htmlspecialchars($empleado['nombre']) . '</p>
        <p><strong>RUT:</strong> ' . htmlspecialchars($empleado['rut']) . '</p>
        <p><strong>Correo:</strong> ' . htmlspecialchars($empleado['email']) . '</p>
        <p><strong>Cargo:</strong> ' . htmlspecialchars($empleado['cargo']) . '</p>
        <p><strong>Fecha:</strong> ' . htmlspecialchars($fecha) . '</p>
        
        <div style="margin-top: 30px;">
            <p><strong>Firma Digital:</strong></p>
            <img src="' . $firma . '" class="firma-img" alt="Firma">
            <p style="margin-top: 10px;"><strong>' . htmlspecialchars($empleado['nombre']) . '</strong></p>
        </div>
    </div>
    
    <div class="footer">
        <p>Documento generado automáticamente por el Sistema de Control de Asistencia Fagotto</p>
        <p>Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>
    </div>
</body>
</html>';
    
    return $html;
}

function enviarCorreoTerminos($destinatario, $nombre, $html, $rutaFirma) {
    // Configuración del correo
    $asunto = 'Términos y Condiciones - Sistema de Asistencia Fagotto';
    
    // Cabeceras del correo
    $cabeceras = "MIME-Version: 1.0\r\n";
    $cabeceras .= "Content-type: text/html; charset=UTF-8\r\n";
    $cabeceras .= "From: Sistema de Asistencia <noreply@fagotto.cl>\r\n";
    $cabeceras .= "Reply-To: rrhh@fagotto.cl\r\n";
    
    // Cuerpo del correo
    $mensaje = $html;
    
    // Enviar correo
    $enviado = mail($destinatario, $asunto, $mensaje, $cabeceras);
    
    // Log del envío
    if ($enviado) {
        error_log("✅ Términos enviados a: $destinatario");
    } else {
        error_log("❌ Error enviando términos a: $destinatario");
    }
    
    return $enviado;
}
